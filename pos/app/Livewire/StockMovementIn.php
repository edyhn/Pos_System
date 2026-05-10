<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class StockMovementIn extends Component
{
    use WithPagination;

    public $storeId;
    public $search = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $showForm = false;
    public $product_id = '';
    public $quantity = 1;
    public $referenceType = 'manual';
    public $selectedPo = '';
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
        $this->referenceType = 'manual';
        $this->selectedPo = '';
        $this->note = '';
    }

    public function addStockIn()
    {
        $this->validate([
            'product_id' => ['required', \Illuminate\Validation\Rule::exists('products', 'id')->where(fn($q) => $q->where('store_id', $this->storeId))],
            'quantity' => 'required|integer|min:1',
            'referenceType' => 'required|in:manual,po',
            'selectedPo' => ['required_if:referenceType,po', \Illuminate\Validation\Rule::exists('purchase_orders', 'id')->where(fn($q) => $q->where('store_id', $this->storeId))],
            'note' => 'nullable|string|max:500',
        ]);

        $product = Product::findOrFail($this->product_id);

        DB::transaction(function () use ($product) {
            $reference = null;
            if ($this->referenceType === 'po') {
                $po = PurchaseOrder::find($this->selectedPo);
                $reference = 'PO #' . ($po ? $po->po_number : $this->selectedPo);
            } else {
                $reference = 'Manual';
            }

            StockMovement::create([
                'store_id' => $this->storeId,
                'product_id' => $this->product_id,
                'user_id' => auth()->id(),
                'reference_type' => $this->referenceType === 'po' ? 'purchase_order' : 'manual',
                'reference_id' => $this->referenceType === 'po' ? $this->selectedPo : null,
                'type' => 'in',
                'quantity' => $this->quantity,
                'note' => $this->note,
            ]);

            $product->increment('stock', $this->quantity);

            \App\Services\ActivityLogger::log('create', 'Barang masuk: ' . $product->name . ' (' . $this->quantity . ') via ' . $reference);
        });

        session()->flash('message', 'Barang masuk berhasil dicatat.');
        $this->showForm = false;
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

    public function getSentPosProperty()
    {
        return PurchaseOrder::where('store_id', $this->storeId)
            ->where('status', 'sent')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function render()
    {
        $query = StockMovement::where('store_id', $this->storeId)
            ->where('type', 'in')
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

        return view('livewire.stock-movement-in', compact('movements'));
    }
}
