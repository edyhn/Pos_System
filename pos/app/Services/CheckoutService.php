<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutService
{
    public function __construct(
        private StockService $stockService,
        private ActivityLogger $activityLogger,
    ) {}

    public function validateCart(array $cart, int $storeId): Collection
    {
        if (empty($cart)) {
            throw new \RuntimeException('Keranjang masih kosong.');
        }

        $productIds = array_column($cart, 'product_id');
        $products = Product::whereIn('id', $productIds)
            ->where('store_id', $storeId)
            ->get()
            ->keyBy('id');

        foreach ($cart as $item) {
            $product = $products->get($item['product_id']);
            if (!$product) {
                throw new \RuntimeException('Produk dengan ID ' . $item['product_id'] . ' tidak ditemukan.');
            }
            $this->stockService->validateStockAvailability($product, $item['quantity']);
        }

        return $products;
    }

    public function generateInvoiceNumber(int $storeId): string
    {
        $lock = Cache::lock('invoice-number-' . date('Ymd'), 10);
        $lock->block(5);

        try {
            $todayCount = Transaction::whereDate('created_at', today())
                ->where('store_id', $storeId)
                ->count();

            return 'INV-' . date('Ymd') . '-' . str_pad($todayCount + 1, 4, '0', STR_PAD_LEFT);
        } finally {
            $lock->release();
        }
    }

    public function calculateSubtotal(array $cart): float
    {
        return array_sum(array_column($cart, 'subtotal'));
    }

    public function calculateTax(array $cart, bool $taxEnabled): float
    {
        if (!$taxEnabled) {
            return 0;
        }

        $tax = 0;
        foreach ($cart as $item) {
            if ($item['is_taxed']) {
                $tax += $item['subtotal'] * ($item['tax_rate'] / 100);
            }
        }
        return $tax;
    }

    public function calculateTotal(array $cart, bool $taxEnabled): float
    {
        return $this->calculateSubtotal($cart) + $this->calculateTax($cart, $taxEnabled);
    }

    public function processCheckout(
        array $cart,
        string $customerName,
        string $paymentMethod,
        float $paymentAmount,
        bool $taxEnabled,
        int $storeId,
        int $userId,
    ): Transaction {
        $products = $this->validateCart($cart, $storeId);
        $invoiceNumber = $this->generateInvoiceNumber($storeId);
        $subtotal = $this->calculateSubtotal($cart);
        $taxAmount = $this->calculateTax($cart, $taxEnabled);
        $total = $subtotal + $taxAmount;

        if ($paymentMethod === 'cash' && $paymentAmount < $total) {
            throw new \RuntimeException('Pembayaran kurang dari total.');
        }

        $changeAmount = $paymentMethod === 'cash' ? max(0, $paymentAmount - $total) : 0;

        return DB::transaction(function () use (
            $cart, $products, $customerName, $paymentMethod,
            $paymentAmount, $changeAmount, $invoiceNumber,
            $subtotal, $taxAmount, $total, $storeId, $userId
        ) {
            $transaction = Transaction::create([
                'store_id' => $storeId,
                'user_id' => $userId,
                'invoice_number' => $invoiceNumber,
                'customer_name' => $customerName,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $total,
                'payment_amount' => $paymentAmount,
                'change_amount' => $changeAmount,
                'payment_method' => $paymentMethod,
                'status' => 'completed',
            ]);

            foreach ($cart as $item) {
                $transaction->items()->create([
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                    'is_taxed' => $item['is_taxed'],
                ]);

                $product = $products->get($item['product_id']);

                $this->stockService->decrementStock(
                    $product,
                    $item['quantity'],
                    'transaction',
                    $transaction->id,
                    $storeId,
                    $userId,
                    'Penjualan #' . $invoiceNumber,
                );

                if ($product->is_subscription) {
                    Subscription::create([
                        'store_id' => $storeId,
                        'transaction_id' => $transaction->id,
                        'product_id' => $item['product_id'],
                        'customer_identifier' => $customerName ?: 'Guest',
                        'start_date' => now(),
                        'end_date' => now()->addDays($product->subscription_days),
                        'status' => 'active',
                    ]);
                }
            }

            $this->activityLogger->log(
                'create',
                'Transaksi penjualan: ' . $invoiceNumber . ' - Rp ' . number_format($total, 0, ',', '.'),
                $storeId,
                $userId,
            );

            Cache::forget("dashboard.owner.{$storeId}");
            Cache::forget("dashboard.cashier.{$storeId}.{$userId}");

            return $transaction;
        });
    }

    public function processMidtransCheckout(
        array $cart,
        string $customerName,
        string $orderId,
        bool $taxEnabled,
        int $storeId,
        int $userId,
    ): Transaction {
        $products = $this->validateCart($cart, $storeId);
        $subtotal = $this->calculateSubtotal($cart);
        $taxAmount = $this->calculateTax($cart, $taxEnabled);
        $total = $subtotal + $taxAmount;

        $lock = Cache::lock('midtrans-' . $orderId, 10);

        try {
            return $lock->get(function () use (
                $cart, $products, $customerName, $orderId,
                $subtotal, $taxAmount, $total, $storeId, $userId
            ) {
                $existing = Transaction::where('invoice_number', $orderId)->first();
                if ($existing && $existing->status === 'completed') {
                    Log::info('Midtrans transaction already completed', ['order_id' => $orderId]);
                    return $existing;
                }

                return DB::transaction(function () use (
                    $cart, $products, $customerName, $orderId,
                    $subtotal, $taxAmount, $total, $storeId, $userId
                ) {
                    $transaction = Transaction::updateOrCreate(
                        ['invoice_number' => $orderId],
                        [
                            'store_id' => $storeId,
                            'user_id' => $userId,
                            'customer_name' => $customerName,
                            'subtotal' => $subtotal,
                            'tax_amount' => $taxAmount,
                            'total_amount' => $total,
                            'payment_amount' => $total,
                            'change_amount' => 0,
                            'payment_method' => 'midtrans',
                            'status' => 'completed',
                        ]
                    );

                    if (!$transaction->items()->exists()) {
                        foreach ($cart as $item) {
                            $transaction->items()->create([
                                'product_id' => $item['product_id'],
                                'product_name' => $item['name'],
                                'quantity' => $item['quantity'],
                                'price' => $item['price'],
                                'subtotal' => $item['subtotal'],
                                'is_taxed' => $item['is_taxed'],
                            ]);

                            $product = $products->get($item['product_id']);

                            $this->stockService->decrementStock(
                                $product,
                                $item['quantity'],
                                'transaction',
                                $transaction->id,
                                $storeId,
                                $userId,
                                'Penjualan Midtrans #' . $orderId,
                            );

                            if ($product->is_subscription) {
                                Subscription::create([
                                    'store_id' => $storeId,
                                    'transaction_id' => $transaction->id,
                                    'product_id' => $item['product_id'],
                                    'customer_identifier' => $customerName ?: 'Guest',
                                    'start_date' => now(),
                                    'end_date' => now()->addDays($product->subscription_days),
                                    'status' => 'active',
                                ]);
                            }
                        }
                    }

                    $this->activityLogger->log(
                        'create',
                        'Transaksi penjualan Midtrans: ' . $orderId . ' - Rp ' . number_format($total, 0, ',', '.'),
                        $storeId,
                        $userId,
                    );

                    Cache::forget("dashboard.owner.{$storeId}");
                    Cache::forget("dashboard.cashier.{$storeId}.{$userId}");

                    return $transaction;
                });
            });
        } finally {
            $lock->release();
        }
    }
}
