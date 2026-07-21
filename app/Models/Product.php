<?php

namespace App\Models;

use App\Support\StorefrontCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        // Invalidate storefront/chatbot/search caches when a product changes.
        $flush = fn () => StorefrontCache::forget(StorefrontCache::CATALOG_KEYS);
        static::saved($flush);
        static::deleted($flush);
        static::restored($flush);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'price',
        'stock', 'is_available', 'image', 'is_featured', 'rating_avg', 'rating_count',
    ];

    /** Mirror the column default so a freshly built model is already sellable. */
    protected $attributes = [
        'is_available' => true,
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_available' => 'boolean',
            'rating_avg' => 'float',
            'rating_count' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * A product is sellable when the admin has it switched on AND there is
     * stock behind it. The manual switch wins, so the shop can take something
     * off the menu without having to zero the counter.
     */
    public function isOutOfStock(): bool
    {
        // Only an explicit `false` counts as switched off, so a query that did
        // not select the column never makes a product look unavailable.
        return $this->is_available === false || $this->stock <= 0;
    }

    public function isLowStock(): bool
    {
        return ! $this->isOutOfStock() && $this->stock < 5;
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('is_available', true)->where('stock', '>', 0);
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->where('is_available', true)
            ->where('stock', '>', 0)
            ->where('stock', '<', 5);
    }

    public function scopeInCategoryActive(Builder $query): Builder
    {
        return $query->whereHas('category', function ($q) {
            $q->where('is_active', true);
        });
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp '.number_format((float) $this->price, 0, ',', '.');
    }

    public function updateRating(): void
    {
        $this->rating_avg = $this->reviews()->where('is_approved', true)->avg('rating') ?? 0;
        $this->rating_count = $this->reviews()->where('is_approved', true)->count();
        $this->save();
    }
}
