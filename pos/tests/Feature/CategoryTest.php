<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_category_list(): void
    {
        $user = User::factory()->owner()->create(['store_id' => null]);
        $this->actingAs($user);

        $this->get('/categories')->assertOk();
    }

    public function test_category_belongs_to_store(): void
    {
        $store1 = Store::factory()->create();
        $store2 = Store::factory()->create();

        Category::factory()->create(['store_id' => $store1->id]);
        Category::factory()->create(['store_id' => $store2->id]);

        $this->assertCount(1, Category::where('store_id', $store1->id)->get());
        $this->assertCount(1, Category::where('store_id', $store2->id)->get());
    }

    public function test_category_can_be_toggled_active(): void
    {
        $store = Store::factory()->create();
        $category = Category::factory()->create(['store_id' => $store->id, 'is_active' => true]);

        $this->assertTrue($category->is_active);

        $category->update(['is_active' => false]);
        $this->assertFalse($category->is_active);
    }

    public function test_cashier_cannot_access_categories(): void
    {
        $store = Store::factory()->create();
        $user = User::factory()->cashier()->create(['store_id' => $store->id]);
        $this->actingAs($user);

        $this->get('/categories')->assertForbidden();
    }
}
