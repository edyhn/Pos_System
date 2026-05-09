<?php

namespace App\Livewire;

use App\Models\ReceiptReprintRequest;
use App\Notifications\RequestApproved;
use App\Notifications\RequestRejected;
use Livewire\Component;
use Livewire\WithPagination;

class ApprovalReceiptIndex extends Component
{
    use WithPagination;

    public $storeId;

    public function mount()
    {
        $this->storeId = auth()->user()->store_id;
    }

    public function approve($id)
    {
        $request = ReceiptReprintRequest::with('transaction', 'user')->findOrFail($id);
        $request->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        if ($request->user) {
            $request->user->notify(new RequestApproved('reprint', $request->transaction->invoice_number));
        }

        session()->flash('message', 'Request cetak ulang disetujui.');
    }

    public function reject($id)
    {
        $request = ReceiptReprintRequest::with('transaction', 'user')->findOrFail($id);
        $request->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        if ($request->user) {
            $request->user->notify(new RequestRejected('reprint', $request->transaction->invoice_number, 'Ditolak oleh owner'));
        }

        session()->flash('message', 'Request cetak ulang ditolak.');
    }

    public function render()
    {
        $requests = ReceiptReprintRequest::where('store_id', $this->storeId)
            ->with('transaction', 'user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.approval-receipt-index', compact('requests'));
    }
}
