<?php

namespace App\Livewire;

use App\Models\RefundRequest;
use App\Models\Subscription;
use App\Models\Product;
use App\Models\StockMovement;
use App\Notifications\RequestApproved;
use App\Notifications\RequestRejected;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class ApprovalRefundIndex extends Component
{
    use WithPagination;

    public $storeId;
    public $search = '';
    public $statusFilter = '';
    public $showApproveForm = false;
    public $requestId;
    public $refundAmount = 0;
    public $refundType = 'uang_kembali';
    public $ownerNote = '';

    public function mount()
    {
        $this->storeId = auth()->user()->store_id;
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatusFilter() { $this->resetPage(); }

    public function showForm($id, $amount)
    {
        $this->showApproveForm = true;
        $this->requestId = $id;
        $this->refundAmount = $amount;
    }

    public function approve()
    {
        $this->validate([
            'refundAmount' => 'required|numeric|min:0',
            'refundType' => 'required|in:uang_kembali,tukar_barang',
        ]);

        try {
            DB::transaction(function () {
                $req = RefundRequest::with('transaction.items', 'user')->findOrFail($this->requestId);
                $transaction = $req->transaction;

                $item = $req->transactionItem ?: $transaction?->items->first();
                if ($item) {
                    $maxRefund = max(0, ($item->subtotal ?? 0) - ($item->discount_amount ?? 0));
                    if ($this->refundAmount > $maxRefund) {
                        throw new \RuntimeException('Jumlah refund tidak boleh melebihi Rp ' . number_format($maxRefund, 0, ',', '.'));
                    }
                }

                $req->update([
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'refund_amount' => $this->refundAmount,
                    'refund_type' => $this->refundType,
                    'owner_note' => $this->ownerNote,
                    'approved_at' => now(),
                ]);

                if ($transaction) {
                    $transaction->update(['status' => 'refunded']);

                    Subscription::where('transaction_id', $transaction->id)
                        ->update(['status' => 'refunded']);

                    if ($item) {
                        Product::where('id', $item->product_id)->increment('stock', $item->quantity);

                        StockMovement::create([
                            'store_id' => $this->storeId,
                            'product_id' => $item->product_id,
                            'user_id' => auth()->id(),
                            'reference_type' => 'refund',
                            'reference_id' => $transaction->id,
                            'type' => 'in',
                            'quantity' => $item->quantity,
                            'note' => 'Refund #' . $transaction->invoice_number,
                        ]);
                    }
                }

                $invoiceNumber = $transaction?->invoice_number ?? '-';
                if ($req->user) {
                    $req->user->notify(new RequestApproved('refund', $invoiceNumber));
                }

                \App\Services\ActivityLogger::log('approve', 'Menyetujui refund: ' . $invoiceNumber);
                session()->flash('message', 'Refund disetujui.');
                $this->showApproveForm = false;
            });
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memproses refund: ' . $e->getMessage());
        }
    }

    public function reject()
    {
        try {
            $req = RefundRequest::with('transaction', 'user')->findOrFail($this->requestId);
            $req->update([
                'status' => 'rejected',
                'approved_by' => auth()->id(),
                'owner_note' => $this->ownerNote ?: 'Ditolak oleh owner',
                'approved_at' => now(),
            ]);

            $invoiceNumber = $req->transaction?->invoice_number ?? '-';
            if ($req->user) {
                $req->user->notify(new RequestRejected('refund', $invoiceNumber, $req->owner_note));
            }

            \App\Services\ActivityLogger::log('reject', 'Menolak refund: ' . $invoiceNumber);
            session()->flash('message', 'Refund ditolak.');
            $this->showApproveForm = false;
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menolak refund: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = RefundRequest::where('store_id', $this->storeId)
            ->with('transaction', 'user', 'approver');

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->search) {
            $query->whereHas('transaction', fn($q) => $q->where('invoice_number', 'like', '%' . $this->search . '%'));
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.approval-refund-index', compact('requests'));
    }
}
