<?php

namespace App\Livewire;

use App\Models\Transaction;
use App\Models\Store;
use Livewire\Component;
use Livewire\WithPagination;

class ReportSales extends Component
{
    public $storeId;
    public $dateFrom;
    public $dateTo;
    public $storeFilter = '';
    public $stores;

    public function mount()
    {
        $user = auth()->user();
        $this->storeId = $user->store_id;
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');

        if ($user->isOwner()) {
            $this->stores = Store::where('is_active', true)->get();
        }
    }

    public function render()
    {
        $query = Transaction::where('status', 'completed');

        if ($this->storeFilter) {
            $query->where('store_id', $this->storeFilter);
        } elseif ($this->storeId) {
            $query->where('store_id', $this->storeId);
        }

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        $transactions = $query->with('store', 'user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $summary = (clone $query)->selectRaw('
            COUNT(*) as total_transactions,
            COALESCE(SUM(total_amount), 0) as total_revenue,
            COALESCE(SUM(tax_amount), 0) as total_tax
        ')->first();

        return view('livewire.report-sales', compact('transactions', 'summary'));
    }
}
