<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Subscription;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function notification(Request $request)
    {
        try {
            $payload = $request->all();

            Log::info('Midtrans webhook received', ['payload' => $payload]);

            $orderId = $payload['order_id'] ?? null;
            $transactionStatus = $payload['transaction_status'] ?? null;
            $fraudStatus = $payload['fraud_status'] ?? null;
            $statusCode = $payload['status_code'] ?? null;
            $grossAmount = $payload['gross_amount'] ?? null;
            $signatureKey = $payload['signature_key'] ?? null;

            if (!$orderId || !$transactionStatus) {
                return response()->json(['status' => 'error', 'message' => 'Invalid payload'], 400);
            }

            $midtrans = app(MidtransService::class);

            if (!$midtrans->verifySignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
                Log::warning('Midtrans webhook: invalid signature', ['order_id' => $orderId]);
                return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 403);
            }

            $transaction = Transaction::where('invoice_number', $orderId)->first();

            if (!$transaction) {
                Log::warning('Midtrans webhook: transaction not found', ['order_id' => $orderId]);
                return response()->json(['status' => 'error', 'message' => 'Transaction not found'], 404);
            }

            if ($transaction->payment_method !== 'midtrans' || $transaction->status === 'completed') {
                return response()->json(['status' => 'ok', 'message' => 'No action needed']);
            }

            if ($transactionStatus === 'settlement' || $transactionStatus === 'capture') {
                if ($fraudStatus === 'accept' || $fraudStatus === null) {
                    $lock = Cache::lock('midtrans-' . $orderId, 10);
                    if (!$lock->get()) {
                        return response()->json(['status' => 'ok', 'message' => 'Already processing']);
                    }
                    try {
                        DB::transaction(function () use ($transaction) {
                            $transaction->update([
                                'status' => 'completed',
                                'payment_amount' => $transaction->total_amount,
                                'change_amount' => 0,
                            ]);

                            if (!$transaction->items()->exists()) {
                                Log::warning('Midtrans webhook: no items for transaction', ['order_id' => $orderId]);
                                return;
                            }

                            foreach ($transaction->items as $item) {
                                $product = Product::find($item->product_id);
                                if ($product) {
                                    $product->decrement('stock', $item->quantity);

                                    StockMovement::create([
                                        'store_id' => $transaction->store_id,
                                        'product_id' => $item->product_id,
                                        'user_id' => $transaction->user_id,
                                        'reference_type' => 'transaction',
                                        'reference_id' => $transaction->id,
                                        'type' => 'out',
                                        'quantity' => $item->quantity,
                                        'note' => 'Penjualan Midtrans #' . $transaction->invoice_number,
                                    ]);
                                }

                                if ($product && $product->is_subscription) {
                                    Subscription::create([
                                        'store_id' => $transaction->store_id,
                                        'transaction_id' => $transaction->id,
                                        'product_id' => $item->product_id,
                                        'customer_identifier' => $transaction->customer_name ?? 'Guest',
                                        'start_date' => now(),
                                        'end_date' => now()->addDays($product->subscription_days),
                                        'status' => 'active',
                                    ]);
                                }
                            }
                        });

                        Log::info('Midtrans payment completed', ['order_id' => $orderId]);
                    } finally {
                        $lock->release();
                    }
                }
            } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
                $transaction->update(['status' => 'cancelled']);
                Log::info('Midtrans payment failed', ['order_id' => $orderId, 'status' => $transactionStatus]);
            }

            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            Log::error('Midtrans webhook error', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
