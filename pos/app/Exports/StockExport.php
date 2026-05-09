<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockExport implements FromQuery, WithHeadings, WithMapping
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
            $product->price,
            $product->isLowStock() ? 'Stok Minim' : 'Normal',
        ];
    }
}
