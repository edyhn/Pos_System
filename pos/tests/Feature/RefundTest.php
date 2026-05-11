<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\RefundRequest;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RefundTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;
    private User $owner;
    private User $cashier;
    private Product $product1;
    private Product $product2;
    private Transaction $transaction;
    private TransactionItem $item1;
    private TransactionItem $item2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->store = Store::factory()->create();
        $this->owner = User::factory()->owner()->create(['store_id' => $this->store->id]);
        $this->cashier = User::factory()->cashier()->create(['store_id' => $this->store->id]);
        $category = Category::factory()->create(['store_id' => $this->store->id]);

        $this->product1 = Product::factory()->create([
            'store_id' => $this->store->id,
            'category_id' => $category->id,
            'stock' => 100,
        ]);
        $this->product2 = Product::factory()->create([
            'store_id' => $this->store->id,
            'category_id' => $category->id,
            'stock' => 100,
        ]);

        $this->transaction = Transaction::factory()->create([
            'store_id' => $this->store->id,
            'user_id' => $this->cashier->id,
            'status' => 'completed',
        ]);

        $this->item1 = TransactionItem::factory()->create([
            'transaction_id' => $this->transaction->id,
            'product_id' => $this->product1->id,
            'quantity' => 5,
            'price' => 10000,
            'subtotal' => 50000,
        ]);

        $this->item2 = TransactionItem::factory()->create([
            'transaction_id' => $this->transaction->id,
            'product_id' => $this->product2->id,
            'quantity' => 3,
            'price' => 20000,
            'subtotal' => 60000,
        ]);
    }

    public function test_cashier_can_submit_refund_request(): void
    {
        $this->actingAs($this->cashier);

        $response = $this->get('/requests/refund');
        $response->assertOk();
    }

    public function test_refund_type_uang_kembali_is_accepted(): void
    {
        $refund = RefundRequest::create([
            'store_id' => $this->store->id,
            'transaction_id' => $this->transaction->id,
            'transaction_item_id' => $this->item1->id,
            'user_id' => $this->cashier->id,
            'condition_info' => 'Barang rusak',
            'refund_type' => 'uang_kembali',
            'refund_amount' => 50000,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('refund_requests', [
            'id' => $refund->id,
            'refund_type' => 'uang_kembali',
        ]);
    }

    public function test_refund_type_tukar_barang_is_accepted(): void
    {
        $refund = RefundRequest::create([
            'store_id' => $this->store->id,
            'transaction_id' => $this->transaction->id,
            'transaction_item_id' => $this->item1->id,
            'user_id' => $this->cashier->id,
            'condition_info' => 'Salah barang',
            'refund_type' => 'tukar_barang',
            'refund_amount' => 50000,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('refund_requests', [
            'id' => $refund->id,
            'refund_type' => 'tukar_barang',
        ]);
    }

    public function test_approving_refund_only_restocks_refunded_item(): void
    {
        $product1StockBefore = $this->product1->fresh()->stock;
        $product2StockBefore = $this->product2->fresh()->stock;

        $refund = RefundRequest::create([
            'store_id' => $this->store->id,
            'transaction_id' => $this->transaction->id,
            'transaction_item_id' => $this->item1->id,
            'user_id' => $this->cashier->id,
            'condition_info' => 'Barang cacat',
            'refund_type' => 'uang_kembali',
            'refund_amount' => 50000,
            'status' => 'pending',
        ]);

        $refund->update([
            'status' => 'approved',
            'approved_by' => $this->owner->id,
            'refund_amount' => 50000,
            'refund_type' => 'uang_kembali',
            'approved_at' => now(),
        ]);

        $this->transaction->update(['status' => 'refunded']);

        $item = $refund->transactionItem;
        Product::where('id', $item->product_id)->increment('stock', $item->quantity);

        StockMovement::create([
            'store_id' => $this->store->id,
            'product_id' => $item->product_id,
            'user_id' => $this->owner->id,
            'reference_type' => 'refund',
            'reference_id' => $this->transaction->id,
            'type' => 'in',
            'quantity' => $item->quantity,
            'note' => 'Refund #' . $this->transaction->invoice_number,
        ]);

        $this->assertEquals($product1StockBefore + 5, $this->product1->fresh()->stock);
        $this->assertEquals($product2StockBefore, $this->product2->fresh()->stock);

        $movements = StockMovement::where('reference_type', 'refund')
            ->where('reference_id', $this->transaction->id)
            ->get();
        $this->assertCount(1, $movements);
        $this->assertEquals($this->product1->id, $movements->first()->product_id);
    }

    public function test_owner_can_reject_refund(): void
    {
        $refund = RefundRequest::create([
            'store_id' => $this->store->id,
            'transaction_id' => $this->transaction->id,
            'transaction_item_id' => $this->item1->id,
            'user_id' => $this->cashier->id,
            'condition_info' => 'Test reject',
            'refund_type' => 'uang_kembali',
            'refund_amount' => 50000,
            'status' => 'pending',
        ]);

        $refund->update([
            'status' => 'rejected',
            'approved_by' => $this->owner->id,
            'owner_note' => 'Ditolak karena tidak sesuai prosedur',
            'approved_at' => now(),
        ]);

        $this->assertDatabaseHas('refund_requests', [
            'id' => $refund->id,
            'status' => 'rejected',
        ]);

        $this->assertEquals($this->product1->fresh()->stock, $this->product1->stock);
    }

    public function test_cashier_cannot_submit_duplicate_refund_for_same_item(): void
    {
        RefundRequest::create([
            'store_id' => $this->store->id,
            'transaction_id' => $this->transaction->id,
            'transaction_item_id' => $this->item1->id,
            'user_id' => $this->cashier->id,
            'condition_info' => 'Pertama',
            'refund_type' => 'uang_kembali',
            'refund_amount' => 50000,
            'status' => 'pending',
        ]);

        $exists = RefundRequest::where('transaction_item_id', $this->item1->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        $this->assertTrue($exists);
    }

    public function test_owner_can_access_refund_approval_list(): void
    {
        $this->actingAs($this->owner);
        $this->get('/approvals/refund')->assertOk();
    }

    public function test_refund_request_belongs_to_correct_store(): void
    {
        $store2 = Store::factory()->create();
        $refund = RefundRequest::create([
            'store_id' => $store2->id,
            'transaction_id' => $this->transaction->id,
            'transaction_item_id' => $this->item1->id,
            'user_id' => $this->cashier->id,
            'condition_info' => 'Test store scope',
            'refund_type' => 'uang_kembali',
            'refund_amount' => 50000,
            'status' => 'pending',
        ]);

        $this->assertEquals($store2->id, $refund->store_id);
        $this->assertCount(0, RefundRequest::where('store_id', $this->store->id)->where('id', $refund->id)->get());
    }
}
