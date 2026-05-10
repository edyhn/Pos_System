<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\PrintService;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    public function receipt(Transaction $transaction)
    {
        abort_if($transaction->store_id !== auth()->user()->store_id, 403, 'Akses ditolak');
        $transaction->load('items', 'store', 'user');
        return view('print.receipt', compact('transaction'));
    }

    public function directPrint(Transaction $transaction)
    {
        abort_if($transaction->store_id !== auth()->user()->store_id, 403, 'Akses ditolak');
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
