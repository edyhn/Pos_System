<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'transaction_id', 'product_id', 'product_name',
        'quantity', 'price', 'subtotal', 'is_taxed',
    ];

    protected function casts(): array
    {
        return [
            'is_taxed' => 'boolean',
            'price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
