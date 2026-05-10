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
    public array $dailyChartData = [];

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

    public function updatedDateFrom() { $this->resetPage(); }
    public function updatedDateTo() { $this->resetPage(); }
    public function updatedStoreFilter() { $this->resetPage(); }

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

        $this->dailyChartData = $this->getDailySales();

        return view('livewire.report-sales', compact('transactions', 'summary'));
    }

    protected function getDailySales(): array
    {
        $query = Transaction::where('status', 'completed')
            ->whereDate('created_at', '>=', $this->dateFrom)
            ->whereDate('created_at', '<=', $this->dateTo);

        if ($this->storeFilter) {
            $query->where('store_id', $this->storeFilter);
        } elseif ($this->storeId) {
            $query->where('store_id', $this->storeId);
        }

        $rows = $query->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $labels = [];
        $values = [];
        $period = \Carbon\CarbonPeriod::create($this->dateFrom, $this->dateTo);
        foreach ($period as $date) {
            $d = $date->format('Y-m-d');
            $labels[] = $date->format('d/m');
            $values[] = (int) ($rows[$d] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }
}
