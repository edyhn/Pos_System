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
        $transaction->load('items', 'store', 'user', 'subscriptions.product');
        return view('transactions.show', compact('transaction'));
    }
}
