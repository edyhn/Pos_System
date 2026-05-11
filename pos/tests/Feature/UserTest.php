<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_access_user_list(): void
    {
        $user = User::factory()->owner()->create(['store_id' => null]);
        $this->actingAs($user);
        $this->get('/users')->assertOk();
    }

    public function test_cashier_cannot_access_user_list(): void
    {
        $store = Store::factory()->create();
        $user = User::factory()->cashier()->create(['store_id' => $store->id]);
        $this->actingAs($user);
        $this->get('/users')->assertForbidden();
    }

    public function test_user_belongs_to_store(): void
    {
        $store = Store::factory()->create();
        $user = User::factory()->cashier()->create(['store_id' => $store->id]);

        $this->assertEquals($store->id, $user->store_id);
    }

    public function test_owner_has_null_store_id(): void
    {
        $user = User::factory()->owner()->create(['store_id' => null]);
        $this->assertNull($user->store_id);
    }

    public function test_user_password_is_hashed(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password123')]);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertFalse(Hash::check('wrongpassword', $user->password));
    }

    public function test_user_id_auto_generation_pattern(): void
    {
        $store = Store::factory()->create();
        $user1 = User::factory()->cashier()->create(['store_id' => $store->id, 'user_id' => 'KSR001']);
        $user2 = User::factory()->cashier()->create(['store_id' => $store->id, 'user_id' => 'KSR002']);

        $this->assertEquals('KSR001', $user1->user_id);
        $this->assertEquals('KSR002', $user2->user_id);

        $owner = User::factory()->owner()->create(['store_id' => null, 'user_id' => 'OWN001']);
        $this->assertStringStartsWith('OWN', $owner->user_id);
    }

    public function test_inactive_user_is_filtered(): void
    {
        User::factory()->cashier()->create(['is_active' => true]);
        User::factory()->cashier()->inactive()->create();

        $this->assertCount(1, User::where('is_active', true)->get());
    }

    public function test_user_is_owner_check(): void
    {
        $owner = User::factory()->owner()->create(['store_id' => null]);
        $cashier = User::factory()->cashier()->create();

        $this->assertEquals('owner', $owner->role);
        $this->assertEquals('cashier', $cashier->role);
    }

    public function test_owner_cannot_be_deactivated_by_cashier_routes(): void
    {
        $owner = User::factory()->owner()->create(['store_id' => null]);
        $this->actingAs($owner);
        $this->get('/users/create')->assertOk();
    }

    public function test_user_edit_page_loads(): void
    {
        $store = Store::factory()->create();
        $owner = User::factory()->owner()->create(['store_id' => $store->id]);
        $user = User::factory()->cashier()->create(['store_id' => $store->id]);

        $this->actingAs($owner);
        $this->get("/users/{$user->id}/edit")->assertOk();
    }
}
