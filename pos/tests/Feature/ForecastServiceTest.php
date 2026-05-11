<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\ForecastService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForecastServiceTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;
    private Product $product;
    private ForecastService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->store = Store::factory()->create();
        $category = Category::factory()->create(['store_id' => $this->store->id]);
        $this->product = Product::factory()->create([
            'store_id' => $this->store->id,
            'category_id' => $category->id,
            'stock' => 100,
            'min_stock' => 10,
        ]);
        $this->service = new ForecastService();
    }

    public function test_stock_runout_prediction_with_zero_stock(): void
    {
        $this->product->stock = 0;
        $result = $this->service->stockRunoutPrediction($this->product, 10);

        $this->assertEquals('habis', $result['status']);
        $this->assertEquals(0, $result['estimated_days']);
    }

    public function test_stock_runout_prediction_below_minimum(): void
    {
        $this->product->stock = 5;
        $this->product->min_stock = 10;
        $result = $this->service->stockRunoutPrediction($this->product, 5);

        $this->assertEquals('kritis', $result['status']);
    }

    public function test_stock_runout_prediction_approaching_minimum(): void
    {
        $this->product->stock = 15;
        $this->product->min_stock = 10;
        $result = $this->service->stockRunoutPrediction($this->product, 5);

        $this->assertEquals('menipis', $result['status']);
    }

    public function test_stock_runout_prediction_safe(): void
    {
        $this->product->stock = 100;
        $this->product->min_stock = 10;
        $result = $this->service->stockRunoutPrediction($this->product, 2);

        $this->assertEquals('aman', $result['status']);
    }

    public function test_reorder_recommendation_with_sales_data(): void
    {
        $this->product->stock = 20;
        $result = $this->service->reorderRecommendation($this->product, 5, 7, 10);

        $this->assertGreaterThan(0, $result['recommended_qty']);
        $this->assertEquals(7, $result['lead_time_days']);
        $this->assertEquals(10, $result['safety_stock']);
    }

    public function test_reorder_recommendation_with_sufficient_stock(): void
    {
        $this->product->stock = 100;
        $result = $this->service->reorderRecommendation($this->product, 2, 7, 10);

        $this->assertEquals(0, $result['recommended_qty']);
    }

    public function test_reorder_recommendation_with_no_sales(): void
    {
        $this->product->stock = 5;
        $this->product->min_stock = 10;
        $result = $this->service->reorderRecommendation($this->product, 0, 7, 10);

        $this->assertGreaterThan(0, $result['recommended_qty']);
    }

    public function test_batch_daily_sales_avg_returns_zero_for_no_data(): void
    {
        $product2 = Product::factory()->create([
            'store_id' => $this->store->id,
            'category_id' => Category::factory()->create(['store_id' => $this->store->id])->id,
        ]);

        $result = $this->service->getBatchDailySalesAvg($this->store->id, [$this->product->id, $product2->id], 30);

        $this->assertArrayHasKey($this->product->id, $result);
        $this->assertArrayHasKey($product2->id, $result);
        $this->assertEquals(0, $result[$this->product->id]);
        $this->assertEquals(0, $result[$product2->id]);
    }

    public function test_batch_daily_sales_avg_with_empty_ids(): void
    {
        $result = $this->service->getBatchDailySalesAvg($this->store->id, [], 30);
        $this->assertEquals([], $result);
    }

    public function test_moving_average(): void
    {
        $data = [10, 20, 30, 40, 50];
        $result = $this->service->movingAverage($data, 3);

        $this->assertNull($result[0]);
        $this->assertNull($result[1]);
        $this->assertEquals(20, $result[2]);
        $this->assertEquals(30, $result[3]);
        $this->assertEquals(40, $result[4]);
    }

    public function test_exponential_smoothing(): void
    {
        $data = [100, 110, 105, 115, 120];
        $result = $this->service->exponentialSmoothing($data, 0.3);

        $this->assertCount(5, $result);
        $this->assertEquals(100, $result[0]);
    }

    public function test_linear_regression(): void
    {
        $data = [10, 20, 30, 40, 50];
        $result = $this->service->linearRegression($data);

        $this->assertEquals('naik', $result['trend']);
        $this->assertGreaterThan(0, $result['slope']);
        $this->assertCount(12, $result['predictions']);
    }

    public function test_predict_future(): void
    {
        $data = [10, 20, 30, 40, 50, 60, 70];
        $result = $this->service->predictFuture($data, 3, 5);

        $this->assertCount(5, $result);
        foreach ($result as $val) {
            $this->assertGreaterThanOrEqual(0, $val);
        }
    }

    public function test_fill_missing_dates(): void
    {
        $today = now()->format('Y-m-d');
        $result = $this->service->fillMissingDates([$today => 100], 5);

        $this->assertCount(5, $result);
        $this->assertEquals(100, $result[$today]);
    }

    public function test_get_product_daily_sales_avg_returns_zero_for_no_data(): void
    {
        $avg = $this->service->getProductDailySalesAvg($this->product, 30);
        $this->assertEquals(0, $avg);
    }

    public function test_seasonal_forecast(): void
    {
        $data = [10, 20, 10, 20, 10, 20, 10, 20, 10, 20, 10, 20, 10, 20];
        $result = $this->service->seasonalForecast($data, 7);

        $this->assertCount(7, $result);
    }

    public function test_get_product_daily_sales_avg_matches_batch_result(): void
    {
        $singleAvg = $this->service->getProductDailySalesAvg($this->product, 30);
        $batchResult = $this->service->getBatchDailySalesAvg($this->store->id, [$this->product->id], 30);

        $this->assertEquals($singleAvg, $batchResult[$this->product->id]);
    }
}
