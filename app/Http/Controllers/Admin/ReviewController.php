<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');

        // Allowed sort columns
        $allowedSorts = ['rating', 'is_approved', 'created_at'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }

        // Allowed direction
        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'desc';
        }

        $query = Review::with(['user', 'product']);

        // 2. Filtering Logic
        if ($request->filled('search')) {
            $q = $request->search;
            $productIds = \App\Models\Product::where('name', 'LIKE', "%$q%")->pluck('id');
            $userIds = \App\Models\User::where('name', 'LIKE', "%$q%")->pluck('id');
            $query->where(function($query) use ($q, $productIds, $userIds) {
                $query->where('comment', 'LIKE', "%$q%")
                      ->orWhereIn('product_id', $productIds)
                      ->orWhereIn('user_id', $userIds);
            });
        }

        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        if ($request->filled('is_approved')) {
            $query->where('is_approved', $request->is_approved === '1');
        }

        // Range on raw timestamp (index-friendly) instead of whereDate().
        if ($request->filled('from_date')) {
            $query->where('created_at', '>=', $request->from_date . ' 00:00:00');
        }

        if ($request->filled('to_date')) {
            $query->where('created_at', '<=', $request->to_date . ' 23:59:59');
        }

        if ($sort === 'created_at') {
            $query->orderBy('created_at', $direction);
        } else {
            $query->orderBy($sort, $direction);
        }

        // 3. Global Statistics (Unfiltered) — cached briefly since they scan the
        // whole table and are shown as an at-a-glance overview.
        $stats = \Illuminate\Support\Facades\Cache::remember('admin.reviews.stats', 60, function () {
            $raw = Review::selectRaw("
                count(*) as total,
                sum(case when is_approved = 0 then 1 else 0 end) as pending,
                avg(rating) as avg_rating,
                sum(case when image is not null then 1 else 0 end) as photo
            ")->first();

            return [
                'total' => $raw->total ?? 0,
                'pending' => $raw->pending ?? 0,
                'avg' => $raw->avg_rating ?? 0,
                'photo' => $raw->photo ?? 0,
            ];
        });
        $totalReviews = $stats['total'];
        $pendingReviews = $stats['pending'];
        $avgRating = $stats['avg'];
        $photoReviews = $stats['photo'];

        $reviews = $query->paginate(15)->appends($request->except('partial'));

        if ($request->boolean('partial')) {
            return view('admin.reviews._table', compact('reviews'));
        }

        return view('admin.reviews.index', compact(
            'reviews',
            'totalReviews',
            'pendingReviews',
            'avgRating',
            'photoReviews'
        ));
    }

    public function update(Request $request, Review $review)
    {
        $review->update(['is_approved' => $request->boolean('is_approved')]);
        $review->product->updateRating();
        return back()->with('success', 'Status ulasan diperbarui.');
    }

    public function destroy(Review $review)
    {
        // Reclaim the uploaded image now rather than leaving an orphan file in
        // storage. The row is soft-deleted (kept for the audit trail); null the
        // column so a later restore doesn't point at a file that's gone.
        if ($review->image) {
            Storage::disk('public')->delete($review->image);
            $review->image = null;
            $review->save();
        }

        $review->delete();
        $review->product->updateRating();

        return back()->with('success', 'Ulasan dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:reviews,id',
        ])['ids'];

        $reviews = Review::whereIn('id', $ids)->get();
        foreach ($reviews as $review) {
            if ($review->image) {
                Storage::disk('public')->delete($review->image);
                $review->image = null;
                $review->save();
            }
            $review->delete();
            $review->product?->updateRating();
        }

        return back()->with('success', $reviews->count().' ulasan dihapus.');
    }
}
