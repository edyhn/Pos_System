<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\StockMovement;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class StockMovementOut extends Component
{
    use WithPagination;

    public $storeId;
    public $search = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $showForm = false;
    public $product_id = '';
    public $quantity = 1;
    public $referenceType = 'rusak';
    public $note = '';
    public $productSearch = '';

    public function mount()
    {
        $this->storeId = auth()->user()->store_id;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function openForm()
    {
        $this->showForm = true;
        $this->product_id = '';
        $this->quantity = 1;
        $this->referenceType = 'rusak';
        $this->note = '';
    }

    public function getProductsProperty()
    {
        $q = Product::where('store_id', $this->storeId)->where('is_active', true);
        if ($this->productSearch) {
            $q->where(function ($query) {
                $query->where('name', 'like', '%' . $this->productSearch . '%')
                    ->orWhere('sku', 'like', '%' . $this->productSearch . '%');
            });
        }
        return $q->orderBy('name')->get();
    }

    public function addStockOut()
    {
        $this->validate([
            'product_id' => ['required', \Illuminate\Validation\Rule::exists('products', 'id')->where(fn($q) => $q->where('store_id', $this->storeId))],
            'quantity' => 'required|integer|min:1',
            'referenceType' => 'required|in:rusak,expired,lainnya',
            'note' => 'required|min:3',
        ]);

        try {
            DB::transaction(function () {
                $query = Product::where('store_id', $this->storeId)
                    ->where('id', $this->product_id);
                $product = DB::connection()->getDriverName() === 'sqlite'
                    ? $query->firstOrFail()
                    : $query->lockForUpdate()->firstOrFail();

                if ($this->quantity > $product->stock) {
                    throw new \RuntimeException('Stok tidak mencukupi. Stok saat ini: ' . $product->stock);
                }

                StockMovement::create([
                    'store_id' => $this->storeId,
                    'product_id' => $this->product_id,
                    'user_id' => auth()->id(),
                    'reference_type' => $this->referenceType,
                    'reference_id' => null,
                    'type' => 'out',
                    'quantity' => $this->quantity,
                    'note' => $this->note,
                ]);

                $product->decrement('stock', $this->quantity);

                \App\Services\ActivityLogger::log('create', 'Barang keluar: ' . $product->name . ' (' . $this->quantity . ') - ' . $this->referenceType);
            });

            session()->flash('message', 'Barang keluar berhasil dicatat.');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
        $this->showForm = false;
    }

    public function render()
    {
        $query = StockMovement::where('store_id', $this->storeId)
            ->where('type', 'out')
            ->with('product', 'user');

        if ($this->search) {
            $query->whereHas('product', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        $movements = $query->latest()->paginate(20);

        return view('livewire.stock-movement-out', compact('movements'));
    }
}
