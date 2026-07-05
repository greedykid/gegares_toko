<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        // Cache only IDs so the payload stays scalar and safe to unserialize
        // across PHP/Laravel upgrades or cache backend changes.
        $categoryIds = Cache::remember('home.categories.ids', 300, fn () =>
            Category::active()
                ->withCount('products')
                ->take(6)
                ->pluck('id')
                ->all()
        );

        $featuredIds = Cache::remember('home.featured.ids', 120, fn () =>
            Product::inCategoryActive()
                ->featured()
                ->latest()
                ->take(4)
                ->pluck('id')
                ->all()
        );

        $categories = Category::active()
            ->withCount('products')
            ->whereIn('id', $categoryIds)
            ->get()
            ->sortBy(fn (Category $category) => array_search($category->id, $categoryIds, true))
            ->values();

        $featuredProducts = Product::with('category')
            ->whereIn('id', $featuredIds)
            ->get()
            ->sortBy(fn (Product $product) => array_search($product->id, $featuredIds, true))
            ->values();

        return view('home', compact('categories', 'featuredProducts'));
    }
}
