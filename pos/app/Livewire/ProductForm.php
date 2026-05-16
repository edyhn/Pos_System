<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use App\Services\BarcodeService;

class ProductForm extends Component
{
    use WithFileUploads;

    public $productId;
    public $category_id;
    public $vendor_id;
    public $name;
    public $slug;
    public $sku;
    public $barcode;
    public $price;
    public $cost_price;
    public $stock = 0;
    public $min_stock = 0;
    public $unit = 'pcs';
    public $is_taxed = false;
    public $tax_rate = 0;
    public $is_subscription = false;
    public $subscription_days = 30;
    public $description;
    public $image;
    public $is_active = true;

    public $isEdit = false;
    public $storeId;
    public $categories;
    public $vendors;
    public $existingImage;

    protected function rules()
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'name' => 'required|min:2|max:255',
            'slug' => 'required|max:255|unique:products,slug,' . ($this->productId ?? '') . ',id,store_id,' . $this->storeId,
            'sku' => 'nullable|max:50|unique:products,sku,' . ($this->productId ?? '') . ',id,store_id,' . $this->storeId,
            'barcode' => 'nullable|max:50',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'unit' => 'required|max:20',
            'is_taxed' => 'boolean',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'is_subscription' => 'boolean',
            'subscription_days' => 'required|integer|min:1',
            'description' => 'nullable',
            'image' => 'nullable|image|max:1024',
            'is_active' => 'boolean',
        ];
    }

    public function mount($id = null)
    {
        $this->storeId = auth()->user()->store_id;
        $this->categories = Category::where('store_id', $this->storeId)->whereHas('store', fn($q) => $q->where('is_active', true))->get();
        $this->vendors = Vendor::where('store_id', $this->storeId)->where('is_active', true)->get();

        if ($id) {
            $this->isEdit = true;
            $this->productId = $id;
            $product = Product::where('store_id', $this->storeId)->findOrFail($id);
            $this->category_id = $product->category_id;
            $this->vendor_id = $product->vendor_id;
            $this->name = $product->name;
            $this->slug = $product->slug;
            $this->sku = $product->sku;
            $this->barcode = $product->barcode;
            $this->price = $product->price;
            $this->cost_price = $product->cost_price;
            $this->stock = $product->stock;
            $this->min_stock = $product->min_stock;
            $this->unit = $product->unit;
            $this->is_taxed = $product->is_taxed;
            $this->tax_rate = $product->tax_rate;
            $this->is_subscription = $product->is_subscription;
            $this->subscription_days = $product->subscription_days;
            $this->description = $product->description;
            $this->is_active = $product->is_active;
            $this->existingImage = $product->image;
        }
    }

    public function updatedCategoryId($value)
    {
        if (!$this->isEdit && $value) {
            $this->generateSku($value);
        }
    }

    protected function generateSku($categoryId)
    {
        $category = Category::find($categoryId);
        if (!$category) return;

        $name = $category->name;
        $prefix = strtoupper(substr($name, 0, 3));
        if (strlen($prefix) < 3) {
            $prefix = str_pad($prefix, 3, strtoupper($name[0]));
        }

        $existing = Product::where('store_id', $this->storeId)
            ->where('sku', 'like', $prefix . '-%')
            ->pluck('sku');

        $max = 0;
        $pattern = '/^' . preg_quote($prefix, '/') . '-(\d+)$/';
        foreach ($existing as $sku) {
            if (preg_match($pattern, $sku, $m)) {
                $num = (int) $m[1];
                if ($num > $max) $max = $num;
            }
        }

        $this->sku = $prefix . '-' . str_pad($max + 1, 3, '0', STR_PAD_LEFT);
    }

    public function updatedName($value)
    {
        if (!$this->isEdit) {
            $this->slug = Str::slug($value);
        }
    }

    public function updatedIsTaxed($value)
    {
        if (!$value) {
            $this->tax_rate = 0;
        }
    }

    public function save()
    {
        $this->validate();

        $imagePath = $this->existingImage;

        if ($this->image) {
            $imagePath = $this->image->store('products', 'public');
            $fullPath = storage_path('app/public/' . $imagePath);
            Image::read($fullPath)->scale(width: 600)->save($fullPath, quality: 80);
        }

        $data = [
            'store_id' => $this->storeId,
            'category_id' => $this->category_id,
            'vendor_id' => $this->vendor_id ?: null,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'price' => $this->price,
            'cost_price' => $this->cost_price,
            'stock' => $this->stock,
            'min_stock' => $this->min_stock,
            'unit' => $this->unit,
            'is_taxed' => $this->is_taxed,
            'tax_rate' => $this->is_taxed ? $this->tax_rate : 0,
            'is_subscription' => $this->is_subscription,
            'subscription_days' => $this->is_subscription ? $this->subscription_days : 0,
            'description' => $this->description,
            'image' => $imagePath,
            'is_active' => $this->is_active,
        ];

        // Auto-generate barcode if empty on new product
        if (empty($data['barcode']) && !$this->isEdit) {
            $lock = \Illuminate\Support\Facades\Cache::lock('barcode-' . $this->storeId, 10);
            $lock->block(5);
            try {
                $store = \App\Models\Store::find($this->storeId);
                $lastProduct = Product::where('store_id', $this->storeId)
                    ->orderBy('id', 'desc')->first();
                $nextId = ($lastProduct ? $lastProduct->id : 0) + 1;
                $data['barcode'] = BarcodeService::generateEAN13(
                    $store->code ?? 'PST', $nextId
                );
            } finally {
                $lock->release();
            }
        }

        if ($this->isEdit) {
            Product::where('store_id', $this->storeId)->where('id', $this->productId)->update($data);
            \App\Services\ActivityLogger::log('update', 'Memperbarui produk: ' . $this->name);
            session()->flash('message', 'Produk berhasil diupdate.');
        } else {
            Product::create($data);
            \App\Services\ActivityLogger::log('create', 'Menambahkan produk baru: ' . $this->name);
            session()->flash('message', 'Produk berhasil ditambahkan.');
        }

        $this->redirect(route('products.index'));
    }

    public function render()
    {
        return view('livewire.product-form');
    }
}
