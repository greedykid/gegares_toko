<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');

        // Allowed sort columns
        $allowedSorts = ['code', 'value', 'usage_limit', 'is_active', 'created_at'];
        if (! in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }

        // Allowed direction
        if (! in_array($direction, ['asc', 'desc'])) {
            $direction = 'desc';
        }

        $query = Coupon::query();

        $query->when($request->search, fn ($q, $s) => $q->where('code', 'like', '%'.$s.'%'));
        $query->when($request->filled('is_active'), fn ($q) => $q->where('is_active', (int) $request->is_active));

        if ($sort === 'created_at') {
            $query->orderBy('created_at', $direction);
        } else {
            $query->orderBy($sort, $direction);
        }

        $coupons = $query->paginate(10)->appends($request->except('partial'));

        if ($request->boolean('partial')) {
            return view('admin.coupons._table', compact('coupons'));
        }

        return view('admin.coupons.index', compact('coupons'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons',
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = $request->has('is_active');
        $validated['min_purchase'] = $validated['min_purchase'] ?? 0;

        Coupon::create($validated);

        return redirect()->back()->with('success', 'Kupon berhasil dibuat!');
    }

    public function update(Request $request, Coupon $coupon)
    {
        $validated = $request->validate([
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['min_purchase'] = $validated['min_purchase'] ?? 0;

        $coupon->update($validated);

        return redirect()->back()->with('success', 'Kupon berhasil diupdate!');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return redirect()->back()->with('success', 'Kupon berhasil dihapus!');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:coupons,id',
        ])['ids'];

        $count = Coupon::whereIn('id', $ids)->count();
        Coupon::whereIn('id', $ids)->delete();

        return back()->with('success', "{$count} kupon berhasil dihapus.");
    }
}
