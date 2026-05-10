<?php

namespace App\Livewire;

use App\Models\Transaction;
use Livewire\Component;
use Livewire\WithPagination;

class TransactionHistory extends Component
{
    use WithPagination;

    public $storeId;
    public $search = '';
    public $dateFrom;
    public $dateTo;

    public function mount()
    {
        $this->storeId = auth()->user()->store_id;
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDateFrom() { $this->resetPage(); }
    public function updatingDateTo() { $this->resetPage(); }

    public function render()
    {
        $query = Transaction::when($this->storeId, fn($q) => $q->where('store_id', $this->storeId));

        if ($this->search) {
            $query->where('invoice_number', 'like', '%' . $this->search . '%');
        }
        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        $transactions = $query->with('user', 'store')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.transaction-history', compact('transactions'));
    }
}
