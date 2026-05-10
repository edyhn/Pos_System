<?php

namespace App\Http\Controllers;

use App\Exports\SalesExport;
use App\Exports\StockExport;
use App\Models\Product;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function sales()
    {
        return view('reports.sales');
    }

    public function tax()
    {
        return view('reports.tax');
    }

    public function exportSalesExcel(Request $request)
    {
        $storeId = $request->store_id ?? auth()->user()->store_id;

        return Excel::download(
            new SalesExport(
                $storeId,
                $request->date_from,
                $request->date_to
            ),
            'laporan-penjualan.xlsx'
        );
    }

    public function exportSalesPdf(Request $request)
    {
        $storeId = $request->store_id ?? auth()->user()->store_id;
        $query = Transaction::with('items', 'user')
            ->where('status', 'completed')
            ->where('store_id', $storeId);

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->latest()->get();

        $pdf = Pdf::loadView('reports.sales-pdf', [
            'transactions' => $transactions,
            'dateFrom' => $request->date_from,
            'dateTo' => $request->date_to,
        ]);

        return $pdf->download('laporan-penjualan.pdf');
    }

    public function exportStockExcel()
    {
        $storeId = auth()->user()->store_id;

        return Excel::download(
            new StockExport($storeId),
            'laporan-stok.xlsx'
        );
    }

    public function exportStockPdf()
    {
        $storeId = auth()->user()->store_id;
        $products = Product::where('store_id', $storeId)
            ->where('is_active', true)
            ->with('category')
            ->get();

        $pdf = Pdf::loadView('reports.stock-pdf', [
            'products' => $products,
        ]);

        return $pdf->download('laporan-stok.pdf');
    }
}
