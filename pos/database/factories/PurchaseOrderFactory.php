<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\Store;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'vendor_id' => Vendor::factory(),
            'user_id' => User::factory(),
            'po_number' => 'PO-' . strtoupper(fake()->bothify('####')),
            'status' => 'draft',
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function sent(): static
    {
        return $this->state(fn (array $attrs) => ['status' => 'sent']);
    }

    public function received(): static
    {
        return $this->state(fn (array $attrs) => ['status' => 'received']);
    }
}
