<?php

namespace App\Livewire;

use App\Models\RefundRequest;
use App\Models\Transaction;
use App\Notifications\RefundRequestSubmitted;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Notification;

class CashierRequestRefund extends Component
{
    use WithPagination;

    public $storeId;
    public $selectedTransaction;
    public $conditionInfo = '';
    public $search = '';

    public function mount()
    {
        $this->storeId = auth()->user()->store_id;
    }

    public function requestRefund()
    {
        $this->validate([
            'selectedTransaction' => 'required',
            'conditionInfo' => 'required|min:5',
        ]);

        $refundRequest = RefundRequest::create([
            'store_id' => $this->storeId,
            'transaction_id' => $this->selectedTransaction,
            'user_id' => auth()->id(),
            'condition_info' => $this->conditionInfo,
            'status' => 'pending',
        ]);

        $owners = \App\Models\User::where('store_id', $this->storeId)
            ->where('role', 'owner')
            ->where('is_active', true)
            ->get();

        Notification::send($owners, new RefundRequestSubmitted($refundRequest));

        session()->flash('message', 'Request refund dikirim ke owner.');
        $this->selectedTransaction = null;
        $this->conditionInfo = '';
    }

    public function render()
    {
        $transactions = Transaction::where('store_id', $this->storeId)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        $myRequests = RefundRequest::where('store_id', $this->storeId)
            ->where('user_id', auth()->id())
            ->with('transaction')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.cashier-request-refund', compact('transactions', 'myRequests'));
    }
}
