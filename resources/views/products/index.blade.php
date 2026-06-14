@extends('layouts.app')
@section('title', 'Produk')
@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">Semua Produk</h1>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Temukan jajanan pasar favorit Anda</p>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">
        {{-- Sidebar Filter --}}
        <aside x-data="{ open: false }" class="w-full lg:w-64 shrink-0">
            {{-- Mobile Toggle Button --}}
            <button @click="open = !open" 
                    class="lg:hidden w-full flex items-center justify-between p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm mb-2 group transition-all duration-300">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                        </svg>
                    </div>
                    <span class="text-sm font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wide">Filter Produk</span>
                </div>
                <svg :class="open ? 'rotate-180' : ''" class="w-5 h-5 text-slate-400 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
            </button>

            <form x-show="open" 
                  x-transition:enter="transition ease-out duration-300"
                  x-transition:enter-start="opacity-0 -translate-y-4"
                  x-transition:enter-end="opacity-100 translate-y-0"
                  method="GET" action="{{ route('products.index') }}" 
                  class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-6 space-y-6 sticky top-24 transition-colors duration-300 lg:block!"
                  style="display: none;">
                <h3 class="hidden lg:block text-sm font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wide">Filter</h3>

                {{-- Category --}}
                <div>
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-2 block">Kategori</label>
                    <select name="category" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->slug }}" {{ request('category') == $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Price Range --}}
                <div>
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-2 block">Harga</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min" class="px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all dark:text-slate-200">
                        <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max" class="px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all dark:text-slate-200">
                    </div>
                </div>

                {{-- Rating --}}
                <div>
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-2 block">Rating Minimum</label>
                    <select name="rating" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all">
                        <option value="">Semua Rating</option>
                        @for($i = 4; $i >= 1; $i--)
                            <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>{{ $i }}+ Bintang</option>
                        @endfor
                    </select>
                </div>

                {{-- Sort --}}
                <div>
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-2 block">Urutkan</label>
                    <select name="sort" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all">
                        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Terbaru</option>
                        <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Harga Terendah</option>
                        <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Harga Tertinggi</option>
                        <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Terpopuler</option>
                        <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>Rating Tertinggi</option>
                    </select>
                </div>

                <button type="submit" class="w-full py-2.5 bg-primary-600 dark:bg-primary-500 text-white text-sm font-semibold rounded-xl hover:bg-primary-700 dark:hover:bg-primary-600 transition-all">Terapkan Filter</button>
                <a href="{{ route('products.index') }}" class="block text-center text-xs text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">Reset Filter</a>
            </form>
        </aside>

        {{-- Products Grid --}}
        <div class="flex-1">
            @if($products->count())
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
                    @foreach($products as $product)
                        @include('components.product-card', ['product' => $product])
                    @endforeach
                </div>
                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-20 text-center">
                    <svg class="w-16 h-16 text-slate-200 dark:text-slate-700 mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    <p class="text-lg font-semibold text-slate-500 transition-colors">Tidak ada produk ditemukan</p>
                    <p class="text-sm text-slate-400 mt-1 transition-colors">Coba ubah filter pencarian Anda</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
