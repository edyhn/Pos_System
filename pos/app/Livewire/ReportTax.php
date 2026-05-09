<?php

namespace App\Livewire;

use App\Models\Transaction;
use Livewire\Component;

class ReportTax extends Component
{
    public $storeId;
    public $dateFrom;
    public $dateTo;

    public function mount()
    {
        $this->storeId = auth()->user()->store_id;
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

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

        $summary = (clone $query)->selectRaw('
            COUNT(*) as total,
            COALESCE(SUM(tax_amount), 0) as total_tax,
            COALESCE(SUM(total_amount), 0) as total_sales
        ')->first();

        return view('livewire.report-tax', compact('transactions', 'summary'));
    }
}
