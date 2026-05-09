<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'category_id' => Category::factory(),
            'name' => fake()->words(3, true),
            'slug' => fake()->slug(),
            'sku' => strtoupper(fake()->bothify('SKU-####')),
            'price' => fake()->numberBetween(1000, 500000),
            'cost_price' => fake()->numberBetween(500, 400000),
            'stock' => fake()->numberBetween(0, 100),
            'min_stock' => fake()->numberBetween(1, 10),
            'unit' => 'pcs',
            'is_taxed' => false,
            'tax_rate' => 0,
            'is_subscription' => false,
            'subscription_days' => 30,
            'is_active' => true,
        ];
    }

    public function taxed(): static
    {
        return $this->state(fn (array $attrs) => [
            'is_taxed' => true,
            'tax_rate' => 11,
        ]);
    }

    public function subscription(int $days = 30): static
    {
        return $this->state(fn (array $attrs) => [
            'is_subscription' => true,
            'subscription_days' => $days,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attrs) => [
            'is_active' => false,
        ]);
    }
}
