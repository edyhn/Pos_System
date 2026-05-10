<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionItemFactory extends Factory
{
    protected $model = TransactionItem::class;

    public function definition(): array
    {
        $price = fake()->numberBetween(1000, 100000);
        $qty = fake()->numberBetween(1, 5);
        return [
            'transaction_id' => Transaction::factory(),
            'product_id' => Product::factory(),
            'product_name' => fake()->words(3, true),
            'quantity' => $qty,
            'price' => $price,
            'subtotal' => $price * $qty,
            'is_taxed' => false,
        ];
    }
}
