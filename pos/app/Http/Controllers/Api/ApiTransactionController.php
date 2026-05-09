<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiTransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $storeId = $request->user()->store_id;

        $transactions = Transaction::where('store_id', $storeId)
            ->with('user:id,name', 'items.product:id,name,sku')
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($request->payment_method, fn($q) => $q->where('payment_method', $request->payment_method))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json($transactions);
    }

    public function show(Request $request, Transaction $transaction): JsonResponse
    {
        if ($transaction->store_id !== $request->user()->store_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json($transaction->load('user', 'items.product', 'subscriptions'));
    }

    public function today(Request $request): JsonResponse
    {
        $storeId = $request->user()->store_id;

        $transactions = Transaction::where('store_id', $storeId)
            ->whereDate('created_at', today())
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        $summary = [
            'total_transactions' => $transactions->count(),
            'total_revenue' => $transactions->where('status', 'completed')->sum('total_amount'),
            'total_tax' => $transactions->where('status', 'completed')->sum('tax_amount'),
        ];

        return response()->json([
            'transactions' => $transactions,
            'summary' => $summary,
        ]);
    }
}
