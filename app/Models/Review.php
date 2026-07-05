<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        $flush = fn () => \App\Support\StorefrontCache::forget(\App\Support\StorefrontCache::REVIEW_KEYS);
        static::saved($flush);
        static::deleted($flush);
        static::restored($flush);
    }

    protected $fillable = [
        'user_id', 'product_id', 'order_id',
        'rating', 'comment', 'image', 'is_approved',
    ];

    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
