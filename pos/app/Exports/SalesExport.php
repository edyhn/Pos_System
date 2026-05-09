<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesExport implements FromQuery, WithHeadings, WithMapping
{
    protected $storeId;
    protected $dateFrom;
    protected $dateTo;

    public function __construct($storeId, $dateFrom, $dateTo)
    {
        $this->storeId = $storeId;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function query()
    {
        $query = Transaction::with('items', 'user')->where('status', 'completed');

        if ($this->storeId) {
            $query->where('store_id', $this->storeId);
        }

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        return $query->latest();
    }

    public function headings(): array
    {
        return ['Invoice', 'Tanggal', 'Customer', 'Subtotal', 'Pajak', 'Total', 'Metode', 'Kasir'];
    }

    public function map($transaction): array
    {
        return [
            $transaction->invoice_number,
            $transaction->created_at->format('d/m/Y H:i'),
            $transaction->customer_name ?: '-',
            $transaction->subtotal,
            $transaction->tax_amount,
            $transaction->total_amount,
            $transaction->payment_method,
            $transaction->user?->name ?: '-',
        ];
    }
}
