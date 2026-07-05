<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\OrderItem;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $days = 30;

        // Heavy aggregate metrics are recomputed at most once per minute; the
        // dashboard is an overview so slightly stale numbers are acceptable.
        $metrics = Cache::remember('admin.dashboard.metrics', 60, function () use ($days) {
            return [
                'totalSales' => Order::where('payment_status', 'paid')->sum('total'),
                'totalUsers' => User::where('role', 'user')->count(),
                'totalOrders' => Order::count(),
                'pendingOrders' => Order::whereIn('status', ['pending', 'awaiting_payment'])->count(),
                'revenueData' => Order::where('payment_status', 'paid')
                    ->where('created_at', '>=', now()->subDays($days))
                    ->selectRaw('DATE(created_at) as date, SUM(total) as revenue')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get()
                    ->pluck('revenue', 'date')
                    ->toArray(),
                'bestSellers' => OrderItem::query()
                    ->whereHas('order', fn($q) => $q->where('payment_status', 'paid'))
                    ->selectRaw('product_name, SUM(quantity) as total_qty')
                    ->groupBy('product_id', 'product_name')
                    ->orderByDesc('total_qty')
                    ->take(5)
                    ->get()
                    ->map(fn($r) => ['name' => $r->product_name, 'qty' => (int) $r->total_qty])
                    ->all(),
            ];
        });

        $totalSales = $metrics['totalSales'];
        $totalUsers = $metrics['totalUsers'];
        $totalOrders = $metrics['totalOrders'];
        $pendingOrders = $metrics['pendingOrders'];

        // Build chart labels/data from cached revenue map (cheap, keeps dates current).
        $revenueData = $metrics['revenueData'];
        $chartLabels = [];
        $chartData = [];
        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartLabels[] = now()->subDays($i)->format('d M');
            $chartData[] = (float) ($revenueData[$date] ?? 0);
        }

        $bestSellerLabels = array_column($metrics['bestSellers'], 'name');
        $bestSellerData = array_column($metrics['bestSellers'], 'qty');

        // Kept live for freshness (cheap queries; admins expect current values).
        $lowStockProducts = Product::lowStock()->with('category')->get();
        $recentOrders = Order::with('user')->latest()->take(5)->get();
        $recentReviews = Review::with(['user', 'product'])->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalSales', 'totalUsers', 'totalOrders',
            'pendingOrders', 'lowStockProducts', 'recentOrders',
            'chartLabels', 'chartData',
            'bestSellerLabels', 'bestSellerData',
            'recentReviews'
        ));
    }

    public function storeSettings()
    {
        return view('admin.settings.store');
    }

    public function contentSettings()
    {
        return view('admin.settings.content');
    }
}
