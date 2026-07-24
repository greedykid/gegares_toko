<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');

        // Allowed sort columns
        $allowedSorts = ['name', 'description', 'category_id', 'price', 'stock', 'is_featured', 'created_at'];
        if (! in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }

        // Allowed direction
        if (! in_array($direction, ['asc', 'desc'])) {
            $direction = 'desc';
        }

        // How many units are still on the shelf but already spoken for. Stock is
        // reserved at checkout, so the `stock` column is what is still sellable,
        // not what is in the warehouse; without this number an admin doing a
        // stocktake would type the physical count into a field that means
        // something else and promise the reserved units twice.
        //
        // Only orders whose goods have not left count: 'shipped' and 'completed'
        // carry the marker too — they took stock out and never give it back —
        // but those units are physically gone, so counting them would overstate
        // the warehouse. Same rule cancelAndRelease() uses to decide a restock.
        $query = Product::with(['category', 'images', 'variants'])
            ->withSum([
                'orderItems as reserved_quantity' => fn ($q) => $q->whereHas(
                    'order',
                    fn ($o) => $o->whereNotNull('stock_reserved_at')
                        ->whereIn('status', ['pending', 'processing'])
                ),
            ], 'quantity');

        // Filtering
        $query->when($request->search, function ($q, $search) {
            return $q->where('name', 'like', '%'.$search.'%');
        });

        $query->when($request->category, function ($q, $cat) {
            return $q->where('category_id', $cat);
        });

        // "Habis" covers both a zero counter and a product switched off manually.
        $query->when($request->stock_status, function ($q, $status) {
            if ($status == 'out_of_stock') {
                return $q->where(fn ($sub) => $sub->where('is_available', false)->orWhere('stock', '<=', 0));
            }
            if ($status == 'low_stock') {
                return $q->lowStock();
            }
            if ($status == 'in_stock') {
                return $q->where('is_available', true)->where('stock', '>=', 5);
            }
        });

        $query->when($request->is_featured !== null && $request->is_featured !== '', function ($q) use ($request) {
            return $q->where('is_featured', $request->is_featured);
        });

        if ($sort === 'created_at') {
            $query->orderBy('created_at', $direction);
        } else {
            $query->orderBy($sort, $direction);
        }

        // Exclude `partial` so pagination links never point at the AJAX fragment.
        $products = $query->paginate(15)->appends($request->except('partial'));
        $categories = Category::all();

        $productStats = Product::selectRaw('
            count(*) as total,
            sum(case when is_featured = 1 then 1 else 0 end) as featured,
            sum(case when is_available = 0 or stock <= 0 then 1 else 0 end) as out_of_stock,
            sum(case when is_available = 1 and stock > 0 and stock < 5 then 1 else 0 end) as low_stock
        ')->first();

        $totalProducts = $productStats->total ?? 0;
        $featuredProducts = $productStats->featured ?? 0;
        $outOfStock = $productStats->out_of_stock ?? 0;
        $lowStock = $productStats->low_stock ?? 0;

        // Live search / filter swaps just the table via AJAX.
        if ($request->boolean('partial')) {
            return view('admin.products._table', compact('products'));
        }

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
            'image' => 'nullable|image|max:10240',
            'gallery' => 'nullable|array',
            'gallery.*' => 'nullable|image|max:10240',
            'is_featured' => 'boolean',
            'variants' => 'nullable|array',
            'variants.*.name' => 'required_with:variants|string|max:255',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'required_with:variants|integer|min:0',
        ]);

        $data['slug'] = Str::slug($data['name']);
        $data['is_featured'] = $request->boolean('is_featured');

        if ($request->hasFile('image')) {
            $data['image'] = app(ImageOptimizer::class)->store($request->file('image'), 'products');
        }

        $product = Product::create($data);

        if ($request->filled('variants')) {
            foreach ($request->variants as $variant) {
                if (! empty($variant['name'])) {
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
                if ($index >= 6) {
                    break;
                } // Max 6 gallery
                $path = app(ImageOptimizer::class)->store($file, 'products/gallery');
                $product->images()->create([
                    'image_path' => $path,
                    'sort_order' => $index,
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
            'image' => 'nullable|image|max:10240',
            'remove_image' => 'nullable|boolean',
            'gallery' => 'nullable|array',
            'gallery.*' => 'nullable|image|max:10240',
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
            $data['image'] = app(ImageOptimizer::class)->store($request->file('image'), 'products');
        } elseif ($request->boolean('remove_image')) {
            $data['image'] = null;
        }

        $product->update($data);

        // Handle removals
        if ($request->filled('removed_gallery_ids')) {
            ProductImage::whereIn('id', $request->removed_gallery_ids)
                ->where('product_id', $product->id)
                ->delete();
        }

        if ($request->filled('removed_variant_ids')) {
            $product->variants()->whereIn('id', $request->removed_variant_ids)->delete();
        }

        // Handle variant updates and creation
        if ($request->filled('variants')) {
            foreach ($request->variants as $variant) {
                if (! empty($variant['name'])) {
                    if (! empty($variant['id'])) {
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
                    if ($index >= $maxAllowedNew) {
                        break;
                    }
                    $path = app(ImageOptimizer::class)->store($file, 'products/gallery');
                    $product->images()->create([
                        'image_path' => $path,
                        'sort_order' => $maxSortOrder + 1 + $index,
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

    /**
     * Delete several products at once from the table's multi-select bar.
     */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:products,id',
        ])['ids'];

        $count = Product::whereIn('id', $ids)->count();
        Product::whereIn('id', $ids)->delete();

        return back()->with('success', "{$count} produk berhasil dihapus.");
    }

    /**
     * Stream all products as a CSV the admin can edit and re-import.
     */
    public function export()
    {
        $columns = ['name', 'slug', 'category', 'price', 'stock', 'is_available', 'is_featured', 'description'];
        $filename = 'produk-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($columns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columns);
            Product::with('category')->orderBy('name')->chunk(200, function ($products) use ($out) {
                foreach ($products as $p) {
                    fputcsv($out, [
                        $p->name,
                        $p->slug,
                        $p->category?->name,
                        $p->price,
                        $p->stock,
                        $p->is_available ? 1 : 0,
                        $p->is_featured ? 1 : 0,
                        $p->description,
                    ]);
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * Import products from a CSV exported above. Rows are matched by slug
     * (updated) or created; a valid existing category name is required.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ], [], ['file' => 'berkas CSV']);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        if (! $handle) {
            return back()->with('error', 'Gagal membaca berkas CSV.');
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);
            return back()->with('error', 'Berkas CSV kosong.');
        }
        $header = array_map(fn ($h) => strtolower(trim($h)), $header);

        $categories = Category::pluck('id', 'name');
        $created = 0;
        $updated = 0;
        $skipped = 0;

        DB::transaction(function () use ($handle, $header, $categories, &$created, &$updated, &$skipped) {
            while (($row = fgetcsv($handle)) !== false) {
                if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                    continue; // blank line
                }
                $data = array_combine($header, array_pad($row, count($header), null));

                $name = trim((string) ($data['name'] ?? ''));
                $categoryId = $categories[trim((string) ($data['category'] ?? ''))] ?? null;
                if ($name === '' || ! $categoryId) {
                    $skipped++;
                    continue;
                }

                $slug = Str::slug(trim((string) ($data['slug'] ?? '')) ?: $name);
                $attrs = [
                    'name' => $name,
                    'category_id' => $categoryId,
                    'price' => (int) round((float) ($data['price'] ?? 0)),
                    'stock' => max(0, (int) ($data['stock'] ?? 0)),
                    'is_available' => (int) ($data['is_available'] ?? 1) === 1,
                    'is_featured' => (int) ($data['is_featured'] ?? 0) === 1,
                    'description' => trim((string) ($data['description'] ?? '')) ?: null,
                ];

                $product = Product::where('slug', $slug)->first();
                if ($product) {
                    $product->update($attrs);
                    $updated++;
                } else {
                    Product::create($attrs + ['slug' => $slug]);
                    $created++;
                }
            }
        });
        fclose($handle);

        return back()->with('success', "Impor selesai — {$created} dibuat, {$updated} diperbarui, {$skipped} dilewati.");
    }

    public function toggleFeatured(Product $product)
    {
        $product->update(['is_featured' => ! $product->is_featured]);

        return back()->with('success', 'Status produk unggulan berhasil diubah.');
    }

    /**
     * Flip a product between "Tersedia" and "Habis" without touching its stock
     * count, so the counter stays intact for when it goes back on the menu.
     */
    public function toggleAvailability(Product $product)
    {
        $product->update(['is_available' => ! $product->is_available]);

        return back()->with(
            'success',
            $product->is_available
                ? 'Produk ditandai TERSEDIA.'
                : 'Produk ditandai HABIS dan tidak bisa dipesan pelanggan.'
        );
    }
}
