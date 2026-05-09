<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\PrintService;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    public function receipt(Transaction $transaction)
    {
        $transaction->load('items', 'store', 'user');
        return view('print.receipt', compact('transaction'));
    }

    public function directPrint(Transaction $transaction)
    {
        $transaction->load('items', 'store', 'user');

        if (!$transaction->store) {
            abort(404, 'Store not found');
        }

        $printService = new PrintService();

        try {
            $printService->printReceipt($transaction);
            return back()->with('message', 'Struk berhasil dicetak.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mencetak: ' . $e->getMessage());
        }
    }
}
