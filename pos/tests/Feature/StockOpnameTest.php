<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockOpnameTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;
    private User $owner;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->store = Store::factory()->create();
        $this->owner = User::factory()->owner()->create(['store_id' => $this->store->id]);
        $category = Category::factory()->create(['store_id' => $this->store->id]);
        $this->product = Product::factory()->create([
            'store_id' => $this->store->id,
            'category_id' => $category->id,
            'stock' => 50,
        ]);
    }

    public function test_owner_can_access_stock_opname_page(): void
    {
        $this->actingAs($this->owner);
        $this->get('/stock-opname')->assertOk();
    }

    public function test_opname_difference_calculation(): void
    {
        $systemStock = 50;
        $actualStock = 45;
        $difference = $actualStock - $systemStock;

        $this->assertEquals(-5, $difference);

        $actualStock2 = 55;
        $difference2 = $actualStock2 - $systemStock;
        $this->assertEquals(5, $difference2);
    }

    public function test_opname_adjusts_stock_correctly(): void
    {
        $opname = StockOpname::create([
            'store_id' => $this->store->id,
            'user_id' => $this->owner->id,
            'date' => now()->format('Y-m-d'),
            'status' => 'completed',
            'notes' => 'Bulanan',
        ]);

        StockOpnameItem::create([
            'stock_opname_id' => $opname->id,
            'product_id' => $this->product->id,
            'system_stock' => 50,
            'actual_stock' => 45,
            'difference' => -5,
            'note' => 'Hilang 5',
        ]);

        $expectedStock = 45;
        $this->product->increment('stock', -5);
        $this->assertEquals($expectedStock, $this->product->fresh()->stock);

        StockMovement::create([
            'store_id' => $this->store->id,
            'product_id' => $this->product->id,
            'user_id' => $this->owner->id,
            'reference_type' => 'opname',
            'reference_id' => $opname->id,
            'type' => 'out',
            'quantity' => abs(-5),
            'note' => 'Stock opname',
        ]);

        $movement = StockMovement::where('reference_type', 'opname')
            ->where('reference_id', $opname->id)
            ->first();

        $this->assertNotNull($movement);
        $this->assertEquals('out', $movement->type);
        $this->assertEquals(5, $movement->quantity);
    }

    public function test_opname_increases_stock_when_actual_higher(): void
    {
        $opname = StockOpname::create([
            'store_id' => $this->store->id,
            'user_id' => $this->owner->id,
            'date' => now()->format('Y-m-d'),
            'status' => 'completed',
            'notes' => 'Kelebihan stok',
        ]);

        StockOpnameItem::create([
            'stock_opname_id' => $opname->id,
            'product_id' => $this->product->id,
            'system_stock' => 50,
            'actual_stock' => 60,
            'difference' => 10,
            'note' => 'Kelebihan 10',
        ]);

        $this->product->increment('stock', 10);
        $this->assertEquals(60, $this->product->fresh()->stock);

        StockMovement::create([
            'store_id' => $this->store->id,
            'product_id' => $this->product->id,
            'user_id' => $this->owner->id,
            'reference_type' => 'opname',
            'reference_id' => $opname->id,
            'type' => 'in',
            'quantity' => 10,
            'note' => 'Stock opname',
        ]);

        $movement = StockMovement::where('reference_type', 'opname')
            ->where('reference_id', $opname->id)
            ->first();

        $this->assertNotNull($movement);
        $this->assertEquals('in', $movement->type);
        $this->assertEquals(10, $movement->quantity);
    }

    public function test_opname_with_zero_difference_does_not_create_movement(): void
    {
        $opname = StockOpname::create([
            'store_id' => $this->store->id,
            'user_id' => $this->owner->id,
            'date' => now()->format('Y-m-d'),
            'status' => 'completed',
        ]);

        StockOpnameItem::create([
            'stock_opname_id' => $opname->id,
            'product_id' => $this->product->id,
            'system_stock' => 50,
            'actual_stock' => 50,
            'difference' => 0,
        ]);

        $movements = StockMovement::where('reference_type', 'opname')
            ->where('reference_id', $opname->id)
            ->get();

        $this->assertCount(0, $movements);
    }

    public function test_opname_items_are_scoped_by_opname(): void
    {
        $opname1 = StockOpname::create([
            'store_id' => $this->store->id,
            'user_id' => $this->owner->id,
            'date' => '2026-01-01',
            'status' => 'completed',
        ]);

        $opname2 = StockOpname::create([
            'store_id' => $this->store->id,
            'user_id' => $this->owner->id,
            'date' => '2026-02-01',
            'status' => 'completed',
        ]);

        StockOpnameItem::create([
            'stock_opname_id' => $opname1->id,
            'product_id' => $this->product->id,
            'system_stock' => 50,
            'actual_stock' => 45,
            'difference' => -5,
        ]);

        StockOpnameItem::create([
            'stock_opname_id' => $opname2->id,
            'product_id' => $this->product->id,
            'system_stock' => 45,
            'actual_stock' => 45,
            'difference' => 0,
        ]);

        $this->assertCount(1, $opname1->items);
        $this->assertCount(1, $opname2->items);
    }

    public function test_cashier_cannot_access_stock_opname(): void
    {
        $cashier = User::factory()->cashier()->create(['store_id' => $this->store->id]);
        $this->actingAs($cashier);
        $this->get('/stock-opname')->assertForbidden();
    }
}
