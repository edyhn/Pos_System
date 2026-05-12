<?php

namespace App\Livewire;

use App\Models\RefundRequest;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Notifications\RefundRequestSubmitted;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Notification;

class CashierRequestRefund extends Component
{
    use WithPagination;

    public $storeId;
    public $selectedTransaction;
    public $selectedItem;
    public $conditionInfo = '';
    public $transactionItems = [];
    public $search = '';
    public $refundType = 'uang_kembali';
    public $refundAmount = 0;

    public function mount()
    {
        $this->storeId = auth()->user()->store_id;
    }

    public function updatedSelectedTransaction($value)
    {
        $this->selectedItem = null;
        $this->transactionItems = [];
        $this->refundAmount = 0;
        if ($value) {
            $transaction = Transaction::with('items')->find($value);
            if ($transaction) {
                $this->transactionItems = $transaction->items->toArray();
            }
        }
    }

    public function updatedSelectedItem($value)
    {
        if ($value) {
            $item = collect($this->transactionItems)->firstWhere('id', $value);
            if ($item) {
                $this->refundAmount = (float) (($item['subtotal'] ?? 0) - ($item['discount_amount'] ?? 0));
            }
        } else {
            $this->refundAmount = 0;
        }
    }

    public function requestRefund()
    {
        try {
            $this->validate([
                'selectedTransaction' => 'required',
                'selectedItem' => 'required',
                'conditionInfo' => 'required|min:5',
                'refundType' => 'required|in:uang_kembali,tukar_barang',
                'refundAmount' => 'required|numeric|min:0',
            ]);

            $existingRefund = RefundRequest::where('transaction_item_id', $this->selectedItem)
                ->whereIn('status', ['pending', 'approved'])
                ->exists();

            if ($existingRefund) {
                session()->flash('error', 'Item ini sudah pernah direfund atau sedang dalam proses refund.');
                return;
            }

            $refundRequest = RefundRequest::create([
                'store_id' => $this->storeId,
                'transaction_id' => $this->selectedTransaction,
                'transaction_item_id' => $this->selectedItem,
                'user_id' => auth()->id(),
                'condition_info' => $this->conditionInfo,
                'refund_type' => $this->refundType,
                'refund_amount' => $this->refundAmount,
                'status' => 'pending',
            ]);

            $owners = \App\Models\User::where('store_id', $this->storeId)
                ->where('role', 'owner')
                ->where('is_active', true)
                ->get();

            Notification::send($owners, new RefundRequestSubmitted($refundRequest));

            \App\Services\ActivityLogger::log('create', 'Request refund untuk transaksi #' . ($refundRequest->transaction?->invoice_number ?? $this->selectedTransaction));

            session()->flash('message', 'Request refund dikirim ke owner.');
            $this->selectedTransaction = null;
            $this->selectedItem = null;
            $this->transactionItems = [];
            $this->conditionInfo = '';
            $this->refundType = 'uang_kembali';
            $this->refundAmount = 0;
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengirim request refund: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $transactionIdsWithRefunds = RefundRequest::whereIn('status', ['pending', 'approved'])
            ->where('store_id', $this->storeId)
            ->pluck('transaction_id')
            ->unique()
            ->toArray();

        $transactions = Transaction::where('store_id', $this->storeId)
            ->where('status', 'completed')
            ->whereNotIn('id', $transactionIdsWithRefunds)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        $myRequests = RefundRequest::where('store_id', $this->storeId)
            ->where('user_id', auth()->id())
            ->with('transaction', 'transactionItem')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.cashier-request-refund', compact('transactions', 'myRequests'));
    }
}
