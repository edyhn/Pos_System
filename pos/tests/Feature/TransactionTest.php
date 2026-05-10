<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_transaction_list(): void
    {
        $user = User::factory()->owner()->create(['store_id' => null]);
        $this->actingAs($user);

        $this->get('/transactions')->assertOk();
    }

    public function test_cashier_can_view_transaction_list(): void
    {
        $store = Store::factory()->create();
        $user = User::factory()->cashier()->create(['store_id' => $store->id]);
        $this->actingAs($user);

        $this->get('/transactions')->assertOk();
    }

    public function test_owner_can_view_transaction_detail(): void
    {
        $store = Store::factory()->create();
        $category = Category::factory()->create(['store_id' => $store->id]);
        $user = User::factory()->owner()->create(['store_id' => null]);

        $transaction = Transaction::factory()->create([
            'store_id' => $store->id,
            'user_id' => $user->id,
        ]);

        TransactionItem::factory()->create([
            'transaction_id' => $transaction->id,
            'product_id' => Product::factory()->create(['store_id' => $store->id, 'category_id' => $category->id])->id,
        ]);

        $this->actingAs($user);
        $this->get("/transactions/{$transaction->id}")->assertOk();
    }

    public function test_transaction_belongs_to_store(): void
    {
        $store1 = Store::factory()->create();
        $store2 = Store::factory()->create();
        $user = User::factory()->owner()->create(['store_id' => null]);

        Transaction::factory()->create(['store_id' => $store1->id, 'user_id' => $user->id]);
        Transaction::factory()->create(['store_id' => $store2->id, 'user_id' => $user->id]);

        $this->assertCount(1, Transaction::where('store_id', $store1->id)->get());
        $this->assertCount(1, Transaction::where('store_id', $store2->id)->get());
    }

    public function test_transaction_with_tax_calculation(): void
    {
        $store = Store::factory()->create();
        $user = User::factory()->owner()->create(['store_id' => null]);

        $transaction = Transaction::factory()->create([
            'store_id' => $store->id,
            'user_id' => $user->id,
            'subtotal' => 100000,
            'tax_amount' => 11000,
            'total_amount' => 111000,
        ]);

        $this->assertEquals(100000, $transaction->subtotal);
        $this->assertEquals(11000, $transaction->tax_amount);
        $this->assertEquals(111000, $transaction->total_amount);
    }

    public function test_transaction_items_are_accessible(): void
    {
        $store = Store::factory()->create();
        $category = Category::factory()->create(['store_id' => $store->id]);
        $user = User::factory()->owner()->create(['store_id' => null]);
        $product = Product::factory()->create(['store_id' => $store->id, 'category_id' => $category->id]);

        $transaction = Transaction::factory()->create([
            'store_id' => $store->id,
            'user_id' => $user->id,
        ]);

        TransactionItem::factory()->count(3)->create([
            'transaction_id' => $transaction->id,
            'product_id' => $product->id,
        ]);

        $this->assertCount(3, $transaction->items()->get());
    }

    public function test_transaction_invoice_number_is_created(): void
    {
        $store = Store::factory()->create();
        $user = User::factory()->owner()->create(['store_id' => null]);

        $transaction = Transaction::factory()->create([
            'store_id' => $store->id,
            'user_id' => $user->id,
        ]);

        $this->assertNotNull($transaction->invoice_number);
        $this->assertStringStartsWith('INV-', $transaction->invoice_number);
    }
}
