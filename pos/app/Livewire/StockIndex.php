<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class StockIndex extends Component
{
    use WithPagination;

    public $storeId;
    public $search = '';
    public $showLowStock = false;
    public $showInactive = false;

    public function mount()
    {
        $this->storeId = auth()->user()->store_id;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function toggleActive($id)
    {
        $product = Product::where('store_id', $this->storeId)->findOrFail($id);
        $product->update(['is_active' => !$product->is_active]);
    }

    public function render()
    {
        $query = Product::where('store_id', $this->storeId)->with('category');

        if (!$this->showInactive) {
            $query->where('is_active', true);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('sku', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->showLowStock) {
            $query->whereColumn('stock', '<=', 'min_stock');
        }

        $products = $query->orderBy('name')->paginate(20);

        return view('livewire.stock-index', compact('products'));
    }
}
