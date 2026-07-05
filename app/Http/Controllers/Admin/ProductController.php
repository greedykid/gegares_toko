<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');

        // Allowed sort columns
        $allowedSorts = ['name', 'description', 'category_id', 'price', 'stock', 'is_featured', 'created_at'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }

        // Allowed direction
        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'desc';
        }

        $query = Product::with(['category', 'images', 'variants']);

        // Filtering
        $query->when($request->search, function($q, $search) {
            return $q->where('name', 'like', '%'.$search.'%');
        });

        $query->when($request->category, function($q, $cat) {
            return $q->where('category_id', $cat);
        });

        $query->when($request->stock_status, function($q, $status) {
            if ($status == 'out_of_stock') return $q->where('stock', 0);
            if ($status == 'low_stock') return $q->where('stock', '>', 0)->where('stock', '<', 5);
            if ($status == 'in_stock') return $q->where('stock', '>=', 5);
        });

        $query->when($request->is_featured !== null && $request->is_featured !== '', function($q) use ($request) {
            return $q->where('is_featured', $request->is_featured);
        });

        if ($sort === 'created_at') {
            $query->orderBy('created_at', $direction);
        } else {
            $query->orderBy($sort, $direction);
        }

        $products = $query->paginate(15)->appends($request->query());
        $categories = Category::all();
        
        $productStats = Product::selectRaw("
            count(*) as total,
            sum(case when is_featured = 1 then 1 else 0 end) as featured,
            sum(case when stock = 0 then 1 else 0 end) as out_of_stock,
            sum(case when stock > 0 and stock < 5 then 1 else 0 end) as low_stock
        ")->first();

        $totalProducts = $productStats->total ?? 0;
        $featuredProducts = $productStats->featured ?? 0;
        $outOfStock = $productStats->out_of_stock ?? 0;
        $lowStock = $productStats->low_stock ?? 0;

        return view('admin.products.index', compact(
            'products', 
            'categories', 
            'totalProducts', 
            'featuredProducts', 
            'outOfStock', 
            'lowStock'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|max:2048',
            'gallery' => 'nullable|array',
            'gallery.*' => 'nullable|image|max:2048',
            'is_featured' => 'boolean',
            'variants' => 'nullable|array',
            'variants.*.name' => 'required_with:variants|string|max:255',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'required_with:variants|integer|min:0',
        ]);

        $data['slug'] = Str::slug($data['name']);
        $data['is_featured'] = $request->boolean('is_featured');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);

        if ($request->filled('variants')) {
            foreach ($request->variants as $variant) {
                if (!empty($variant['name'])) {
                    $product->variants()->create([
                        'name' => $variant['name'],
                        'price' => $variant['price'] ?? null,
                        'stock' => $variant['stock'] ?? 0,
                    ]);
                }
            }
        }

        if ($request->hasFile('gallery')) {
            $uploadedFiles = array_values(array_filter($request->file('gallery')));
            foreach ($uploadedFiles as $index => $file) {
                if ($index >= 6) break; // Max 6 gallery
                $path = $file->store('products/gallery', 'public');
                $product->images()->create([
                    'image_path' => $path,
                    'sort_order' => $index
                ]);
            }
        }

        return back()->with('success', 'Produk berhasil ditambahkan.');
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|max:2048',
            'remove_image' => 'nullable|boolean',
            'gallery' => 'nullable|array',
            'gallery.*' => 'nullable|image|max:2048',
            'removed_gallery_ids' => 'nullable|array',
            'removed_gallery_ids.*' => 'exists:product_images,id',
            'is_featured' => 'boolean',
            'variants' => 'nullable|array',
            'variants.*.id' => 'nullable|exists:product_variants,id',
            'variants.*.name' => 'required_with:variants|string|max:255',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'required_with:variants|integer|min:0',
            'removed_variant_ids' => 'nullable|array',
            'removed_variant_ids.*' => 'exists:product_variants,id',
        ]);

        $data['slug'] = Str::slug($data['name']);
        $data['is_featured'] = $request->boolean('is_featured');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        } elseif ($request->boolean('remove_image')) {
            $data['image'] = null;
        }

        $product->update($data);

        // Handle removals
        if ($request->filled('removed_gallery_ids')) {
            \App\Models\ProductImage::whereIn('id', $request->removed_gallery_ids)
                ->where('product_id', $product->id)
                ->delete();
        }

        if ($request->filled('removed_variant_ids')) {
            $product->variants()->whereIn('id', $request->removed_variant_ids)->delete();
        }

        // Handle variant updates and creation
        if ($request->filled('variants')) {
            foreach ($request->variants as $variant) {
                if (!empty($variant['name'])) {
                    if (!empty($variant['id'])) {
                        // Update
                        $product->variants()->where('id', $variant['id'])->update([
                            'name' => $variant['name'],
                            'price' => $variant['price'] ?? null,
                            'stock' => $variant['stock'] ?? 0,
                        ]);
                    } else {
                        // Create
                        $product->variants()->create([
                            'name' => $variant['name'],
                            'price' => $variant['price'] ?? null,
                            'stock' => $variant['stock'] ?? 0,
                        ]);
                    }
                }
            }
        }

        // Handle new gallery uploads
        if ($request->hasFile('gallery')) {
            $maxSortOrder = $product->images()->max('sort_order') ?? -1;
            $currentGalleryCount = $product->images()->count();
            $maxAllowedNew = 6 - $currentGalleryCount;

            if ($maxAllowedNew > 0) {
                $uploadedFiles = array_values(array_filter($request->file('gallery')));
                foreach ($uploadedFiles as $index => $file) {
                    if ($index >= $maxAllowedNew) break;
                    $path = $file->store('products/gallery', 'public');
                    $product->images()->create([
                        'image_path' => $path,
                        'sort_order' => $maxSortOrder + 1 + $index
                    ]);
                }
            }
        }

        return back()->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return back()->with('success', 'Produk berhasil dihapus.');
    }

    public function toggleFeatured(Product $product)
    {
        $product->update(['is_featured' => !$product->is_featured]);
        return back()->with('success', 'Status produk unggulan berhasil diubah.');
    }
}
