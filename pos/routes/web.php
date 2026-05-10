<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::post('midtrans/webhook', [App\Http\Controllers\MidtransWebhookController::class, 'notification'])
    ->name('midtrans.webhook');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('dashboard');

    Route::get('/cashier', function () {
        return view('cashier.index');
    })->name('cashier');

    Route::resource('products', App\Http\Controllers\ProductController::class)
        ->middleware('role:owner');
    Route::resource('categories', App\Http\Controllers\CategoryController::class)
        ->middleware('role:owner');
    Route::resource('vendors', App\Http\Controllers\VendorController::class)
        ->middleware('role:owner');
    Route::resource('users', App\Http\Controllers\UserController::class)
        ->middleware('role:owner');
    Route::resource('purchase-orders', App\Http\Controllers\PurchaseOrderController::class)
        ->middleware('role:owner');
    Route::resource('stock-opname', App\Http\Controllers\StockOpnameController::class)
        ->middleware('role:owner');

    Route::get('/stock', [App\Http\Controllers\StockController::class, 'index'])
        ->name('stock.index')->middleware('role:owner');
    Route::get('/stock-movement/in', [App\Http\Controllers\StockMovementController::class, 'in'])
        ->name('stock-movement.in')->middleware('role:owner');
    Route::get('/stock-movement/out', [App\Http\Controllers\StockMovementController::class, 'out'])
        ->name('stock-movement.out')->middleware('role:owner');

    Route::get('/reports/sales', [App\Http\Controllers\ReportController::class, 'sales'])
        ->name('reports.sales')->middleware('role:owner');
    Route::get('/reports/tax', [App\Http\Controllers\ReportController::class, 'tax'])
        ->name('reports.tax')->middleware('role:owner');

    Route::get('/forecast/sales', [App\Http\Controllers\ForecastController::class, 'sales'])
        ->name('forecast.sales')->middleware('role:owner');
    Route::get('/forecast/stock', [App\Http\Controllers\ForecastController::class, 'stock'])
        ->name('forecast.stock')->middleware('role:owner');

    Route::get('/approvals/receipt', [App\Http\Controllers\ApprovalController::class, 'receipt'])
        ->name('approvals.receipt')->middleware('role:owner');
    Route::get('/approvals/refund', [App\Http\Controllers\ApprovalController::class, 'refund'])
        ->name('approvals.refund')->middleware('role:owner');

    Route::get('/settings/store', [App\Http\Controllers\SettingController::class, 'store'])
        ->name('settings.store')->middleware('role:owner');
    Route::get('/activity-logs', [App\Http\Controllers\ActivityLogController::class, 'index'])
        ->name('activity-logs.index')->middleware('role:owner');

    Route::get('/transactions', [App\Http\Controllers\TransactionController::class, 'index'])
        ->name('transactions.index')->middleware('role:owner,cashier');
    Route::get('/transactions/{transaction}', [App\Http\Controllers\TransactionController::class, 'show'])
        ->name('transactions.show')->middleware('role:owner,cashier');

    Route::get('/subscriptions/check', [App\Http\Controllers\SubscriptionController::class, 'check'])
        ->name('subscriptions.check')->middleware('role:owner,cashier');

    Route::get('/requests/receipt', [App\Http\Controllers\RequestController::class, 'receipt'])
        ->name('requests.receipt')->middleware('role:cashier');
    Route::get('/requests/refund', [App\Http\Controllers\RequestController::class, 'refund'])
        ->name('requests.refund')->middleware('role:cashier');

    Route::get('/print/receipt/{transaction}', [App\Http\Controllers\PrintController::class, 'receipt'])
        ->name('print.receipt')->middleware('role:owner,cashier');
    Route::get('/print/direct/{transaction}', [App\Http\Controllers\PrintController::class, 'directPrint'])
        ->name('print.direct')->middleware('role:owner');

    Route::get('/reports/sales/export-excel', [App\Http\Controllers\ReportController::class, 'exportSalesExcel'])
        ->name('reports.sales.export-excel')->middleware('role:owner');
    Route::get('/reports/sales/export-pdf', [App\Http\Controllers\ReportController::class, 'exportSalesPdf'])
        ->name('reports.sales.export-pdf')->middleware('role:owner');
    Route::get('/stock/export-excel', [App\Http\Controllers\ReportController::class, 'exportStockExcel'])
        ->name('stock.export-excel')->middleware('role:owner');
    Route::get('/stock/export-pdf', [App\Http\Controllers\ReportController::class, 'exportStockPdf'])
        ->name('stock.export-pdf')->middleware('role:owner');
});
