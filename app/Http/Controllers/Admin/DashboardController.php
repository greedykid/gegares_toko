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
        // Heavy aggregate metrics are recomputed at most once per minute; the
        // dashboard is an overview so slightly stale numbers are acceptable.
        $metrics = Cache::remember('admin.dashboard.metrics', 60, function () {
            $orderStats = Order::selectRaw("
                count(*) as total_orders,
                sum(case when payment_status = 'paid' then total else 0 end) as total_sales,
                sum(case when status in ('pending', 'awaiting_payment') then 1 else 0 end) as pending_orders
            ")->first();

            return [
                'totalSales' => $orderStats->total_sales ?? 0,
                'totalUsers' => User::where('role', 'user')->count(),
                'totalOrders' => $orderStats->total_orders ?? 0,
                'pendingOrders' => $orderStats->pending_orders ?? 0,
                'revenueSeries' => $this->buildRevenueSeries(),
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
        $revenueSeries = $metrics['revenueSeries'];

        $bestSellerLabels = array_column($metrics['bestSellers'], 'name');
        $bestSellerData = array_column($metrics['bestSellers'], 'qty');

        // Kept live for freshness (cheap queries; admins expect current values).
        $lowStockProducts = Product::lowStock()->with('category')->get();
        $recentOrders = Order::with('user')->latest()->take(5)->get();
        $recentReviews = Review::with(['user', 'product'])->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalSales', 'totalUsers', 'totalOrders',
            'pendingOrders', 'lowStockProducts', 'recentOrders',
            'revenueSeries',
            'bestSellerLabels', 'bestSellerData',
            'recentReviews'
        ));
    }

    /**
     * Paid revenue for the three ranges the dashboard chart can switch between:
     * today (per hour), the last 7 days and the last 30 days (per day).
     *
     * @return array<string, array{labels: string[], data: float[]}>
     */
    protected function buildRevenueSeries(): array
    {
        // One query covers both the 7- and 30-day ranges.
        $daily = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->selectRaw('DATE(created_at) as date, SUM(total) as revenue')
            ->groupBy('date')
            ->pluck('revenue', 'date')
            ->toArray();

        $range = function (int $days) use ($daily): array {
            $labels = [];
            $data = [];

            for ($i = $days - 1; $i >= 0; $i--) {
                $day = now()->subDays($i);
                $labels[] = $day->format('d M');
                $data[] = (float) ($daily[$day->format('Y-m-d')] ?? 0);
            }

            return ['labels' => $labels, 'data' => $data];
        };

        // Today, bucketed per hour. Bucketing in PHP rather than with HOUR()
        // keeps this working on SQLite (used by the test suite) as well as MySQL.
        $hourly = array_fill(0, 24, 0.0);

        Order::where('payment_status', 'paid')
            ->whereDate('created_at', today())
            ->get(['created_at', 'total'])
            ->each(function (Order $order) use (&$hourly) {
                $hourly[(int) $order->created_at->format('G')] += (float) $order->total;
            });

        return [
            'day' => [
                'labels' => array_map(fn ($h) => sprintf('%02d:00', $h), range(0, 23)),
                'data' => array_values($hourly),
            ],
            'week' => $range(7),
            'month' => $range(30),
        ];
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
