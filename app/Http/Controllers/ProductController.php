<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::active()->get();

        $query = Product::with('category')->inCategoryActive();

        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
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
            $query->where('name', 'like', '%'.$request->search.'%');
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

        $activeFilters = $this->activeFilters($request, $categories);

        return view('products.index', compact('products', 'categories', 'activeFilters'));
    }

    /**
     * Everything currently shaping the catalogue, each with the URL that drops
     * just that one.
     *
     * @return array<int, array{label: string, removeUrl: string}>
     */
    protected function activeFilters(Request $request, $categories): array
    {
        $rupiah = fn ($v) => 'Rp '.number_format((float) $v, 0, ',', '.');

        $filters = [];

        if ($request->filled('search')) {
            $filters[] = ['key' => 'search', 'label' => 'Pencarian: "'.$request->search.'"'];
        }

        if ($request->filled('category')) {
            $name = $categories->firstWhere('slug', $request->category)?->name ?? $request->category;
            $filters[] = ['key' => 'category', 'label' => 'Kategori: '.$name];
        }

        if ($request->filled('min_price')) {
            $filters[] = ['key' => 'min_price', 'label' => 'Harga dari '.$rupiah($request->min_price)];
        }

        if ($request->filled('max_price')) {
            $filters[] = ['key' => 'max_price', 'label' => 'Harga sampai '.$rupiah($request->max_price)];
        }

        if ($request->filled('rating')) {
            $filters[] = ['key' => 'rating', 'label' => 'Rating '.$request->rating.'+ bintang'];
        }

        // "latest" is the default ordering, so it gets no badge: its × would drop
        // the parameter without changing a single row, which reads as a no-op.
        $sortLabels = [
            'price_low' => 'Harga Terendah',
            'price_high' => 'Harga Tertinggi',
            'popular' => 'Terpopuler',
            'rating' => 'Rating Tertinggi',
        ];

        if ($request->filled('sort') && isset($sortLabels[$request->sort])) {
            $filters[] = ['key' => 'sort', 'label' => 'Urutkan: '.$sortLabels[$request->sort]];
        }

        return array_map(function (array $filter) use ($request) {
            // Drop `page` too, or removing a filter could land on a page that no
            // longer exists. `partial` is an internal scroll-loading flag.
            $filter['removeUrl'] = route('products.index', $request->except([$filter['key'], 'page', 'partial']));
            unset($filter['key']);

            return $filter;
        }, $filters);
    }

    public function show(Product $product)
    {
        if (! $product->category->is_active) {
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
