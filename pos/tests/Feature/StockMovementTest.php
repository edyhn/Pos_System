<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockMovementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_stock_movement_in(): void
    {
        $user = User::factory()->owner()->create(['store_id' => null]);
        $this->actingAs($user);

        $this->get('/stock-movement/in')->assertOk();
    }

    public function test_owner_can_view_stock_movement_out(): void
    {
        $user = User::factory()->owner()->create(['store_id' => null]);
        $this->actingAs($user);

        $this->get('/stock-movement/out')->assertOk();
    }

    public function test_stock_movement_record_creation(): void
    {
        $store = Store::factory()->create();
        $category = Category::factory()->create(['store_id' => $store->id]);
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
        ]);
        $user = User::factory()->owner()->create(['store_id' => null]);

        $movement = StockMovement::create([
            'store_id' => $store->id,
            'product_id' => $product->id,
            'user_id' => $user->id,
            'type' => 'in',
            'quantity' => 5,
            'reference_type' => 'adjustment',
            'note' => 'Manual adjustment',
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'id' => $movement->id,
            'type' => 'in',
            'quantity' => 5,
        ]);
    }

    public function test_stock_movement_types_are_filtered(): void
    {
        $store = Store::factory()->create();
        $category = Category::factory()->create(['store_id' => $store->id]);
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
        ]);
        $user = User::factory()->owner()->create(['store_id' => null]);

        StockMovement::create([
            'store_id' => $store->id,
            'product_id' => $product->id,
            'user_id' => $user->id,
            'type' => 'in',
            'quantity' => 10,
            'reference_type' => 'adjustment',
        ]);

        StockMovement::create([
            'store_id' => $store->id,
            'product_id' => $product->id,
            'user_id' => $user->id,
            'type' => 'out',
            'quantity' => 3,
            'reference_type' => 'adjustment',
        ]);

        $this->assertCount(1, StockMovement::where('type', 'in')->get());
        $this->assertCount(1, StockMovement::where('type', 'out')->get());
        $this->assertCount(2, StockMovement::all());
    }
}
