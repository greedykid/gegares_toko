<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::active()->get();

        $query = Product::with('category')->inCategoryActive();

        if ($request->filled('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->filled('rating')) {
            $query->where('rating_avg', '>=', $request->rating);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $sortBy = $request->get('sort', 'latest');
        $query = match ($sortBy) {
            'price_low' => $query->orderBy('price', 'asc'),
            'price_high' => $query->orderBy('price', 'desc'),
            'popular' => $query->orderBy('rating_count', 'desc'),
            'rating' => $query->orderBy('rating_avg', 'desc'),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();

        // Infinite scroll / "Muat Lebih Banyak": return just the next batch of
        // cards so the browser appends them instead of repainting the page.
        if ($request->boolean('partial')) {
            return response()->json([
                'html' => view('products.partials.grid-items', compact('products'))->render(),
                'next_page_url' => $products->nextPageUrl(),
            ]);
        }

        return view('products.index', compact('products', 'categories'));
    }

    public function show(Product $product)
    {
        if (!$product->category->is_active) {
            abort(404);
        }

        $product->load(['category', 'images', 'variants', 'reviews' => function ($q) {
            $q->where('is_approved', true)->with('user')->latest()->take(10);
        }]);

        $relatedProducts = Product::inCategoryActive()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->inStock()
            ->take(4)
            ->get();

        return view('products.show', compact('product', 'relatedProducts'));
    }
}
