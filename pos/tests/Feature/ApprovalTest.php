<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_access_receipt_approval(): void
    {
        $store = Store::factory()->create();
        $user = User::factory()->owner()->create(['store_id' => $store->id]);
        $this->actingAs($user);

        $this->get('/approvals/receipt')->assertOk();
    }

    public function test_owner_can_access_refund_approval(): void
    {
        $store = Store::factory()->create();
        $user = User::factory()->owner()->create(['store_id' => $store->id]);
        $this->actingAs($user);

        $this->get('/approvals/refund')->assertOk();
    }

    public function test_cashier_cannot_access_approvals(): void
    {
        $store = Store::factory()->create();
        $user = User::factory()->cashier()->create(['store_id' => $store->id]);
        $this->actingAs($user);

        $this->get('/approvals/receipt')->assertForbidden();
        $this->get('/approvals/refund')->assertForbidden();
    }

    public function test_owner_can_access_settings(): void
    {
        $store = Store::factory()->create();
        $user = User::factory()->owner()->create(['store_id' => $store->id]);
        $this->actingAs($user);

        $this->get('/settings/store')->assertOk();
    }

    public function test_cashier_cannot_access_settings(): void
    {
        $store = Store::factory()->create();
        $user = User::factory()->cashier()->create(['store_id' => $store->id]);
        $this->actingAs($user);

        $this->get('/settings/store')->assertForbidden();
    }

    public function test_owner_can_access_activity_logs(): void
    {
        $store = Store::factory()->create();
        $user = User::factory()->owner()->create(['store_id' => $store->id]);
        $this->actingAs($user);

        $this->get('/activity-logs')->assertOk();
    }

    public function test_cashier_cannot_access_activity_logs(): void
    {
        $store = Store::factory()->create();
        $user = User::factory()->cashier()->create(['store_id' => $store->id]);
        $this->actingAs($user);

        $this->get('/activity-logs')->assertForbidden();
    }
}
