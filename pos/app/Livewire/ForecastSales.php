<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\ForecastService;
use Livewire\Component;

class ForecastSales extends Component
{
    public $storeId;
    public $dateRange = 90;
    public $period = 7;
    public $alpha = 0.3;
    public $futureDays = 14;
    public $productFilter = '';
    public $products;
    public $activeTab = 'sma';

    public array $historicalLabels = [];
    public array $historicalValues = [];
    public array $forecastLabels = [];

    public array $smaValues = [];
    public array $wmaValues = [];
    public array $sesValues = [];
    public array $regressionValues = [];
    public array $seasonalValues = [];
    public array $futureValues = [];

    public array $stats = [];
    public string $smaChartJson = '';
    public string $wmaChartJson = '';
    public string $sesChartJson = '';
    public string $regressionChartJson = '';
    public string $seasonalChartJson = '';
    public string $futureChartJson = '';

    public function mount()
    {
        $this->storeId = auth()->user()->store_id;
        $this->products = Product::where('store_id', $this->storeId)->where('is_active', true)->orderBy('name')->get();
        $this->calculate();
    }

    public function updatedDateRange() { $this->calculate(); }
    public function updatedPeriod() { $this->calculate(); }
    public function updatedAlpha() { $this->calculate(); }
    public function updatedFutureDays() { $this->calculate(); }
    public function updatedProductFilter() { $this->calculate(); }

    protected function chartJson($type, $labels, $datasets): string
    {
        $config = [
            'type' => $type,
            'data' => [
                'labels' => $labels,
                'datasets' => $datasets,
            ],
            'options' => [
                'responsive' => true,
                'plugins' => ['legend' => ['position' => 'top']],
                'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['callback' => 'formatRupiah']]],
            ],
        ];
        return json_encode($config);
    }

    public function calculate()
    {
        $service = new ForecastService();

        if ($this->productFilter) {
            $rawData = $service->getDailySalesByProduct($this->storeId, $this->productFilter, $this->dateRange);
            $dailyData = [];
            foreach ($rawData as $date => $item) {
                $dailyData[$date] = is_object($item) ? ($item->total ?? 0) : ($item['total'] ?? 0);
            }
        } else {
            $dailyData = $service->getDailySales($this->storeId, $this->dateRange);
        }

        $filledData = $service->fillMissingDates($dailyData, $this->dateRange);

        $labels = array_values(array_keys($filledData));
        $values = array_values($filledData);

        $this->historicalLabels = $labels;
        $this->historicalValues = $values;

        $futureStart = now()->addDay()->format('Y-m-d');
        $this->forecastLabels = [];
        for ($i = 0; $i < $this->futureDays; $i++) {
            $this->forecastLabels[] = \Carbon\Carbon::parse($futureStart)->addDays($i)->format('D d/m');
        }

        $this->smaValues = $service->movingAverage($filledData, $this->period);
        $this->wmaValues = $service->weightedMovingAverage($filledData, $this->period);
        $this->sesValues = $service->exponentialSmoothing($filledData, $this->alpha);

        $regression = $service->linearRegression($filledData);
        $this->regressionValues = $regression['predictions'];

        $this->seasonalValues = $service->seasonalForecast($filledData, 7);

        $future = $service->predictFuture($filledData, $this->period, $this->futureDays);
        $this->futureValues = $future;

        $actualValues = array_filter($values, fn($v) => $v > 0);
        $totalSales = array_sum($actualValues);
        $count = count($actualValues);
        $avgDaily = $count > 0 ? $totalSales / $count : 0;

        $futureTotal = array_sum($future);

        $this->stats = [
            'total_sales' => $totalSales,
            'avg_daily' => round($avgDaily),
            'peak_value' => !empty($actualValues) ? max($actualValues) : 0,
            'days_data' => $count,
            'future_total' => $futureTotal,
            'future_avg' => $this->futureDays > 0 ? round($futureTotal / $this->futureDays) : 0,
            'trend' => $regression['trend'],
            'slope' => round($regression['slope'], 2),
        ];

        $this->buildChartConfigs($labels, $values);
    }

    protected function buildChartConfigs(array $labels, array $values): void
    {
        $actualDataset = [
            'label' => 'Aktual',
            'data' => $values,
            'borderColor' => '#94a3b8',
            'backgroundColor' => 'rgba(148,163,184,0.1)',
            'fill' => true,
            'tension' => 0.3,
            'pointRadius' => 2,
            'borderWidth' => 1,
        ];

        $this->smaChartJson = $this->chartJson('line', $labels, [
            $actualDataset,
            [
                'label' => "SMA ({$this->period})",
                'data' => $this->smaValues,
                'borderColor' => '#3b82f6',
                'backgroundColor' => 'transparent',
                'tension' => 0.3,
                'pointRadius' => 0,
                'borderWidth' => 2,
            ],
        ]);

        $this->wmaChartJson = $this->chartJson('line', $labels, [
            $actualDataset,
            [
                'label' => "WMA ({$this->period})",
                'data' => $this->wmaValues,
                'borderColor' => '#8b5cf6',
                'backgroundColor' => 'transparent',
                'tension' => 0.3,
                'pointRadius' => 0,
                'borderWidth' => 2,
            ],
        ]);

        $this->sesChartJson = $this->chartJson('line', $labels, [
            $actualDataset,
            [
                'label' => "SES (alpha={$this->alpha})",
                'data' => $this->sesValues,
                'borderColor' => '#10b981',
                'backgroundColor' => 'transparent',
                'tension' => 0.3,
                'pointRadius' => 0,
                'borderWidth' => 2,
            ],
        ]);

        $this->regressionChartJson = $this->chartJson('line', $labels, [
            $actualDataset,
            [
                'label' => 'Regresi',
                'data' => array_slice($this->regressionValues, 0, count($labels)),
                'borderColor' => '#f59e0b',
                'backgroundColor' => 'transparent',
                'tension' => 0,
                'pointRadius' => 0,
                'borderWidth' => 2,
                'borderDash' => [5, 5],
            ],
        ]);

        $this->seasonalChartJson = $this->chartJson('bar', ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'], [
            [
                'label' => 'Rata-rata per Hari',
                'data' => $this->seasonalValues,
                'backgroundColor' => 'rgba(59,130,246,0.7)',
                'borderColor' => '#3b82f6',
                'borderWidth' => 1,
            ],
        ]);

        $futureConfig = [
            'type' => 'line',
            'data' => [
                'labels' => $this->forecastLabels,
                'datasets' => [
                    [
                        'label' => 'Prediksi',
                        'data' => $this->futureValues,
                        'borderColor' => '#3b82f6',
                        'backgroundColor' => 'rgba(59,130,246,0.15)',
                        'fill' => true,
                        'tension' => 0.3,
                        'pointRadius' => 4,
                        'borderWidth' => 2,
                    ],
                ],
            ],
            'options' => [
                'responsive' => true,
                'plugins' => ['legend' => ['position' => 'top']],
                'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['callback' => 'formatRupiah']]],
            ],
        ];
        $this->futureChartJson = json_encode($futureConfig);
    }

    public function render()
    {
        return view('livewire.forecast-sales');
    }
}
