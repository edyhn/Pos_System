<?php

namespace App\Exports;

use App\Models\Product;
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
use PhpOffice\PhpSpreadsheet\Style\Border;

class StockExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    protected $storeId;

    public function __construct($storeId)
    {
        $this->storeId = $storeId;
    }

    public function query()
    {
        return Product::where('store_id', $this->storeId)
            ->where('is_active', true)
            ->with('category');
    }

    public function headings(): array
    {
        return ['Produk', 'SKU', 'Kategori', 'Stok', 'Min Stok', 'Harga', 'Status'];
    }

    public function map($product): array
    {
        return [
            $product->name,
            $product->sku ?: '-',
            $product->category?->name ?: '-',
            $product->stock,
            $product->min_stock,
            'Rp ' . number_format($product->price, 0, ',', '.'),
            $product->isLowStock() ? 'Stok Minim' : 'Normal',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            5 => [
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

                $sheet->insertNewRowBefore(1, 4);

                $sheet->mergeCells('A1:G1');
                $sheet->setCellValue('A1', 'LAPORAN STOK PRODUK');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1F2937']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells('A2:G2');
                $storeName = $store?->name ?? 'Toko';
                $storeInfo = $storeName;
                if ($store?->address) $storeInfo .= ' | ' . $store->address;
                if ($store?->phone) $storeInfo .= ' | Telp: ' . $store->phone;
                $sheet->setCellValue('A2', $storeInfo);
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['size' => 11, 'color' => ['rgb' => '6B7280']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells('A3:G3');
                $sheet->setCellValue('A3', 'Tanggal cetak: ' . now()->format('d/m/Y H:i'));
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['rgb' => '9CA3AF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->getStyle('A5:G5')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '3B82F6']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $highestRow = $sheet->getHighestRow();
                $summaryRow = $highestRow + 2;
                $sheet->mergeCells("A{$summaryRow}:F{$summaryRow}");
                $sheet->setCellValue("A{$summaryRow}", 'TOTAL PRODUK:');
                $sheet->getStyle("A{$summaryRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ]);
                $totalProducts = Product::where('store_id', $this->storeId)->where('is_active', true)->count();
                $sheet->setCellValue("G{$summaryRow}", $totalProducts);
                $sheet->getStyle("G{$summaryRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '3B82F6']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $summaryRow2 = $summaryRow + 1;
                $sheet->mergeCells("A{$summaryRow2}:F{$summaryRow2}");
                $sheet->setCellValue("A{$summaryRow2}", 'PRODUK STOK MINIM:');
                $sheet->getStyle("A{$summaryRow2}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'EF4444']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ]);
                $lowStockCount = Product::where('store_id', $this->storeId)->where('is_active', true)
                    ->whereColumn('stock', '<=', 'min_stock')->count();
                $sheet->setCellValue("G{$summaryRow2}", $lowStockCount);
                $sheet->getStyle("G{$summaryRow2}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'EF4444']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                foreach (range('A', 'G') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
