<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_login(): void
    {
        Store::factory()->create();
        $user = User::factory()->owner()->create(['password' => bcrypt('password'), 'store_id' => null]);

        $response = $this->post('/login', [
            'user_id' => $user->user_id,
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_cashier_can_login(): void
    {
        $store = Store::factory()->create();
        $user = User::factory()->cashier()->create([
            'password' => bcrypt('password'),
            'store_id' => $store->id,
        ]);

        $response = $this->post('/login', [
            'user_id' => $user->user_id,
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_login_with_invalid_credentials(): void
    {
        Store::factory()->create();
        $user = User::factory()->owner()->create(['password' => bcrypt('password'), 'store_id' => null]);

        $response = $this->post('/login', [
            'user_id' => $user->user_id,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_owner_can_access_owner_routes(): void
    {
        $store = Store::factory()->create();
        $user = User::factory()->owner()->create(['store_id' => null]);
        $this->actingAs($user);

        $this->get('/products')->assertOk();
        $this->get('/categories')->assertOk();
        $this->get('/vendors')->assertOk();
        $this->get('/users')->assertOk();
        $this->get('/purchase-orders')->assertOk();
        $this->get('/stock')->assertOk();
        $this->get('/stock-movement/in')->assertOk();
        $this->get('/stock-movement/out')->assertOk();
        $this->get('/reports/sales')->assertOk();
        $this->get('/reports/tax')->assertOk();
        $this->get('/approvals/receipt')->assertOk();
        $this->get('/approvals/refund')->assertOk();
        $this->get('/settings/store')->assertOk();
    }

    public function test_owner_can_access_activity_logs(): void
    {
        Store::factory()->create();
        $user = User::factory()->owner()->create(['store_id' => null]);
        $this->actingAs($user);
        $this->get('/activity-logs')->assertOk();
    }

    public function test_cashier_cannot_access_owner_routes(): void
    {
        $store = Store::factory()->create();
        $user = User::factory()->cashier()->create(['store_id' => $store->id]);
        $this->actingAs($user);

        $this->get('/products')->assertForbidden();
        $this->get('/categories')->assertForbidden();
        $this->get('/vendors')->assertForbidden();
        $this->get('/users')->assertForbidden();
        $this->get('/purchase-orders')->assertForbidden();
        $this->get('/stock')->assertForbidden();
        $this->get('/reports/sales')->assertForbidden();
        $this->get('/approvals/receipt')->assertForbidden();
        $this->get('/settings/store')->assertForbidden();
    }

    public function test_cashier_can_access_cashier_routes(): void
    {
        $store = Store::factory()->create();
        $user = User::factory()->cashier()->create(['store_id' => $store->id]);
        $this->actingAs($user);

        $this->get('/cashier')->assertOk();
        $this->get('/requests/receipt')->assertOk();
        $this->get('/requests/refund')->assertOk();
        $this->get('/transactions')->assertOk();
    }

    public function test_logout(): void
    {
        $user = User::factory()->owner()->create(['store_id' => null]);
        $this->actingAs($user);

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_guest_redirected_to_login(): void
    {
        $this->get('/products')->assertRedirect('/login');
        $this->get('/dashboard')->assertRedirect('/login');
    }
}
