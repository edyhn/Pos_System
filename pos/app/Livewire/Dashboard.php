<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\ReceiptReprintRequest;
use App\Models\RefundRequest;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public $storeId;
    public $todaySales = 0;
    public $todayTransactions = 0;
    public $totalProducts = 0;
    public $pendingApprovals = 0;
    public $draftPos = [];
    public $pendingRequests = 0;
    public $lowStockProducts = [];
    public array $weeklyChartData = [];
    public array $monthlyChartData = [];
    public array $topProductsChart = [];
    public array $categorySalesChart = [];

    public function mount()
    {
        $this->storeId = auth()->user()->store_id;
    }

    public function loadData()
    {
        $user = auth()->user();
        $storeId = $this->storeId;

        if ($user->isOwner()) {
            $todayData = cache()->remember("dashboard.today.{$storeId}", 60, function () use ($storeId) {
                return Transaction::byStore($storeId)
                    ->today()
                    ->where('status', 'completed')
                    ->selectRaw('COALESCE(SUM(total_amount), 0) as sales, COUNT(*) as count')
                    ->first();
            });

            $pendingReprint = cache()->remember("dashboard.pending.reprint.{$storeId}", 60, function () use ($storeId) {
                return ReceiptReprintRequest::where('store_id', $storeId)
                    ->where('status', 'pending')->count();
            });
            $pendingRefund = cache()->remember("dashboard.pending.refund.{$storeId}", 60, function () use ($storeId) {
                return RefundRequest::where('store_id', $storeId)
                    ->where('status', 'pending')->count();
            });

            $this->todaySales = $todayData->sales;
            $this->todayTransactions = $todayData->count;
            $this->totalProducts = cache()->remember("dashboard.products.count.{$storeId}", 300, function () use ($storeId) {
                return Product::byStore($storeId)->where('is_active', true)->count();
            });
            $this->pendingApprovals = $pendingReprint + $pendingRefund;
            $this->draftPos = cache()->remember("dashboard.draftPos.{$storeId}", 60, function () use ($storeId) {
                return PurchaseOrder::where('store_id', $storeId)
                    ->where('status', 'draft')->with('vendor')->withCount('items')
                    ->orderBy('created_at', 'desc')->take(5)->get();
            });
            $this->lowStockProducts = cache()->remember("dashboard.lowStock.{$storeId}", 300, function () use ($storeId) {
                return Product::byStore($storeId)
                    ->where('is_active', true)->whereColumn('stock', '<=', 'min_stock')
                    ->where('min_stock', '>', 0)->orderBy('stock', 'asc')->take(5)->get();
            });
            $this->weeklyChartData = $this->getWeeklySales();
            $this->monthlyChartData = $this->getMonthlySales();
            $this->topProductsChart = $this->getTopProducts();
            $this->categorySalesChart = $this->getCategorySales();
        } else {
            $pendingReprint = cache()->remember("dashboard.cashier.pending.reprint.{$storeId}.{$user->id}", 60, function () use ($storeId, $user) {
                return ReceiptReprintRequest::where('store_id', $storeId)
                    ->where('user_id', $user->id)->where('status', 'pending')->count();
            });
            $pendingRefund = cache()->remember("dashboard.cashier.pending.refund.{$storeId}.{$user->id}", 60, function () use ($storeId, $user) {
                return RefundRequest::where('store_id', $storeId)
                    ->where('user_id', $user->id)->where('status', 'pending')->count();
            });

            $this->todaySales = cache()->remember("dashboard.cashier.sales.{$storeId}.{$user->id}", 60, function () use ($storeId, $user) {
                return Transaction::byStore($storeId)
                    ->today()->where('status', 'completed')
                    ->where('user_id', $user->id)->sum('total_amount');
            });
            $this->todayTransactions = cache()->remember("dashboard.cashier.count.{$storeId}.{$user->id}", 60, function () use ($storeId, $user) {
                return Transaction::byStore($storeId)
                    ->today()->where('status', 'completed')
                    ->where('user_id', $user->id)->count();
            });
            $this->pendingRequests = $pendingReprint + $pendingRefund;
            $this->weeklyChartData = $this->getCashierWeeklySales();
        }
    }

    protected function getCashierWeeklySales(): array
    {
        $user = auth()->user();
        $storeId = $this->storeId;

        $rows = Transaction::byStore($storeId)
            ->where('status', 'completed')
            ->where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $labels = [];
        $values = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('D');
            $values[] = (int) ($rows[$date] ?? 0);
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    protected function getWeeklySales(): array
    {
        $storeId = $this->storeId;
        $cacheKey = "dashboard.weekly.{$storeId}";

        $rows = cache()->remember($cacheKey, 300, function () use ($storeId) {
            return Transaction::byStore($storeId)
                ->where('status', 'completed')
                ->where('created_at', '>=', now()->subDays(6)->startOfDay())
                ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('total', 'date')
                ->toArray();
        });

        $labels = [];
        $values = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('D');
            $values[] = (int) ($rows[$date] ?? 0);
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    protected function getMonthlySales(): array
    {
        $storeId = $this->storeId;
        $cacheKey = "dashboard.monthly.{$storeId}";

        $rows = cache()->remember($cacheKey, 3600, function () use ($storeId) {
            $driver = DB::connection()->getDriverName();
            $monthExpr = $driver === 'sqlite'
                ? "CAST(strftime('%m', created_at) AS INTEGER)"
                : 'MONTH(created_at)';
            return Transaction::byStore($storeId)
                ->where('status', 'completed')
                ->whereYear('created_at', now()->year)
                ->selectRaw("{$monthExpr} as month, SUM(total_amount) as total")
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('total', 'month')
                ->toArray();
        });

        $labels = [];
        $values = [];
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        for ($m = 1; $m <= 12; $m++) {
            $labels[] = $months[$m - 1];
            $values[] = (int) ($rows[$m] ?? 0);
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    protected function getTopProducts(): array
    {
        $storeId = $this->storeId;

        $rows = TransactionItem::whereHas('transaction', function ($q) use ($storeId) {
            $q->byStore($storeId)->where('status', 'completed')
              ->where('created_at', '>=', now()->subDays(30)->startOfDay());
        })
            ->selectRaw('product_name, SUM(quantity) as total_qty')
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        return [
            'labels' => $rows->pluck('product_name')->map(fn($n) => \Illuminate\Support\Str::limit($n, 20))->toArray(),
            'values' => $rows->pluck('total_qty')->toArray(),
        ];
    }

    protected function getCategorySales(): array
    {
        $storeId = $this->storeId;

        $rows = TransactionItem::whereHas('transaction', function ($q) use ($storeId) {
            $q->byStore($storeId)->where('status', 'completed')
              ->where('created_at', '>=', now()->subDays(30)->startOfDay());
        })
            ->join('products', 'products.id', '=', 'transaction_items.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->selectRaw('categories.name as category_name, SUM(transaction_items.subtotal) as total')
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->get();

        return [
            'labels' => $rows->pluck('category_name')->toArray(),
            'values' => $rows->pluck('total')->map(fn($v) => (int) $v)->toArray(),
        ];
    }

    public function render()
    {
        $this->loadData();
        return view('livewire.dashboard');
    }
}
