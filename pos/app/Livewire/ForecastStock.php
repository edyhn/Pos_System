<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PoItem;
use App\Models\User;
use App\Services\ForecastService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ForecastStock extends Component
{
    use WithPagination;

    public $storeId;
    public $search = '';
    public $categoryFilter = '';
    public $leadTimeDays = 7;
    public $safetyStock = 10;
    public $sortBy = 'status';
    public $showPreview = false;
    public $previewVendorGroups = [];
    public $previewCount = 0;
    public $previewTotal = 0;
    public $previewTotalPrice = 0;

    public function mount()
    {
        $this->storeId = auth()->user()->store_id;
    }

    public function updatingSearch() { $this->resetPage(); }

    public function getCategoriesProperty()
    {
        return \App\Models\Category::where('store_id', $this->storeId)->where('is_active', true)->orderBy('name')->get();
    }

    public function getProductIdsWithAutoPo(): array
    {
        return PoItem::whereHas('purchaseOrder', function ($q) {
            $q->where('store_id', $this->storeId)
              ->where('is_auto_draft', true)
              ->where('status', 'draft');
        })->pluck('product_id')->toArray();
    }

    public function previewPO()
    {
        $service = new ForecastService();
        $excludeIds = $this->getProductIdsWithAutoPo();
        $products = Product::with('vendor')
            ->where('store_id', $this->storeId)
            ->where('is_active', true)
            ->whereNotIn('id', $excludeIds)
            ->get();

        $groups = [];
        $count = 0;
        $totalQty = 0;
        $totalPrice = 0;

        foreach ($products as $product) {
            $avgDailySales = $service->getProductDailySalesAvg($product, 30);
            $reorder = $service->reorderRecommendation($product, $avgDailySales, $this->leadTimeDays, $this->safetyStock);
            if ($reorder['recommended_qty'] <= 0) continue;

            $vendorId = $product->vendor_id;
            $vendorName = $product->vendor?->name ?? 'Tanpa Vendor';
            $key = $vendorId ?? 'no_vendor';

            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'vendor_id' => $vendorId,
                    'vendor_name' => $vendorName,
                    'items' => [],
                    'count' => 0,
                    'total_qty' => 0,
                    'total_price' => 0,
                ];
            }

            $item = [
                'product_id' => $product->id,
                'vendor_id' => $vendorId,
                'vendor_name' => $vendorName,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'stock' => $product->stock,
                'price' => $product->cost_price ?: $product->price,
                'qty' => $reorder['recommended_qty'],
                'subtotal' => $reorder['recommended_qty'] * ($product->cost_price ?: $product->price),
            ];

            $groups[$key]['items'][] = $item;
            $groups[$key]['count']++;
            $groups[$key]['total_qty'] += $item['qty'];
            $groups[$key]['total_price'] += $item['subtotal'];

            $count++;
            $totalQty += $item['qty'];
            $totalPrice += $item['subtotal'];
        }

        if ($count === 0) {
            session()->flash('message', 'Tidak ada produk yang perlu reorder.');
            return;
        }

        $this->previewVendorGroups = array_values($groups);
        $this->previewCount = $count;
        $this->previewTotal = $totalQty;
        $this->previewTotalPrice = $totalPrice;
        $this->showPreview = true;
    }

    public function closePreview()
    {
        $this->showPreview = false;
        $this->previewVendorGroups = [];
        $this->previewCount = 0;
        $this->previewTotal = 0;
        $this->previewTotalPrice = 0;
    }

    public function confirmPO()
    {
        if (empty($this->previewVendorGroups)) {
            session()->flash('message', 'Tidak ada item untuk dibuat draft PO.');
            $this->closePreview();
            return;
        }

        $owner = User::where('store_id', $this->storeId)->where('role', 'owner')->where('is_active', true)->first();
        $userId = $owner?->id ?? auth()->id();
        $poNumbers = [];

        DB::transaction(function () use ($userId, &$poNumbers) {
            foreach ($this->previewVendorGroups as $group) {
                $lock = Cache::lock('po-auto-number-' . date('Ymd'), 10);
                $lock->block(5);
                $poNumber = 'PO-FCST-' . date('Ymd') . '-' . str_pad(PurchaseOrder::whereDate('created_at', today())->where('store_id', $this->storeId)->count() + 1, 4, '0', STR_PAD_LEFT);
                $lock->release();

                $po = PurchaseOrder::create([
                    'store_id' => $this->storeId,
                    'vendor_id' => $group['vendor_id'],
                    'user_id' => $userId,
                    'po_number' => $poNumber,
                    'status' => 'draft',
                    'is_auto_draft' => true,
                    'notes' => 'Auto draft dari forecast stok - ' . $group['vendor_name'],
                ]);

                foreach ($group['items'] as $item) {
                    PoItem::create([
                        'purchase_order_id' => $po->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['qty'],
                        'price' => $item['price'],
                        'subtotal' => $item['subtotal'],
                    ]);
                }

                $poNumbers[] = $poNumber;
                \App\Services\ActivityLogger::log('create', 'Draft PO dari forecast: ' . $poNumber . ' (' . $group['vendor_name'] . ')');
            }
        });

        $this->closePreview();
        session()->flash('message', count($poNumbers) . ' Draft PO berhasil dibuat: ' . implode(', ', $poNumbers));
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

        $productIds = $products->pluck('id')->toArray();
        $productIdsWithAutoPo = $this->getProductIdsWithAutoPo();
        $avgSalesBatch = !empty($productIds)
            ? $service->getBatchDailySalesAvg($this->storeId, $productIds, 30)
            : [];

        $forecasts = collect();
        foreach ($products as $product) {
            $avgDailySales = $avgSalesBatch[$product->id] ?? 0;
            $runout = $service->stockRunoutPrediction($product, $avgDailySales);
            $reorder = $service->reorderRecommendation($product, $avgDailySales, $this->leadTimeDays, $this->safetyStock);

            $hasAutoPo = in_array($product->id, $productIdsWithAutoPo);
            $forecasts->push((object) [
                'product' => $product,
                'avg_daily_sales' => $avgDailySales,
                'stock' => $product->stock,
                'min_stock' => $product->min_stock,
                'estimated_days' => $runout['estimated_days'],
                'estimated_date' => $runout['estimated_date'],
                'status' => $runout['status'],
                'recommended_qty' => $reorder['recommended_qty'],
                'needs_reorder' => $reorder['recommended_qty'] > 0 && !$hasAutoPo,
                'has_auto_po' => $hasAutoPo,
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

        $summary = $this->computeFullSummary($service);

        return view('livewire.forecast-stock', compact('forecasts', 'summary', 'products'));
    }

    private function computeFullSummary(ForecastService $service): array
    {
        $allProducts = Product::where('store_id', $this->storeId)->where('is_active', true)->get();
        $allIds = $allProducts->pluck('id')->toArray();
        $productIdsWithAutoPo = $this->getProductIdsWithAutoPo();
        $allAvgSales = !empty($allIds)
            ? $service->getBatchDailySalesAvg($this->storeId, $allIds, 30)
            : [];

        $summary = [
            'total' => $allProducts->count(),
            'kritis' => 0,
            'menipis' => 0,
            'habis' => 0,
            'aman' => 0,
            'has_auto_po' => 0,
            'needs_reorder' => 0,
            'total_reorder_qty' => 0,
        ];

        foreach ($allProducts as $product) {
            $avgDailySales = $allAvgSales[$product->id] ?? 0;
            $runout = $service->stockRunoutPrediction($product, $avgDailySales);
            $reorder = $service->reorderRecommendation($product, $avgDailySales, $this->leadTimeDays, $this->safetyStock);
            $hasAutoPo = in_array($product->id, $productIdsWithAutoPo);

            $summary[$runout['status']]++;

            if ($hasAutoPo) {
                $summary['has_auto_po']++;
            }

            if ($reorder['recommended_qty'] > 0 && !$hasAutoPo) {
                $summary['needs_reorder']++;
                $summary['total_reorder_qty'] += $reorder['recommended_qty'];
            }
        }

        return $summary;
    }
}
