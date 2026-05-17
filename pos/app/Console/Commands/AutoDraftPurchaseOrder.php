<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PoItem;
use App\Models\Store;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AutoDraftPurchaseOrder extends Command
{
    protected $signature = 'pos:auto-draft-po';
    protected $description = 'Auto-create draft PO for products with stock below minimum';

    public function handle()
    {
        $stores = Store::where('is_active', true)->get();

        foreach ($stores as $store) {
            $lowStockProducts = Product::where('store_id', $store->id)
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->where(function ($q2) {
                        $q2->whereColumn('stock', '<=', 'min_stock')
                           ->where('min_stock', '>', 0);
                    })->orWhere('stock', 0);
                })
                ->get();

            if ($lowStockProducts->isEmpty()) {
                continue;
            }

            $lock = Cache::lock('po-auto-number-' . date('Ymd'), 10);
            $lock->block(5);
            $poNumber = 'PO-AUTO-' . date('Ymd') . '-' . str_pad(PurchaseOrder::whereDate('created_at', today())->where('store_id', $store->id)->count() + 1, 4, '0', STR_PAD_LEFT);
            $lock->release();

            DB::transaction(function () use ($store, $lowStockProducts, $poNumber) {
                $owner = User::where('store_id', $store->id)->where('role', 'owner')->where('is_active', true)->first();

                $po = PurchaseOrder::create([
                    'store_id' => $store->id,
                    'vendor_id' => null,
                    'user_id' => $owner?->id ?? 1,
                    'po_number' => $poNumber,
                    'status' => 'draft',
                    'is_auto_draft' => true,
                    'notes' => 'Auto draft: stok di bawah minimum',
                ]);

                foreach ($lowStockProducts as $product) {
                    $qty = $product->min_stock * 2 - $product->stock;

                    PoItem::create([
                        'purchase_order_id' => $po->id,
                        'product_id' => $product->id,
                        'quantity' => max(1, $qty),
                        'price' => $product->cost_price ?: $product->price,
                        'subtotal' => max(1, $qty) * ($product->cost_price ?: $product->price),
                    ]);
                }

                $this->info("Auto draft PO created: {$poNumber} for {$store->name}");
            });
        }

        $this->info('Auto draft PO check completed.');
    }
}
