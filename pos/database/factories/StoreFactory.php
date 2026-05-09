<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreFactory extends Factory
{
    protected $model = Store::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'slug' => fake()->slug(),
            'code' => strtoupper(fake()->lexify('???')),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'receipt_footer' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
