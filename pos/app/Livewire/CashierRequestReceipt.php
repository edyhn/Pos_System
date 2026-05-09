<?php

namespace App\Livewire;

use App\Models\ReceiptReprintRequest;
use App\Models\Transaction;
use App\Notifications\ReceiptReprintRequested;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Notification;

class CashierRequestReceipt extends Component
{
    use WithPagination;

    public $storeId;
    public $selectedTransaction;
    public $reason = '';
    public $search = '';

    public function mount()
    {
        $this->storeId = auth()->user()->store_id;
    }

    public function requestReprint()
    {
        $this->validate([
            'selectedTransaction' => 'required',
            'reason' => 'required|min:5',
        ]);

        $reprintRequest = ReceiptReprintRequest::create([
            'store_id' => $this->storeId,
            'transaction_id' => $this->selectedTransaction,
            'user_id' => auth()->id(),
            'status' => 'pending',
            'reason' => $this->reason,
        ]);

        $owners = \App\Models\User::where('store_id', $this->storeId)
            ->where('role', 'owner')
            ->where('is_active', true)
            ->get();

        Notification::send($owners, new ReceiptReprintRequested($reprintRequest));

        session()->flash('message', 'Request cetak ulang dikirim ke owner.');
        $this->selectedTransaction = null;
        $this->reason = '';
    }

    public function render()
    {
        $transactions = Transaction::where('store_id', $this->storeId)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        $myRequests = ReceiptReprintRequest::where('store_id', $this->storeId)
            ->where('user_id', auth()->id())
            ->with('transaction')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.cashier-request-receipt', compact('transactions', 'myRequests'));
    }
}
