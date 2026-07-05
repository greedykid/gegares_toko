<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        // Homepage data changes infrequently; cache briefly to cut repeated
        // queries on a high-traffic page.
        $categories = Cache::remember('home.categories', 300, fn () =>
            Category::active()->withCount('products')->take(6)->get()
        );

        $featuredProducts = Cache::remember('home.featured', 120, fn () =>
            Product::with('category')
                ->inCategoryActive()
                ->featured()
                ->latest()
                ->take(4)
                ->get()
        );

        return view('home', compact('categories', 'featuredProducts'));
    }
}
