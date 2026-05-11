<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id', 'name', 'type', 'value', 'min_purchase',
        'start_date', 'end_date', 'priority', 'stackable', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_purchase' => 'decimal:2',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'stackable' => 'boolean',
            'is_active' => 'boolean',
            'priority' => 'integer',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_discounts');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    public function scopeByStore($query, int $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function appliesToProduct(Product $product): bool
    {
        if (!$this->is_active || now()->lt($this->start_date) || now()->gt($this->end_date)) {
            return false;
        }

        if ($this->products()->exists()) {
            return $this->products()->where('product_id', $product->id)->exists();
        }

        return true;
    }

    public function calculateDiscount(float $subtotal, int $quantity = 1): float
    {
        return match ($this->type) {
            'percentage' => round($subtotal * ($this->value / 100), 2),
            'fixed' => $this->value * $quantity,
            default => 0,
        };
    }
}
