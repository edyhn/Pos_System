<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_product_list(): void
    {
        $user = User::factory()->owner()->create(['store_id' => null]);
        $this->actingAs($user);

        $this->get('/products')->assertOk();
    }

    public function test_product_low_stock_detection(): void
    {
        $store = Store::factory()->create();
        $category = Category::factory()->create(['store_id' => $store->id]);
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'stock' => 5,
            'min_stock' => 10,
        ]);

        $this->assertTrue($product->isLowStock());

        $product->stock = 15;
        $this->assertFalse($product->isLowStock());
    }

    public function test_products_are_scoped_by_store(): void
    {
        $store1 = Store::factory()->create();
        $store2 = Store::factory()->create();
        $cat1 = Category::factory()->create(['store_id' => $store1->id]);
        $cat2 = Category::factory()->create(['store_id' => $store2->id]);

        Product::factory()->create(['store_id' => $store1->id, 'category_id' => $cat1->id]);
        Product::factory()->create(['store_id' => $store2->id, 'category_id' => $cat2->id]);

        $this->assertCount(1, Product::where('store_id', $store1->id)->get());
        $this->assertCount(1, Product::where('store_id', $store2->id)->get());
    }

    public function test_taxed_product_calculates_correctly(): void
    {
        $store = Store::factory()->create();
        $category = Category::factory()->create(['store_id' => $store->id]);
        $product = Product::factory()->taxed()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'price' => 100000,
        ]);

        $expectedTax = 100000 * 11 / 100;
        $this->assertEquals(11000, $expectedTax);
        $this->assertEquals(111000, 100000 + $expectedTax);
    }

    public function test_subscription_product(): void
    {
        $store = Store::factory()->create();
        $category = Category::factory()->create(['store_id' => $store->id]);
        $product = Product::factory()->subscription(30)->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
        ]);

        $this->assertTrue($product->is_subscription);
        $this->assertEquals(30, $product->subscription_days);
    }
}
