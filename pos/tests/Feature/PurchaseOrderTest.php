<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Store;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_access_po_page(): void
    {
        $user = User::factory()->owner()->create(['store_id' => null]);
        $this->actingAs($user);

        $this->get('/purchase-orders')->assertOk();
    }

    public function test_owner_can_access_create_po_page(): void
    {
        $user = User::factory()->owner()->create(['store_id' => null]);
        $this->actingAs($user);

        $this->get('/purchase-orders/create')->assertOk();
    }

    public function test_po_belongs_to_store(): void
    {
        $store = Store::factory()->create();
        $vendor = Vendor::factory()->create(['store_id' => $store->id]);
        $user = User::factory()->owner()->create(['store_id' => null]);

        $po = PurchaseOrder::factory()->create([
            'store_id' => $store->id,
            'vendor_id' => $vendor->id,
            'user_id' => $user->id,
        ]);

        $this->assertEquals($store->id, $po->store_id);
        $this->assertEquals($vendor->id, $po->vendor_id);
    }
}
