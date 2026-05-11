<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function decrementStock(
        Product $product,
        int $quantity,
        string $referenceType,
        ?int $referenceId,
        int $storeId,
        int $userId,
        string $note = '',
    ): void {
        DB::transaction(function () use ($product, $quantity, $referenceType, $referenceId, $storeId, $userId, $note) {
            $product->decrement('stock', $quantity);

            StockMovement::create([
                'store_id' => $storeId,
                'product_id' => $product->id,
                'user_id' => $userId,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'type' => 'out',
                'quantity' => $quantity,
                'note' => $note ?: "Stok keluar: {$product->name} x{$quantity}",
            ]);
        });
    }

    public function incrementStock(
        Product $product,
        int $quantity,
        string $referenceType,
        ?int $referenceId,
        int $storeId,
        int $userId,
        string $note = '',
    ): void {
        DB::transaction(function () use ($product, $quantity, $referenceType, $referenceId, $storeId, $userId, $note) {
            $product->increment('stock', $quantity);

            StockMovement::create([
                'store_id' => $storeId,
                'product_id' => $product->id,
                'user_id' => $userId,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'type' => 'in',
                'quantity' => $quantity,
                'note' => $note ?: "Stok masuk: {$product->name} x{$quantity}",
            ]);
        });
    }

    public function validateStockAvailability(Product $product, int $quantity): void
    {
        if ($product->stock < $quantity) {
            throw new \RuntimeException("Stok {$product->name} tidak mencukupi. Tersedia: {$product->stock}, diminta: {$quantity}");
        }
    }
}
