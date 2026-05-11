<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    private StockService $stockService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stockService = app(StockService::class);
    }

    private function createStoreWithUser(): array
    {
        $store = Store::factory()->create();
        $user = User::factory()->cashier()->create(['store_id' => $store->id]);
        $this->actingAs($user);
        return [$store, $user];
    }

    public function test_decrement_stock_reduces_product_stock(): void
    {
        [$store, $user] = $this->createStoreWithUser();
        $category = Category::factory()->create(['store_id' => $store->id]);
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'stock' => 100,
        ]);

        $this->stockService->decrementStock(
            $product, 5, 'test', null, $store->id, $user->id
        );

        $this->assertEquals(95, $product->fresh()->stock);
    }

    public function test_decrement_stock_creates_stock_movement(): void
    {
        [$store, $user] = $this->createStoreWithUser();
        $category = Category::factory()->create(['store_id' => $store->id]);
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'stock' => 100,
        ]);

        $this->stockService->decrementStock(
            $product, 5, 'transaction', 1, $store->id, $user->id, 'Penjualan #INV-001'
        );

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'out',
            'quantity' => 5,
            'reference_type' => 'transaction',
            'reference_id' => 1,
            'note' => 'Penjualan #INV-001',
        ]);
    }

    public function test_increment_stock_increases_product_stock(): void
    {
        [$store, $user] = $this->createStoreWithUser();
        $category = Category::factory()->create(['store_id' => $store->id]);
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'stock' => 50,
        ]);

        $this->stockService->incrementStock(
            $product, 20, 'purchase_order', 1, $store->id, $user->id
        );

        $this->assertEquals(70, $product->fresh()->stock);
    }

    public function test_increment_stock_creates_stock_movement(): void
    {
        [$store, $user] = $this->createStoreWithUser();
        $category = Category::factory()->create(['store_id' => $store->id]);
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'stock' => 50,
        ]);

        $this->stockService->incrementStock(
            $product, 20, 'purchase_order', 1, $store->id, $user->id
        );

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 20,
        ]);
    }

    public function test_validate_stock_availability_passes_when_sufficient(): void
    {
        [$store, $user] = $this->createStoreWithUser();
        $category = Category::factory()->create(['store_id' => $store->id]);
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'stock' => 10,
        ]);

        $this->stockService->validateStockAvailability($product, 5);

        $this->assertTrue(true);
    }

    public function test_validate_stock_availability_throws_when_insufficient(): void
    {
        [$store, $user] = $this->createStoreWithUser();
        $category = Category::factory()->create(['store_id' => $store->id]);
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'stock' => 3,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stok ' . $product->name . ' tidak mencukupi');

        $this->stockService->validateStockAvailability($product, 10);
    }
}
