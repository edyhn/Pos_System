<?php

namespace Database\Factories;

use App\Models\Store;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $subtotal = fake()->numberBetween(10000, 500000);
        $tax = (int) ($subtotal * 0.11);
        return [
            'store_id' => Store::factory(),
            'user_id' => User::factory(),
            'invoice_number' => 'INV-' . date('Ymd') . '-' . str_pad(fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'customer_name' => fake()->optional()->name(),
            'subtotal' => $subtotal,
            'tax_amount' => $tax,
            'total_amount' => $subtotal + $tax,
            'payment_amount' => $subtotal + $tax,
            'change_amount' => 0,
            'payment_method' => fake()->randomElement(['cash', 'transfer', 'qris']),
            'status' => 'completed',
        ];
    }
}
