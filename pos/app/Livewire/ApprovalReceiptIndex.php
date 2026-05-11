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
    public $search = '';
    public $statusFilter = '';

    public function mount()
    {
        $this->storeId = auth()->user()->store_id;
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatusFilter() { $this->resetPage(); }

    public function approve($id)
    {
        try {
            $request = ReceiptReprintRequest::with('transaction', 'user')->findOrFail($id);
            $request->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            if ($request->user) {
                $invoiceNumber = $request->transaction?->invoice_number ?? '-';
                $request->user->notify(new RequestApproved('reprint', $invoiceNumber));
            }

            \App\Services\ActivityLogger::log('approve', 'Menyetujui cetak ulang: ' . ($request->transaction?->invoice_number ?? '-'));
            session()->flash('message', 'Request cetak ulang disetujui.');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menyetujui: ' . $e->getMessage());
        }
    }

    public function reject($id)
    {
        try {
            $request = ReceiptReprintRequest::with('transaction', 'user')->findOrFail($id);
            $request->update([
                'status' => 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            if ($request->user) {
                $invoiceNumber = $request->transaction?->invoice_number ?? '-';
                $request->user->notify(new RequestRejected('reprint', $invoiceNumber, 'Ditolak oleh owner'));
            }

            \App\Services\ActivityLogger::log('reject', 'Menolak cetak ulang: ' . ($request->transaction?->invoice_number ?? '-'));
            session()->flash('message', 'Request cetak ulang ditolak.');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menolak: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = ReceiptReprintRequest::where('store_id', $this->storeId)
            ->with('transaction', 'user', 'approver');

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->search) {
            $query->whereHas('transaction', fn($q) => $q->where('invoice_number', 'like', '%' . $this->search . '%'));
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.approval-receipt-index', compact('requests'));
    }
}
