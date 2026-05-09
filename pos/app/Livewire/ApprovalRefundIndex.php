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
    public $showApproveForm = false;
    public $requestId;
    public $refundAmount = 0;
    public $refundType = 'prorata';
    public $ownerNote = '';

    public function mount()
    {
        $this->storeId = auth()->user()->store_id;
    }

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
            'refundType' => 'required|in:full,prorata',
        ]);

        DB::transaction(function () {
            $req = RefundRequest::with('transaction.items', 'user')->findOrFail($this->requestId);
            $transaction = $req->transaction;

            $req->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'refund_amount' => $this->refundAmount,
                'refund_type' => $this->refundType,
                'owner_note' => $this->ownerNote,
                'approved_at' => now(),
            ]);

            $transaction->update(['status' => 'refunded']);

            Subscription::where('transaction_id', $transaction->id)
                ->update(['status' => 'refunded']);

            foreach ($transaction->items as $item) {
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

            if ($req->user) {
                $req->user->notify(new RequestApproved('refund', $transaction->invoice_number));
            }

            session()->flash('message', 'Refund disetujui.');
            $this->showApproveForm = false;
        });
    }

    public function reject()
    {
        $req = RefundRequest::with('transaction', 'user')->findOrFail($this->requestId);
        $req->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'owner_note' => $this->ownerNote ?: 'Ditolak oleh owner',
            'approved_at' => now(),
        ]);

        if ($req->user) {
            $req->user->notify(new RequestRejected('refund', $req->transaction->invoice_number, $req->owner_note));
        }

        session()->flash('message', 'Refund ditolak.');
        $this->showApproveForm = false;
    }

    public function render()
    {
        $requests = RefundRequest::where('store_id', $this->storeId)
            ->with('transaction', 'user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.approval-refund-index', compact('requests'));
    }
}
