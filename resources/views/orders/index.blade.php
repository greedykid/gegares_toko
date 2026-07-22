@extends('layouts.app')
@section('title', 'Pesanan Saya')
@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
    {{-- Header --}}
    <div class="mb-10">
        <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Pesanan Saya</h1>
        <p class="mt-2 text-sm font-medium text-slate-500 dark:text-slate-400">Pantau status, riwayat belanja, dan lacak produk favorit Anda</p>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-12">
        {{-- Total Orders Card --}}
        <div class="bg-white dark:bg-slate-900/60 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm ring-1 ring-slate-200/50 dark:ring-slate-800/80 transition-all hover:bg-slate-50 dark:hover:bg-slate-900/80 group">
            <div class="flex flex-col gap-3">
                <div class="w-10 h-10 flex items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Semua Pesanan</p>
                    <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>

        {{-- Pending Card --}}
        <div class="bg-white dark:bg-slate-900/60 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm ring-1 ring-slate-200/50 dark:ring-slate-800/80 transition-all hover:bg-slate-50 dark:hover:bg-slate-900/80 group">
            <div class="flex flex-col gap-3">
                <div class="w-10 h-10 flex items-center justify-center rounded-2xl bg-orange-50 dark:bg-orange-500/10 text-orange-600 dark:text-orange-500 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Belum Dibayar</p>
                    <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $stats['pending'] }}</p>
                </div>
            </div>
        </div>

        {{-- Processing Card --}}
        <div class="bg-white dark:bg-slate-900/60 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm ring-1 ring-slate-200/50 dark:ring-slate-800/80 transition-all hover:bg-slate-50 dark:hover:bg-slate-900/80 group">
            <div class="flex flex-col gap-3">
                <div class="w-10 h-10 flex items-center justify-center rounded-2xl bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-500 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Diproses</p>
                    <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $stats['processing'] }}</p>
                </div>
            </div>
        </div>

        {{-- Completed Card --}}
        <div class="bg-white dark:bg-slate-900/60 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm ring-1 ring-slate-200/50 dark:ring-slate-800/80 transition-all hover:bg-slate-50 dark:hover:bg-slate-900/80 group">
            <div class="flex flex-col gap-3">
                <div class="w-10 h-10 flex items-center justify-center rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-500 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Selesai</p>
                    <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $stats['completed'] }}</p>
                </div>
            </div>
        </div>

        {{-- Spending Card --}}
        <div class="bg-white dark:bg-slate-900/60 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm ring-1 ring-slate-200/50 dark:ring-slate-800/80 transition-all hover:bg-slate-50 dark:hover:bg-slate-900/80 group col-span-2 md:col-span-1 border-l-4 border-l-primary-500 overflow-hidden relative">
            <div class="absolute inset-0 bg-linear-to-br from-primary-500/5 to-transparent"></div>
            <div class="flex flex-col gap-3 relative z-10">
                <div class="w-10 h-10 flex items-center justify-center rounded-2xl bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-500 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black text-primary-500/80 dark:text-primary-400/80 uppercase tracking-widest mb-1">Pengeluaran</p>
                    <p class="text-xl font-black text-slate-900 dark:text-white truncate">Rp {{ number_format($stats['monthly_spent'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtering Tabs --}}
    @php $currentStatus = request('status', 'all'); @endphp
    <div class="flex items-center gap-3 overflow-x-auto pb-6 mb-2 hide-scrollbar">
        <a href="{{ route('orders.index') }}" class="shrink-0 px-5 py-2.5 rounded-xl text-sm font-bold border-2 transition-all {{ $currentStatus === 'all' ? 'bg-slate-900 border-slate-900 text-white dark:bg-white dark:border-white dark:text-slate-900 shadow-md shadow-slate-900/20' : 'bg-transparent border-slate-200 dark:border-slate-800 text-slate-500 hover:border-slate-400 hover:text-slate-800 dark:hover:border-slate-600 dark:hover:text-slate-300' }}">Semua Pesanan</a>
        <a href="{{ route('orders.index', ['status' => 'pending']) }}" class="shrink-0 px-5 py-2.5 rounded-xl text-sm font-bold border-2 transition-all {{ $currentStatus === 'pending' ? 'bg-orange-500 border-orange-500 text-white shadow-md shadow-orange-500/20 dark:bg-orange-500 dark:border-orange-500' : 'bg-transparent border-slate-200 dark:border-slate-800 text-slate-500 hover:border-orange-300 hover:text-orange-600 dark:hover:border-orange-800/80 dark:hover:text-orange-500' }}">Tertunda</a>
        <a href="{{ route('orders.index', ['status' => 'processing']) }}" class="shrink-0 px-5 py-2.5 rounded-xl text-sm font-bold border-2 transition-all {{ $currentStatus === 'processing' ? 'bg-blue-500 border-blue-500 text-white shadow-md shadow-blue-500/20 dark:bg-blue-500 dark:border-blue-500' : 'bg-transparent border-slate-200 dark:border-slate-800 text-slate-500 hover:border-blue-300 hover:text-blue-600 dark:hover:border-blue-800/80 dark:hover:text-blue-500' }}">Diproses</a>
        <a href="{{ route('orders.index', ['status' => 'completed']) }}" class="shrink-0 px-5 py-2.5 rounded-xl text-sm font-bold border-2 transition-all {{ $currentStatus === 'completed' ? 'bg-emerald-500 border-emerald-500 text-white shadow-md shadow-emerald-500/20 dark:bg-emerald-500 dark:border-emerald-500' : 'bg-transparent border-slate-200 dark:border-slate-800 text-slate-500 hover:border-emerald-300 hover:text-emerald-600 dark:hover:border-emerald-800/80 dark:hover:text-emerald-500' }}">Selesai</a>
        <a href="{{ route('orders.index', ['status' => 'cancelled']) }}" class="shrink-0 px-5 py-2.5 rounded-xl text-sm font-bold border-2 transition-all {{ $currentStatus === 'cancelled' ? 'bg-red-500 border-red-500 text-white shadow-md shadow-red-500/20 dark:bg-red-500 dark:border-red-500' : 'bg-transparent border-slate-200 dark:border-slate-800 text-slate-500 hover:border-red-300 hover:text-red-600 dark:hover:border-red-800/80 dark:hover:text-red-500' }}">Dibatalkan</a>
    </div>

    {{-- Orders List --}}
    @if($orders->count())
        <div x-data="orderPager(@js($orders->nextPageUrl()), @js($orders->hasPages()))">
            <div x-ref="list" class="space-y-6 lg:space-y-8">
                @include('orders.partials.order-cards', ['orders' => $orders])
            </div>

            {{-- Skeletons hold the page height while a batch is in flight, shaped
                 like the real cards so the swap is barely noticeable. --}}
            <div x-show="loading" x-cloak
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="space-y-6 lg:space-y-8 mt-6 lg:mt-8">
                <template x-for="i in 2" :key="i">
                    <div class="rounded-4xl border border-slate-100 dark:border-slate-800/60 overflow-hidden">
                        <div class="px-6 py-5 sm:px-8 flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl shimmer-block"></div>
                            <div class="space-y-2">
                                <div class="h-4 w-40 rounded-md shimmer-block"></div>
                                <div class="h-3 w-28 rounded-md shimmer-block"></div>
                            </div>
                            <div class="ml-auto h-7 w-24 rounded-xl shimmer-block"></div>
                        </div>
                        <div class="px-6 py-6 sm:px-8 flex items-center gap-4">
                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl shimmer-block"></div>
                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl shimmer-block"></div>
                            <div class="ml-auto h-8 w-32 rounded-md shimmer-block"></div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Tripwire: entering the viewport pulls the next batch in early --}}
            <div x-ref="sentinel" aria-hidden="true" class="h-px"></div>

            <div class="mt-10 flex flex-col items-center gap-3" aria-live="polite">
                <p x-show="error" x-cloak class="text-sm font-semibold text-red-500">
                    Gagal memuat pesanan. Periksa koneksi Anda.
                </p>

                <button type="button" x-show="nextUrl" x-cloak @click="loadMore()" :disabled="loading"
                    class="px-6 py-3 rounded-xl bg-primary-600 hover:bg-primary-700 disabled:opacity-60 disabled:cursor-not-allowed text-white text-sm font-bold transition-colors">
                    <span x-text="loading ? 'Memuat…' : (error ? 'Coba Lagi' : 'Muat Lebih Banyak')"></span>
                </button>

                <p x-show="paginated && !nextUrl && !loading" x-cloak
                    class="text-sm text-slate-400 dark:text-slate-500">
                    Semua pesanan sudah ditampilkan.
                </p>
            </div>

            {{-- Without JS the classic page links remain the only way through --}}
            <noscript>
                <div class="mt-10">{{ $orders->links() }}</div>
            </noscript>
        </div>
    @else
        <div class="bg-white dark:bg-slate-900/60 rounded-[3rem] border border-slate-100 dark:border-slate-800 shadow-sm ring-1 ring-slate-200/50 dark:ring-slate-800/80 p-12 lg:p-24 text-center">
            <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-full bg-slate-50 dark:bg-slate-800 mx-auto flex items-center justify-center mb-8">
                <svg class="w-12 h-12 sm:w-16 sm:h-16 text-slate-300 dark:text-slate-700" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
            </div>
            <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-2">Belum Ada Pesanan</h2>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-8 max-w-sm mx-auto">Anda belum melakukan pesanan apa pun. Mulai jelajahi produk menarik kami sekarang juga!</p>
            <a href="{{ route('products.index') }}" class="inline-flex items-center px-8 py-4 bg-primary-600 text-white text-sm font-bold rounded-2xl hover:bg-primary-700 transition-all transform hover:-translate-y-0.5 active:scale-95 group">
                Mulai Belanja 
                <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
            </a>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('orderPager', (initialNextUrl, paginated = false) => ({
            nextUrl: initialNextUrl,
            paginated,
            loading: false,
            error: false,
            observer: null,

            init() {
                if (!this.nextUrl || !('IntersectionObserver' in window)) return;

                // Prefetch a screen early so the list never visibly runs dry.
                this.observer = new IntersectionObserver(([entry]) => {
                    if (entry.isIntersecting && !this.loading && !this.error) {
                        this.loadMore();
                    }
                }, { rootMargin: '400px 0px' });

                this.observer.observe(this.$refs.sentinel);
            },

            destroy() {
                this.observer?.disconnect();
            },

            async loadMore() {
                if (this.loading || !this.nextUrl) return;

                this.loading = true;
                this.error = false;

                try {
                    // Keeps the active status filter: nextPageUrl() already carries it.
                    const url = new URL(this.nextUrl, window.location.origin);
                    url.searchParams.set('partial', '1');

                    const response = await fetch(url, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });

                    if (!response.ok) throw new Error(`HTTP ${response.status}`);

                    const { html, next_page_url: next } = await response.json();

                    this.$refs.list.insertAdjacentHTML('beforeend', html);

                    this.nextUrl = next;
                    if (!this.nextUrl) this.observer?.disconnect();
                } catch (e) {
                    // Leave nextUrl intact so the button retries the same page.
                    this.error = true;
                }finally {
                    this.loading = false;
                }
            },
        }));
    });
</script>
@endpush
