<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\ReceiptReprintRequest;
use App\Models\RefundRequest;
use App\Models\Transaction;
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

    public function mount()
    {
        $this->storeId = auth()->user()->store_id;
    }

    public function loadData()
    {
        $user = auth()->user();
        $storeId = $this->storeId;

        if ($user->isOwner()) {
            $cacheKey = "dashboard.owner.{$storeId}";
            $data = cache()->remember($cacheKey, 60, function () use ($storeId) {
                $todayData = Transaction::byStore($storeId)
                    ->today()
                    ->where('status', 'completed')
                    ->selectRaw('COALESCE(SUM(total_amount), 0) as sales, COUNT(*) as count')
                    ->first();

                $pendingReprint = ReceiptReprintRequest::where('store_id', $storeId)
                    ->where('status', 'pending')
                    ->count();

                $pendingRefund = RefundRequest::where('store_id', $storeId)
                    ->where('status', 'pending')
                    ->count();

                return [
                    'todaySales' => $todayData->sales,
                    'todayTransactions' => $todayData->count,
                    'totalProducts' => Product::byStore($storeId)->where('is_active', true)->count(),
                    'pendingApprovals' => $pendingReprint + $pendingRefund,
                    'draftPos' => PurchaseOrder::where('store_id', $storeId)
                        ->where('status', 'draft')
                        ->with('vendor')
                        ->withCount('items')
                        ->orderBy('created_at', 'desc')
                        ->take(5)
                        ->get(),
                    'lowStockProducts' => Product::byStore($storeId)
                        ->where('is_active', true)
                        ->whereColumn('stock', '<=', 'min_stock')
                        ->where('min_stock', '>', 0)
                        ->orderBy('stock', 'asc')
                        ->take(5)
                        ->get(),
                ];
            });

            $this->todaySales = $data['todaySales'];
            $this->todayTransactions = $data['todayTransactions'];
            $this->totalProducts = $data['totalProducts'];
            $this->pendingApprovals = $data['pendingApprovals'];
            $this->draftPos = $data['draftPos'];
            $this->lowStockProducts = $data['lowStockProducts'];
            $this->weeklyChartData = $this->getWeeklySales();
            $this->monthlyChartData = $this->getMonthlySales();
        } else {
            $cacheKey = "dashboard.cashier.{$storeId}.{$user->id}";
            $data = cache()->remember($cacheKey, 30, function () use ($storeId, $user) {
                $pendingReprint = ReceiptReprintRequest::where('store_id', $storeId)
                    ->where('user_id', $user->id)
                    ->where('status', 'pending')
                    ->count();

                $pendingRefund = RefundRequest::where('store_id', $storeId)
                    ->where('user_id', $user->id)
                    ->where('status', 'pending')
                    ->count();

                return [
                    'todaySales' => Transaction::byStore($storeId)
                        ->today()->where('status', 'completed')
                        ->where('user_id', $user->id)->sum('total_amount'),
                    'todayTransactions' => Transaction::byStore($storeId)
                        ->today()->where('status', 'completed')
                        ->where('user_id', $user->id)->count(),
                    'pendingRequests' => $pendingReprint + $pendingRefund,
                ];
            });

            $this->todaySales = $data['todaySales'];
            $this->todayTransactions = $data['todayTransactions'];
            $this->pendingRequests = $data['pendingRequests'];
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
            ->whereDate('created_at', '>=', now()->subDays(6))
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
                ->whereDate('created_at', '>=', now()->subDays(6))
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

    public function render()
    {
        $this->loadData();
        return view('livewire.dashboard');
    }
}
