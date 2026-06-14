<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

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
            $query->where(function($query) use ($q) {
                $query->whereHas('product', function($query) use ($q) {
                    $query->where('name', 'LIKE', "%$q%");
                })->orWhereHas('user', function($query) use ($q) {
                    $query->where('name', 'LIKE', "%$q%");
                });
            });
        }

        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        if ($request->filled('is_approved')) {
            $query->where('is_approved', $request->is_approved === '1');
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($sort === 'created_at') {
            $query->orderBy('created_at', $direction);
        } else {
            $query->orderBy($sort, $direction);
        }

        // 3. Global Statistics (Unfiltered)
        $totalReviews = Review::count();
        $pendingReviews = Review::where('is_approved', false)->count();
        $avgRating = Review::avg('rating') ?? 0;
        $photoReviews = Review::whereNotNull('image')->count();

        $reviews = $query->paginate(15)->withQueryString();

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
        $review->delete();
        $review->product->updateRating();
        return back()->with('success', 'Ulasan dihapus.');
    }
}
