@extends('layouts.admin')
@section('page_title', 'Dashboard')
@section('content')

{{-- Stats Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-6 transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Total Penjualan</p>
                <p class="mt-2 text-2xl font-extrabold text-slate-900 dark:text-slate-100">Rp {{ number_format($totalSales, 0, ',', '.') }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-900/40 flex items-center justify-center transition-colors">
                <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-6 transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Total Pelanggan</p>
                <p class="mt-2 text-2xl font-extrabold text-slate-900 dark:text-slate-100">{{ number_format($totalUsers) }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-900/40 flex items-center justify-center transition-colors">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-6 transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Total Pesanan</p>
                <p class="mt-2 text-2xl font-extrabold text-slate-900 dark:text-slate-100">{{ number_format($totalOrders) }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-900/40 flex items-center justify-center transition-colors">
                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-6 transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Menunggu Proses</p>
                <p class="mt-2 text-2xl font-extrabold text-slate-900 dark:text-slate-100">{{ $pendingOrders }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-900/40 flex items-center justify-center transition-colors">
                <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            </div>
        </div>
    </div>
</div>

{{-- Charts Section --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    {{-- Revenue Chart --}}
    <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm transition-all duration-300">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Grafik Pendapatan</h3>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Pendapatan 30 hari terakhir</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="flex items-center gap-1.5 text-xs font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-1 rounded-lg">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Area Chart
                </span>
            </div>
        </div>
        <div class="p-6">
            <div id="revenueChart" style="min-height: 350px;"></div>
        </div>
    </div>

    {{-- Best Sellers Pie Chart --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm transition-all duration-300">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Produk Terlaris</h3>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Top 5 produk berdasarkan volume</p>
        </div>
        <div class="p-6">
            <div id="bestSellersChart" style="min-height: 350px;"></div>
        </div>
    </div>

    {{-- Chart Data Provider --}}
    <div id="chartDataPrv" 
         data-series="{{ json_encode($chartData) }}" 
         data-labels="{{ json_encode($chartLabels) }}"
         data-bs-series="{{ json_encode($bestSellerData) }}"
         data-bs-labels="{{ json_encode($bestSellerLabels) }}"
         class="hidden"></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Recent Orders --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm transition-all duration-300 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Pesanan Terbaru</h3>
            <a href="{{ route('admin.orders.index') }}" class="text-xs font-semibold text-primary-600 dark:text-primary-400 hover:text-primary-700">Lihat Semua</a>
        </div>
        <div class="divide-y divide-slate-50 dark:divide-slate-800">
            @forelse($recentOrders as $order)
                <div class="px-6 py-4 flex items-center justify-between hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                    <div>
                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $order->order_number }}</p>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5 uppercase tracking-wider">{{ $order->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ $order->formatted_total }}</p>
                        <span class="inline-flex px-1.5 py-0.5 text-[10px] font-bold rounded mt-1 {{ match($order->status_color) { 'green', 'emerald' => 'bg-emerald-50 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400', 'red' => 'bg-red-50 dark:bg-red-900/40 text-red-700 dark:text-red-400', 'orange' => 'bg-orange-50 dark:bg-orange-900/40 text-orange-700 dark:text-orange-400', 'yellow' => 'bg-yellow-50 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-400', default => 'bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400' } }} uppercase">{{ $order->status_label }}</span>
                    </div>
                </div>
            @empty
                <p class="px-6 py-8 text-sm text-slate-400 dark:text-slate-500 text-center">Belum ada pesanan.</p>
            @endforelse
        </div>
    </div>

    {{-- Recent Reviews --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm transition-all duration-300 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Ulasan Terbaru</h3>
            <a href="{{ route('admin.reviews.index') }}" class="text-xs font-semibold text-primary-600 dark:text-primary-400 hover:text-primary-700">Lihat Semua</a>
        </div>
        <div class="divide-y divide-slate-50 dark:divide-slate-800">
            @forelse($recentReviews as $review)
                <div class="px-6 py-4 hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $review->user->name ?? 'User' }}</p>
                        <div class="flex items-center gap-0.5">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-3 h-3 {{ $i <= $review->rating ? 'text-amber-400' : 'text-slate-200 dark:text-slate-700' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            @endfor
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-1 italic">"{{ $review->comment }}"</p>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1 uppercase tracking-wider">{{ $review->product->name ?? 'Produk' }}</p>
                </div>
            @empty
                <p class="px-6 py-8 text-sm text-slate-400 dark:text-slate-500 text-center">Belum ada ulasan.</p>
            @endforelse
        </div>
    </div>

    {{-- Low Stock Alert --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm transition-all duration-300 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                Stok Hampir Habis
            </h3>
        </div>
        <div class="divide-y divide-slate-50 dark:divide-slate-800">
            @forelse($lowStockProducts as $product)
                <div class="px-6 py-3.5 flex items-center justify-between hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                    <div>
                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100 line-clamp-1">{{ $product->name }}</p>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 uppercase tracking-wider mt-0.5">{{ $product->category->name ?? 'Umum' }}</p>
                    </div>
                    <span class="px-2 py-1 text-[10px] font-bold bg-amber-50 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400 rounded-lg whitespace-nowrap">SISA {{ $product->stock }}</span>
                </div>
            @empty
                <p class="px-6 py-8 text-sm text-slate-400 dark:text-slate-500 text-center">Semua stok aman 👍</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dataProv = document.getElementById('chartDataPrv');
    const seriesData = JSON.parse(dataProv.dataset.series);
    const labelsData = JSON.parse(dataProv.dataset.labels);
    const bsSeries = JSON.parse(dataProv.dataset.bsSeries);
    const bsLabels = JSON.parse(dataProv.dataset.bsLabels);

    const getChartColors = () => {
        const isDark = document.documentElement.classList.contains('dark');
        return {
            grid: isDark ? '#1e293b' : '#f1f5f9',
            text: isDark ? '#94a3b8' : '#64748b',
            title: isDark ? '#f1f5f9' : '#1e293b',
            tooltipTheme: isDark ? 'dark' : 'light'
        };
    };

    let colors = getChartColors();

    const revenueOptions = {
        series: [{ name: 'Pendapatan', data: seriesData }],
        chart: {
            height: 350,
            type: 'area',
            toolbar: { show: false },
            fontFamily: 'Inter, sans-serif',
            zoom: { enabled: false }
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 3, colors: ['#10b981'] },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.45,
                opacityTo: 0.05,
                stops: [20, 100],
                colorStops: [
                    { offset: 0, color: '#10b981', opacity: 0.4 },
                    { offset: 100, color: '#10b981', opacity: 0 }
                ]
            }
        },
        grid: {
            borderColor: colors.grid,
            strokeDashArray: 4,
            xaxis: { lines: { show: true } },
            yaxis: { lines: { show: true } }
        },
        xaxis: {
            categories: labelsData,
            labels: {
                style: { colors: colors.text, fontSize: '11px' },
                rotate: -45,
                rotateAlways: false,
                hideOverlappingLabels: true,
            },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            labels: {
                style: { colors: colors.text, fontSize: '11px' },
                formatter: (value) => "Rp " + value.toLocaleString('id-ID')
            }
        },
        tooltip: {
            theme: colors.tooltipTheme,
            y: { formatter: (value) => "Rp " + value.toLocaleString('id-ID') }
        },
        colors: ['#10b981']
    };

    const revenueChart = new ApexCharts(document.querySelector("#revenueChart"), revenueOptions);
    revenueChart.render();

    const bsOptions = {
        series: bsSeries,
        chart: {
            height: 350,
            type: 'donut',
            fontFamily: 'Inter, sans-serif',
        },
        labels: bsLabels,
        dataLabels: { enabled: false },
        plotOptions: {
            pie: {
                donut: {
                    size: '75%',
                    labels: {
                        show: true,
                        name: { show: true, fontSize: '14px', fontWeight: 600, color: colors.text },
                        value: { show: true, fontSize: '20px', fontWeight: 800, color: colors.title, formatter: (val) => val + ' pcs' },
                        total: {
                            show: true,
                            label: 'Total Terjual',
                            fontSize: '12px',
                            fontWeight: 600,
                            color: colors.text,
                            formatter: (w) => w.globals.seriesTotals.reduce((a, b) => a + b, 0) + ' pcs'
                        }
                    }
                }
            }
        },
        legend: {
            position: 'bottom',
            fontSize: '11px',
            fontWeight: 500,
            labels: { colors: colors.text },
            markers: { radius: 12, offsetX: -4 }
        },
        colors: ['#10b981', '#3b82f6', '#f59e0b', '#8b5cf6', '#ec4899'],
        tooltip: {
            theme: colors.tooltipTheme,
            y: { formatter: (val) => val + ' pcs' }
        }
    };

    const bsChart = new ApexCharts(document.querySelector("#bestSellersChart"), bsOptions);
    bsChart.render();

    // Live Theme Switcher for Charts
    window.addEventListener('theme-changed', () => {
        const newColors = getChartColors();
        
        revenueChart.updateOptions({
            grid: { borderColor: newColors.grid },
            xaxis: { labels: { style: { colors: newColors.text } } },
            yaxis: { labels: { style: { colors: newColors.text } } },
            tooltip: { theme: newColors.tooltipTheme }
        });

        bsChart.updateOptions({
            plotOptions: {
                pie: {
                    donut: {
                        labels: {
                            name: { color: newColors.text },
                            value: { color: newColors.title },
                            total: { color: newColors.text }
                        }
                    }
                }
            },
            legend: { labels: { colors: newColors.text } },
            tooltip: { theme: newColors.tooltipTheme }
        });
    });
});
</script>
@endpush
