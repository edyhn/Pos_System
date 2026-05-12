<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class ForecastService
{
    public function getDailySales($storeId, $days = 90)
    {
        return Transaction::where('store_id', $storeId)
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subDays($days - 1)->startOfDay())
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();
    }

    public function getDailySalesByProduct($storeId, $productId, $days = 90)
    {
        return DB::table('transaction_items')
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->where('transactions.store_id', $storeId)
            ->where('transactions.status', 'completed')
            ->where('transaction_items.product_id', $productId)
            ->where('transactions.created_at', '>=', now()->subDays($days - 1)->startOfDay())
            ->selectRaw('DATE(transactions.created_at) as date, SUM(transaction_items.quantity) as qty, SUM(transaction_items.subtotal) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date')
            ->toArray();
    }

    public function fillMissingDates(array $data, $days = 90): array
    {
        $result = [];
        $start = now()->subDays($days - 1)->startOfDay();
        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i)->format('Y-m-d');
            $result[$date] = $data[$date] ?? 0;
        }
        return $result;
    }

    public function movingAverage(array $data, int $period = 7): array
    {
        if ($period <= 0) $period = 1;
        $values = array_values($data);
        $result = [];
        for ($i = 0; $i < count($values); $i++) {
            if ($i < $period - 1) {
                $result[] = null;
            } else {
                $sum = 0;
                for ($j = $i - $period + 1; $j <= $i; $j++) {
                    $sum += $values[$j];
                }
                $result[] = round($sum / $period);
            }
        }
        return $result;
    }

    public function weightedMovingAverage(array $data, int $period = 7): array
    {
        if ($period <= 0) $period = 1;
        $values = array_values($data);
        $weights = [];
        $weightSum = 0;
        for ($w = 1; $w <= $period; $w++) {
            $weights[] = $w;
            $weightSum += $w;
        }

        $result = [];
        for ($i = 0; $i < count($values); $i++) {
            if ($i < $period - 1) {
                $result[] = null;
            } else {
                $sum = 0;
                for ($j = $i - $period + 1, $wIdx = 0; $j <= $i; $j++, $wIdx++) {
                    $sum += $values[$j] * $weights[$wIdx];
                }
                $result[] = round($sum / $weightSum);
            }
        }
        return $result;
    }

    public function exponentialSmoothing(array $data, float $alpha = 0.3): array
    {
        if ($alpha <= 0) $alpha = 0.1;
        if ($alpha >= 1) $alpha = 0.9;
        $values = array_values($data);
        $result = [];
        if (empty($values)) return $result;

        $result[] = $values[0];
        for ($i = 1; $i < count($values); $i++) {
            $result[] = round($alpha * $values[$i] + (1 - $alpha) * $result[$i - 1]);
        }
        return $result;
    }

    public function linearRegression(array $data): array
    {
        $values = array_values($data);
        $n = count($values);
        if ($n < 2) return ['slope' => 0, 'intercept' => 0, 'predictions' => $values];

        $xSum = 0;
        $ySum = 0;
        $xySum = 0;
        $x2Sum = 0;

        foreach ($values as $i => $y) {
            $x = $i + 1;
            $xSum += $x;
            $ySum += $y;
            $xySum += $x * $y;
            $x2Sum += $x * $x;
        }

        $denominator = $n * $x2Sum - $xSum * $xSum;
        if (abs($denominator) < 1e-10) {
            return ['slope' => 0, 'intercept' => $n > 0 ? $ySum / $n : 0, 'predictions' => $values];
        }

        $slope = ($n * $xySum - $xSum * $ySum) / $denominator;
        $intercept = ($ySum - $slope * $xSum) / $n;

        $predictions = [];
        foreach ($values as $i => $y) {
            $x = $i + 1;
            $predictions[] = round($slope * $x + $intercept);
        }

        for ($i = 0; $i < 7; $i++) {
            $x = $n + $i + 1;
            $predictions[] = round($slope * $x + $intercept);
        }

        return [
            'slope' => $slope,
            'intercept' => $intercept,
            'predictions' => $predictions,
            'trend' => $slope > 0 ? 'naik' : ($slope < 0 ? 'turun' : 'stabil'),
        ];
    }

    public function predictFuture(array $data, int $period = 7, int $futureDays = 7): array
    {
        if ($period <= 0) $period = 1;
        $values = array_values($data);
        $n = count($values);
        if ($n < $period) return array_fill(0, $futureDays, 0);

        $regression = $this->linearRegression($data);
        $slope = $regression['slope'];
        $intercept = $regression['intercept'];

        $predictions = [];
        for ($i = 0; $i < $futureDays; $i++) {
            $x = $n + $i + 1;
            $predicted = round($slope * $x + $intercept);
            $predictions[] = max(0, $predicted);
        }
        return $predictions;
    }

    public function seasonalForecast(array $data, int $period = 7): array
    {
        if ($period <= 0) $period = 1;
        $values = array_values($data);
        $n = count($values);
        if ($n < $period * 2) return array_fill(0, $period, 0);

        $seasonalIndices = [];
        for ($d = 0; $d < $period; $d++) {
            $sum = 0;
            $count = 0;
            for ($i = $d; $i < $n; $i += $period) {
                $sum += $values[$i];
                $count++;
            }
            $seasonalIndices[$d] = $count > 0 ? $sum / $count : 0;
        }

        $overallAvg = array_sum($values) / $n;
        $seasonalFactors = [];
        foreach ($seasonalIndices as $i => $val) {
            $seasonalFactors[$i] = $overallAvg > 0 ? $val / $overallAvg : 1;
        }

        $lastAvg = array_sum(array_slice($values, -$period)) / $period;
        $predictions = [];
        for ($i = 0; $i < $period; $i++) {
            $predictions[] = round($lastAvg * ($seasonalFactors[$i] ?? 1));
        }
        return $predictions;
    }

    public function stockRunoutPrediction(Product $product, float $avgDailySales): array
    {
        $stock = $product->stock ?? 0;
        $minStock = $product->min_stock ?? 0;

        if ($stock <= 0) {
            return ['estimated_days' => 0, 'estimated_date' => 'Hari ini', 'status' => 'habis'];
        }

        if ($stock <= $minStock) {
            return ['estimated_days' => null, 'estimated_date' => 'Di bawah minimum', 'status' => 'kritis'];
        }

        if ($stock <= $minStock * 2) {
            return ['estimated_days' => null, 'estimated_date' => 'Mendekati minimum', 'status' => 'menipis'];
        }

        if ($avgDailySales > 0) {
            $estimatedDays = (int) floor($stock / $avgDailySales);
            $estimatedDate = now()->addDays($estimatedDays);

            if ($estimatedDays <= 7) {
                return ['estimated_days' => $estimatedDays, 'estimated_date' => $estimatedDate->format('d/m/Y'), 'status' => 'kritis'];
            }

            if ($estimatedDays <= 30) {
                return ['estimated_days' => $estimatedDays, 'estimated_date' => $estimatedDate->format('d/m/Y'), 'status' => 'menipis'];
            }
        }

        return ['estimated_days' => null, 'estimated_date' => 'Stok aman', 'status' => 'aman'];
    }

    public function reorderRecommendation(Product $product, float $avgDailySales, int $leadTimeDays = 7, int $safetyStock = 10): array
    {
        $stock = $product->stock ?? 0;

        if ($avgDailySales <= 0) {
            $minStock = $product->min_stock ?? 0;
            $targetStock = max($minStock, $safetyStock);
            $recommended = $stock < $targetStock ? $targetStock - $stock : 0;
            return [
                'recommended_qty' => max(0, $recommended),
                'safety_stock' => $safetyStock,
                'lead_time_days' => $leadTimeDays,
                'avg_daily_sales' => 0,
            ];
        }

        $usageDuringLeadTime = $avgDailySales * $leadTimeDays;
        $recommended = (int) ceil(($usageDuringLeadTime + $safetyStock) - $stock);
        $recommended = max(0, $recommended);

        return [
            'recommended_qty' => $recommended,
            'safety_stock' => $safetyStock,
            'lead_time_days' => $leadTimeDays,
            'avg_daily_sales' => round($avgDailySales, 1),
        ];
    }

    public function getProductDailySalesAvg(Product $product, int $days = 30): float
    {
        $data = $this->getDailySalesByProduct($product->store_id, $product->id, $days);
        if (empty($data)) return 0;

        $totalQty = 0;
        foreach ($data as $day) {
            $totalQty += is_object($day) ? ($day->qty ?? 0) : ($day['qty'] ?? 0);
        }
        return $totalQty / $days;
    }

    public function getBatchDailySalesAvg(int $storeId, array $productIds, int $days = 30): array
    {
        if (empty($productIds)) return [];

        $salesData = DB::table('transaction_items')
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->where('transactions.store_id', $storeId)
            ->where('transactions.status', 'completed')
            ->whereIn('transaction_items.product_id', $productIds)
            ->whereDate('transactions.created_at', '>=', now()->subDays($days))
            ->selectRaw('transaction_items.product_id, SUM(transaction_items.quantity) as total_qty')
            ->groupBy('transaction_items.product_id')
            ->pluck('total_qty', 'product_id');

        $result = [];
        foreach ($productIds as $id) {
            $totalQty = (int) ($salesData[$id] ?? 0);
            $result[$id] = $totalQty / $days;
        }
        return $result;
    }
}
