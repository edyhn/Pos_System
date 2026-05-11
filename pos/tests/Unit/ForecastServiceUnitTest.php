<?php

namespace Tests\Unit;

use App\Services\ForecastService;
use PHPUnit\Framework\TestCase;

class ForecastServiceUnitTest extends TestCase
{
    private ForecastService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ForecastService();
    }

    public function test_moving_average_with_period_1(): void
    {
        $data = [10, 20, 30];
        $result = $this->service->movingAverage($data, 1);

        $this->assertEquals([10, 20, 30], $result);
    }

    public function test_moving_average_with_empty_data(): void
    {
        $result = $this->service->movingAverage([], 7);
        $this->assertEquals([], $result);
    }

    public function test_moving_average_with_zero_period(): void
    {
        $data = [10, 20, 30, 40, 50];
        $result = $this->service->movingAverage($data, 0);

        $this->assertCount(5, $result);
        $this->assertEquals(10, $result[0]);
    }

    public function test_weighted_moving_average(): void
    {
        $data = [10, 20, 30, 40, 50];
        $result = $this->service->weightedMovingAverage($data, 3);

        $this->assertNull($result[0]);
        $this->assertNull($result[1]);
        $this->assertIsNumeric($result[2]);
        $this->assertIsNumeric($result[3]);
        $this->assertIsNumeric($result[4]);
    }

    public function test_exponential_smoothing_with_empty_data(): void
    {
        $result = $this->service->exponentialSmoothing([], 0.5);
        $this->assertEquals([], $result);
    }

    public function test_exponential_smoothing_clamps_alpha(): void
    {
        $data = [100, 110, 105];
        $resultLow = $this->service->exponentialSmoothing($data, 0);
        $resultHigh = $this->service->exponentialSmoothing($data, 1);

        $this->assertCount(3, $resultLow);
        $this->assertCount(3, $resultHigh);
    }

    public function test_linear_regression_with_less_than_2_points(): void
    {
        $data = [42];
        $result = $this->service->linearRegression($data);

        $this->assertEquals(0, $result['slope']);
        $this->assertEquals(0, $result['intercept']);
    }

    public function test_linear_regression_downward_trend(): void
    {
        $data = [50, 40, 30, 20, 10];
        $result = $this->service->linearRegression($data);

        $this->assertEquals('turun', $result['trend']);
        $this->assertLessThan(0, $result['slope']);
    }

    public function test_linear_regression_stable_trend(): void
    {
        $data = [10, 10, 10, 10, 10];
        $result = $this->service->linearRegression($data);

        $this->assertEquals('stabil', $result['trend']);
        $this->assertEquals(0, $result['slope']);
    }

    public function test_predict_future_with_insufficient_data(): void
    {
        $result = $this->service->predictFuture([1], 7, 5);
        $this->assertCount(5, $result);
    }

    public function test_seasonal_forecast_with_insufficient_data(): void
    {
        $result = $this->service->seasonalForecast([10, 20, 30], 7);
        $this->assertCount(7, $result);
        foreach ($result as $val) {
            $this->assertEquals(0, $val);
        }
    }

    public function test_fill_missing_dates_preserves_existing(): void
    {
        $today = now()->format('Y-m-d');
        $data = [$today => 100];
        $result = $this->service->fillMissingDates($data, 3);

        $this->assertCount(3, $result);
        $this->assertEquals(100, $result[$today]);
    }

}
