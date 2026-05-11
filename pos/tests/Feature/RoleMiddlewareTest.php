<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_access_route_with_comma_separated_roles(): void
    {
        $store = Store::factory()->create();
        $owner = User::factory()->owner()->create(['store_id' => $store->id]);
        $this->actingAs($owner);

        $this->get('/transactions')->assertOk();
        $this->get('/subscriptions/check')->assertOk();
    }

    public function test_cashier_can_access_route_with_comma_separated_roles(): void
    {
        $store = Store::factory()->create();
        $cashier = User::factory()->cashier()->create(['store_id' => $store->id]);
        $this->actingAs($cashier);

        $this->get('/transactions')->assertOk();
        $this->get('/subscriptions/check')->assertOk();
    }

    public function test_inactive_user_cannot_access_owner_routes(): void
    {
        $store = Store::factory()->create();
        $user = User::factory()->cashier()->inactive()->create(['store_id' => $store->id]);
        $this->actingAs($user);

        $this->get('/products')->assertForbidden();
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get('/transactions');
        $response->assertRedirect('/login');
    }

    public function test_cashier_cannot_access_owner_only_route_with_comma_separated(): void
    {
        $store = Store::factory()->create();
        $cashier = User::factory()->cashier()->create(['store_id' => $store->id]);
        $this->actingAs($cashier);

        $this->get('/approvals/receipt')->assertForbidden();
        $this->get('/approvals/refund')->assertForbidden();
    }

    public function test_api_returns_json_for_unauthenticated(): void
    {
        $response = $this->getJson('/api/products');
        $response->assertUnauthorized();
    }
}
