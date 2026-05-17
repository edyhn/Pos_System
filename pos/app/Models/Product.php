<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'store_id', 'category_id', 'vendor_id', 'name', 'slug', 'sku', 'barcode',
        'price', 'cost_price', 'stock', 'min_stock', 'unit',
        'is_taxed', 'tax_rate', 'is_subscription', 'subscription_days',
        'description', 'image', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_taxed' => 'boolean',
            'is_subscription' => 'boolean',
            'is_active' => 'boolean',
            'price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'tax_rate' => 'decimal:2',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function poItems(): HasMany
    {
        return $this->hasMany(PoItem::class);
    }

    public function stockOpnameItems(): HasMany
    {
        return $this->hasMany(StockOpnameItem::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function scopeByStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function isLowStock(): bool
    {
        return $this->stock <= 0 || ($this->min_stock > 0 && $this->stock <= $this->min_stock);
    }
}
