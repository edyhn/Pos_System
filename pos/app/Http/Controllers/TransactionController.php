<?php

namespace App\Http\Controllers;

use App\Models\Transaction;

class TransactionController extends Controller
{
    public function index()
    {
        return view('transactions.index');
    }

    public function show(Transaction $transaction)
    {
        $user = auth()->user();
        if (!$user->isOwner()) {
            abort_if($transaction->store_id !== $user->store_id, 403, 'Akses ditolak');
        }
        $transaction->load('items', 'store', 'user', 'subscriptions.product');
        return view('transactions.show', compact('transaction'));
    }
}
