<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Central registry of cached storefront/admin keys and helpers to invalidate
 * them, so model changes reflect immediately instead of waiting for TTL.
 */
class StorefrontCache
{
    /** Derived from products & categories (homepage, chatbot catalog, search). */
    public const CATALOG_KEYS = [
        'home.categories.ids',
        'home.featured.ids',
        'chatbot.catalog',
        'chatbot.whitelist',
        'chatbot.bestsellers',
        'products.for_matching',
        'search.fuzzy.products',
        'search.fuzzy.categories',
    ];

    /** Derived from orders (admin dashboard metrics + best sellers). */
    public const ORDER_KEYS = [
        'admin.dashboard.metrics',
        'chatbot.bestsellers',
    ];

    /** Derived from reviews (moderation stats + catalog ratings). */
    public const REVIEW_KEYS = [
        'admin.reviews.stats',
        'chatbot.catalog',
    ];

    public static function forget(array $keys): void
    {
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }
}
