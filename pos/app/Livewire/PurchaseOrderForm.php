<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PoItem;
use App\Models\StockMovement;
use App\Models\Vendor;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class PurchaseOrderForm extends Component
{
    public $storeId;
    public $poId;
    public $vendor_id = '';
    public $notes = '';
    public $items = [];
    public $isEdit = false;
    public $vendors;
    public $products;

    public function mount($id = null)
    {
        $this->storeId = auth()->user()->store_id;
        $this->vendors = Vendor::where('store_id', $this->storeId)->where('is_active', true)->get();
        $this->products = Product::where('store_id', $this->storeId)->where('is_active', true)->get();

        if ($id) {
            $this->isEdit = true;
            $this->poId = $id;
            $po = PurchaseOrder::where('store_id', $this->storeId)->with('items')->findOrFail($id);
            $this->vendor_id = $po->vendor_id;
            $this->notes = $po->notes;
            $this->items = $po->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'price' => (float) $item->price,
                    'subtotal' => (float) $item->subtotal,
                ];
            })->toArray();
        }
    }

    public function addItem()
    {
        $this->items[] = [
            'product_id' => '',
            'product_name' => '',
            'quantity' => 1,
            'price' => 0,
            'subtotal' => 0,
        ];
    }

    public function updatedItems($value, $key)
    {
        if (str_ends_with($key, '.product_id')) {
            $index = explode('.', $key)[0];
            $product = Product::find($value);
            if ($product) {
                $this->items[$index]['product_name'] = $product->name;
                $this->items[$index]['price'] = (float) $product->cost_price ?: (float) $product->price;
                $this->items[$index]['subtotal'] = $this->items[$index]['quantity'] * $this->items[$index]['price'];
            }
        }

        if (str_ends_with($key, '.quantity') || str_ends_with($key, '.price')) {
            $index = explode('.', $key)[0];
            $this->items[$index]['subtotal'] = $this->items[$index]['quantity'] * $this->items[$index]['price'];
        }
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save()
    {
        if (empty($this->items)) {
            session()->flash('error', 'Minimal 1 item.');
            return;
        }

        $poNumber = 'PO-' . date('Ymd') . '-' . str_pad(PurchaseOrder::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($poNumber) {
            $po = PurchaseOrder::create([
                'store_id' => $this->storeId,
                'vendor_id' => $this->vendor_id ?: null,
                'user_id' => auth()->id(),
                'po_number' => $poNumber,
                'status' => 'draft',
                'notes' => $this->notes,
            ]);

            foreach ($this->items as $item) {
                PoItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price'],
                ]);
            }

            session()->flash('message', 'PO berhasil dibuat: ' . $poNumber);
        });

        return redirect()->route('purchase-orders.index');
    }

    public function render()
    {
        return view('livewire.purchase-order-form');
    }
}
