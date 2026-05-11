<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutServiceTest extends TestCase
{
    use RefreshDatabase;

    private CheckoutService $checkoutService;
    private Store $store;
    private User $user;
    private Product $product;
    private Product $subscriptionProduct;

    protected function setUp(): void
    {
        parent::setUp();
        $this->checkoutService = app(CheckoutService::class);
        $this->store = Store::factory()->create();
        $this->user = User::factory()->cashier()->create(['store_id' => $this->store->id]);
        $category = Category::factory()->create(['store_id' => $this->store->id]);

        $this->product = Product::factory()->create([
            'store_id' => $this->store->id,
            'category_id' => $category->id,
            'price' => 50000,
            'stock' => 100,
            'is_taxed' => false,
        ]);

        $this->subscriptionProduct = Product::factory()->subscription(30)->create([
            'store_id' => $this->store->id,
            'category_id' => $category->id,
            'price' => 100000,
            'stock' => 50,
            'is_taxed' => true,
            'tax_rate' => 11,
        ]);
    }

    public function test_calculate_subtotal(): void
    {
        $cart = [
            ['product_id' => 1, 'price' => 50000, 'quantity' => 2, 'subtotal' => 100000],
            ['product_id' => 2, 'price' => 25000, 'quantity' => 3, 'subtotal' => 75000],
        ];

        $subtotal = $this->checkoutService->calculateSubtotal($cart);

        $this->assertEquals(175000, $subtotal);
    }

    public function test_calculate_tax_when_disabled(): void
    {
        $cart = [
            ['subtotal' => 100000, 'is_taxed' => true, 'tax_rate' => 11],
        ];

        $tax = $this->checkoutService->calculateTax($cart, false);

        $this->assertEquals(0, $tax);
    }

    public function test_calculate_tax_when_enabled(): void
    {
        $cart = [
            ['subtotal' => 100000, 'is_taxed' => true, 'tax_rate' => 11],
            ['subtotal' => 50000, 'is_taxed' => false, 'tax_rate' => 0],
        ];

        $tax = $this->checkoutService->calculateTax($cart, true);

        $this->assertEquals(11000, $tax);
    }

    public function test_validate_cart_throws_when_empty(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Keranjang masih kosong.');

        $this->checkoutService->validateCart([], $this->store->id);
    }

    public function test_validate_cart_throws_when_stock_insufficient(): void
    {
        $cart = [
            ['product_id' => $this->product->id, 'quantity' => 999],
        ];

        $this->expectException(\RuntimeException::class);

        $this->checkoutService->validateCart($cart, $this->store->id);
    }

    public function test_process_checkout_creates_transaction(): void
    {
        $this->actingAs($this->user);

        $cart = [
            [
                'product_id' => $this->product->id,
                'name' => $this->product->name,
                'price' => (float) $this->product->price,
                'quantity' => 2,
                'subtotal' => (float) $this->product->price * 2,
                'is_taxed' => false,
                'tax_rate' => 0,
            ],
        ];

        $transaction = $this->checkoutService->processCheckout(
            cart: $cart,
            customerName: 'Test Customer',
            paymentMethod: 'cash',
            paymentAmount: 200000,
            taxEnabled: false,
            storeId: $this->store->id,
            userId: $this->user->id,
        );

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'invoice_number' => $transaction->invoice_number,
            'customer_name' => 'Test Customer',
            'payment_method' => 'cash',
            'total_amount' => 100000,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('transaction_items', [
            'transaction_id' => $transaction->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $this->assertEquals(98, $this->product->fresh()->stock);
    }

    public function test_process_checkout_with_subscription_product(): void
    {
        $this->actingAs($this->user);

        $cart = [
            [
                'product_id' => $this->subscriptionProduct->id,
                'name' => $this->subscriptionProduct->name,
                'price' => (float) $this->subscriptionProduct->price,
                'quantity' => 1,
                'subtotal' => (float) $this->subscriptionProduct->price,
                'is_taxed' => true,
                'tax_rate' => 11,
            ],
        ];

        $transaction = $this->checkoutService->processCheckout(
            cart: $cart,
            customerName: 'Subscriber',
            paymentMethod: 'cash',
            paymentAmount: 200000,
            taxEnabled: true,
            storeId: $this->store->id,
            userId: $this->user->id,
        );

        $this->assertDatabaseHas('subscriptions', [
            'transaction_id' => $transaction->id,
            'product_id' => $this->subscriptionProduct->id,
            'customer_identifier' => 'Subscriber',
            'status' => 'active',
        ]);

        $expectedTax = $this->subscriptionProduct->price * 0.11;
        $this->assertEquals($expectedTax, $transaction->tax_amount);
        $this->assertEquals($this->subscriptionProduct->price + $expectedTax, $transaction->total_amount);
    }

    public function test_process_checkout_throws_when_payment_less_than_total(): void
    {
        $this->actingAs($this->user);

        $cart = [
            [
                'product_id' => $this->product->id,
                'name' => $this->product->name,
                'price' => (float) $this->product->price,
                'quantity' => 2,
                'subtotal' => (float) $this->product->price * 2,
                'is_taxed' => false,
                'tax_rate' => 0,
            ],
        ];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Pembayaran kurang dari total.');

        $this->checkoutService->processCheckout(
            cart: $cart,
            customerName: '',
            paymentMethod: 'cash',
            paymentAmount: 50000,
            taxEnabled: false,
            storeId: $this->store->id,
            userId: $this->user->id,
        );
    }

    public function test_generate_invoice_number_format(): void
    {
        $invoice = $this->checkoutService->generateInvoiceNumber($this->store->id);

        $this->assertStringStartsWith('INV-' . date('Ymd') . '-', $invoice);
        $this->assertEquals(17, strlen($invoice));
    }

    public function test_process_checkout_creates_stock_movement(): void
    {
        $this->actingAs($this->user);

        $cart = [
            [
                'product_id' => $this->product->id,
                'name' => $this->product->name,
                'price' => (float) $this->product->price,
                'quantity' => 3,
                'subtotal' => (float) $this->product->price * 3,
                'is_taxed' => false,
                'tax_rate' => 0,
            ],
        ];

        $transaction = $this->checkoutService->processCheckout(
            cart: $cart,
            customerName: '',
            paymentMethod: 'cash',
            paymentAmount: 200000,
            taxEnabled: false,
            storeId: $this->store->id,
            userId: $this->user->id,
        );

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'type' => 'out',
            'quantity' => 3,
            'reference_type' => 'transaction',
            'reference_id' => $transaction->id,
        ]);
    }
}
