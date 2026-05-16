<?php

namespace App\Livewire;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ReportTax extends Component
{
    use WithPagination;

    public $storeId;
    public $dateFrom;
    public $dateTo;
    public array $monthlyChartData = [];

    public function mount()
    {
        $this->storeId = auth()->user()->store_id;
        $this->dateFrom = now()->startOfYear()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function updatedDateFrom() { $this->resetPage(); }
    public function updatedDateTo() { $this->resetPage(); }

    public function render()
    {
        $query = Transaction::where('status', 'completed')->where('tax_amount', '>', 0);

        if ($this->storeId) {
            $query->where('store_id', $this->storeId);
        }

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        $transactions = $query->with('store', 'user')->orderBy('created_at', 'desc')->paginate(20);

        $summary = (clone $query)->reorder()->selectRaw('
            COUNT(*) as total,
            COALESCE(SUM(tax_amount), 0) as total_tax,
            COALESCE(SUM(total_amount), 0) as total_sales
        ')->first();

        $this->monthlyChartData = $this->getMonthlyTax();

        return view('livewire.report-tax', compact('transactions', 'summary'));
    }

    protected function getMonthlyTax(): array
    {
        $driver = DB::connection()->getDriverName();
        $monthExpr = $driver === 'sqlite'
            ? "CAST(strftime('%m', created_at) AS INTEGER)"
            : 'MONTH(created_at)';

        $rows = Transaction::where('status', 'completed')
            ->where('tax_amount', '>', 0)
            ->where('store_id', $this->storeId)
            ->whereYear('created_at', now()->year)
            ->selectRaw("{$monthExpr} as month, COALESCE(SUM(tax_amount), 0) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $labels = [];
        $values = [];
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        for ($m = 1; $m <= 12; $m++) {
            $labels[] = $months[$m - 1];
            $values[] = (int) ($rows[$m] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }
}
