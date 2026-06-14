<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\OrderItem;
use App\Models\Review;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalSales = Order::where('payment_status', 'paid')->sum('total');
        $totalUsers = User::where('role', 'user')->count();
        $totalOrders = Order::count();
        $pendingOrders = Order::whereIn('status', ['pending', 'awaiting_payment'])->count();
        $lowStockProducts = Product::lowStock()->with('category')->get();
        $recentOrders = Order::with('user')->latest()->take(5)->get();
        $recentReviews = Review::with(['user', 'product'])->latest()->take(5)->get();

        // Revenue Chart Data (Last 30 Days)
        $days = 30;
        $revenueData = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, SUM(total) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('revenue', 'date')
            ->toArray();

        $chartLabels = [];
        $chartData = [];

        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartLabels[] = now()->subDays($i)->format('d M');
            $chartData[] = (float) ($revenueData[$date] ?? 0);
        }

        // Best Sellers (Top 5)
        $bestSellers = OrderItem::query()
            ->whereHas('order', function($q) {
                $q->where('payment_status', 'paid');
            })
            ->selectRaw('product_name, SUM(quantity) as total_qty')
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        $bestSellerLabels = $bestSellers->pluck('product_name')->toArray();
        $bestSellerData = $bestSellers->pluck('total_qty')->map(fn($val) => (int) $val)->toArray();

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
}
