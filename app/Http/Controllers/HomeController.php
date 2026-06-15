<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::active()->withCount('products')->take(6)->get();
        $featuredProducts = Product::with('category')
            ->inCategoryActive()
            ->featured()
            ->latest()
            ->take(4)
            ->get();

        return view('home', compact('categories', 'featuredProducts'));
    }
}
