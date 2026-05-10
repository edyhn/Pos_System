<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\ForecastService;
use Livewire\Component;
use Livewire\WithPagination;

class ForecastStock extends Component
{
    use WithPagination;

    public $storeId;
    public $search = '';
    public $categoryFilter = '';
    public $leadTimeDays = 7;
    public $safetyStock = 10;
    public $sortBy = 'status';

    public function mount()
    {
        $this->storeId = auth()->user()->store_id;
    }

    public function updatingSearch() { $this->resetPage(); }

    public function getCategoriesProperty()
    {
        return \App\Models\Category::where('store_id', $this->storeId)->where('is_active', true)->orderBy('name')->get();
    }

    public function render()
    {
        $service = new ForecastService();

        $query = Product::where('store_id', $this->storeId)->where('is_active', true);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('sku', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->categoryFilter) {
            $query->where('category_id', $this->categoryFilter);
        }

        $products = $query->with('category')->orderBy('name')->paginate(20);

        $forecasts = collect();
        foreach ($products as $product) {
            $avgDailySales = $service->getProductDailySalesAvg($product, 30);
            $runout = $service->stockRunoutPrediction($product, $avgDailySales);
            $reorder = $service->reorderRecommendation($product, $avgDailySales, $this->leadTimeDays, $this->safetyStock);

            $forecasts->push((object) [
                'product' => $product,
                'avg_daily_sales' => $avgDailySales,
                'stock' => $product->stock,
                'min_stock' => $product->min_stock,
                'estimated_days' => $runout['estimated_days'],
                'estimated_date' => $runout['estimated_date'],
                'status' => $runout['status'],
                'recommended_qty' => $reorder['recommended_qty'],
                'needs_reorder' => $reorder['recommended_qty'] > 0,
            ]);
        }

        $sortBy = $this->sortBy;
        if ($sortBy === 'status') {
            $order = ['habis' => 0, 'kritis' => 1, 'menipis' => 2, 'aman' => 3, 'no_data' => 4];
            $forecasts = $forecasts->sortBy(fn($f) => $order[$f->status] ?? 99);
        } elseif ($sortBy === 'estimated_days') {
            $forecasts = $forecasts->sortBy(fn($f) => $f->estimated_days ?? 9999);
        } elseif ($sortBy === 'recommended_qty') {
            $forecasts = $forecasts->sortByDesc('recommended_qty');
        }

        $summary = [
            'total' => $forecasts->count(),
            'kritis' => $forecasts->where('status', 'kritis')->count(),
            'menipis' => $forecasts->where('status', 'menipis')->count(),
            'habis' => $forecasts->where('status', 'habis')->count(),
            'aman' => $forecasts->where('status', 'aman')->count(),
            'needs_reorder' => $forecasts->where('needs_reorder', true)->count(),
            'total_reorder_qty' => $forecasts->where('needs_reorder', true)->sum('recommended_qty'),
        ];

        return view('livewire.forecast-stock', compact('forecasts', 'summary', 'products'));
    }
}
