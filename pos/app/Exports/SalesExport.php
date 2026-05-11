<?php

namespace App\Exports;

use App\Models\Transaction;
use App\Models\Store;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class SalesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
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
        $storeId = $this->storeId ?? auth()->user()->store_id;
        $query = Transaction::with('items', 'user')
            ->where('status', 'completed')
            ->where('store_id', $storeId);

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
            'Rp ' . number_format($transaction->subtotal, 0, ',', '.'),
            'Rp ' . number_format($transaction->tax_amount, 0, ',', '.'),
            'Rp ' . number_format($transaction->total_amount, 0, ',', '.'),
            $transaction->payment_method,
            $transaction->user?->name ?: '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            6 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '3B82F6']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $store = Store::find($this->storeId);

                $sheet->insertNewRowBefore(1, 5);

                $sheet->mergeCells('A1:H1');
                $sheet->setCellValue('A1', 'LAPORAN PENJUALAN');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1F2937']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells('A2:H2');
                $storeName = $store?->name ?? 'Toko';
                $storeInfo = $storeName;
                if ($store?->address) $storeInfo .= ' | ' . $store->address;
                if ($store?->phone) $storeInfo .= ' | Telp: ' . $store->phone;
                $sheet->setCellValue('A2', $storeInfo);
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['size' => 11, 'color' => ['rgb' => '6B7280']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells('A3:H3');
                $dateRange = 'Periode: ' . ($this->dateFrom ?: 'Awal') . ' - ' . ($this->dateTo ?: 'Akhir');
                $sheet->setCellValue('A3', $dateRange);
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['size' => 11, 'color' => ['rgb' => '6B7280']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells('A4:H4');
                $sheet->setCellValue('A4', 'Tanggal cetak: ' . now()->format('d/m/Y H:i'));
                $sheet->getStyle('A4')->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['rgb' => '9CA3AF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->getStyle('A6:H6')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '3B82F6']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $storeId = $this->storeId ?? auth()->user()->store_id;
                $query = Transaction::where('status', 'completed')->where('store_id', $storeId);
                if ($this->dateFrom) $query->whereDate('created_at', '>=', $this->dateFrom);
                if ($this->dateTo) $query->whereDate('created_at', '<=', $this->dateTo);
                $allData = $query->get();

                $highestRow = $sheet->getHighestRow();
                $summaryRow = $highestRow + 2;

                $sheet->mergeCells("A{$summaryRow}:E{$summaryRow}");
                $sheet->setCellValue("A{$summaryRow}", 'TOTAL TRANSAKSI:');
                $sheet->getStyle("A{$summaryRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ]);
                $sheet->setCellValue("F{$summaryRow}", $allData->count());
                $sheet->getStyle("F{$summaryRow}")->applyFromArray(['font' => ['bold' => true, 'size' => 12]]);

                $summaryRow2 = $summaryRow + 1;
                $sheet->mergeCells("A{$summaryRow2}:E{$summaryRow2}");
                $sheet->setCellValue("A{$summaryRow2}", 'TOTAL PENDAPATAN:');
                $sheet->getStyle("A{$summaryRow2}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ]);
                $sheet->setCellValue("F{$summaryRow2}", 'Rp ' . number_format($allData->sum('total_amount'), 0, ',', '.'));
                $sheet->getStyle("F{$summaryRow2}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '059669']],
                ]);

                $summaryRow3 = $summaryRow + 2;
                $sheet->mergeCells("A{$summaryRow3}:E{$summaryRow3}");
                $sheet->setCellValue("A{$summaryRow3}", 'TOTAL PAJAK:');
                $sheet->getStyle("A{$summaryRow3}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ]);
                $sheet->setCellValue("F{$summaryRow3}", 'Rp ' . number_format($allData->sum('tax_amount'), 0, ',', '.'));
                $sheet->getStyle("F{$summaryRow3}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '7C3AED']],
                ]);

                $summaryRow4 = $summaryRow + 3;
                $sheet->mergeCells("A{$summaryRow4}:E{$summaryRow4}");
                $sheet->setCellValue("A{$summaryRow4}", 'RATA-RATA PER TRANSAKSI:');
                $sheet->getStyle("A{$summaryRow4}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ]);
                $avg = $allData->count() > 0 ? $allData->sum('total_amount') / $allData->count() : 0;
                $sheet->setCellValue("F{$summaryRow4}", 'Rp ' . number_format($avg, 0, ',', '.'));
                $sheet->getStyle("F{$summaryRow4}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '3B82F6']],
                ]);

                foreach (range('A', 'H') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
