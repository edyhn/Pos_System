<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'store_id', 'transaction_id', 'product_id',
        'customer_identifier', 'start_date', 'end_date', 'status',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('end_date', '>=', today());
    }

    public function scopeExpired($query)
    {
        return $query->where(function ($q) {
            $q->where('end_date', '<', today())->orWhere('status', 'expired');
        });
    }
}
