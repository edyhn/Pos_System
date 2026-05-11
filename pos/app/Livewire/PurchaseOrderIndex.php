<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class PurchaseOrderIndex extends Component
{
    use WithPagination;

    public $storeId;
    public $search = '';
    public $statusFilter = '';

    public function mount()
    {
        $this->storeId = auth()->user()->store_id;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        $po = PurchaseOrder::where('store_id', $this->storeId)->findOrFail($id);

        if (!in_array($po->status, ['draft', 'cancelled'])) {
            session()->flash('message', 'Hanya PO dengan status draft/cancelled yang bisa dihapus.');
            return;
        }

        $poNumber = $po->po_number;
        $po->delete();

        \App\Services\ActivityLogger::log('delete', 'Menghapus PO: ' . $poNumber);
        session()->flash('message', 'PO ' . $poNumber . ' berhasil dihapus.');
    }

    public function updateStatus($id, $status)
    {
        $po = PurchaseOrder::where('store_id', $this->storeId)->with('items')->findOrFail($id);

        if ($status === 'received' && $po->status === 'sent') {
            DB::transaction(function () use ($po) {
                $po->update(['status' => 'received']);

                foreach ($po->items as $item) {
                    Product::where('id', $item->product_id)->increment('stock', $item->quantity);

                    StockMovement::create([
                        'store_id' => $this->storeId,
                        'product_id' => $item->product_id,
                        'user_id' => auth()->id(),
                        'reference_type' => 'purchase_order',
                        'reference_id' => $po->id,
                        'type' => 'in',
                        'quantity' => $item->quantity,
                        'note' => 'Penerimaan PO #' . $po->po_number,
                    ]);
                }
            });

            session()->flash('message', 'PO #' . $po->po_number . ' diterima, stok bertambah.');
        } else {
            $po->update(['status' => $status]);
            session()->flash('message', 'PO status diupdate ke ' . $status);
        }
    }

    public function render()
    {
        $query = PurchaseOrder::where('store_id', $this->storeId)->with('vendor', 'user');

        if ($this->search) {
            $query->where('po_number', 'like', '%' . $this->search . '%');
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.purchase-order-index', compact('orders'));
    }
}
