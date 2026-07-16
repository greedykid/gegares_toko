@extends('layouts.app')
@section('title', 'Produk')
@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12" x-data="{ filterOpen: false }">
        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">Semua Jajanan</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Temukan jajanan pasar favorit Anda</p>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            {{-- Sidebar Filter --}}
            <!-- Backdrop Overlay for Mobile -->
            <div x-show="filterOpen" x-cloak x-transition:enter="transition-opacity ease-out duration-[350ms]"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-in duration-[280ms]" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" @click="filterOpen = false"
                class="fixed inset-0 z-40 bg-black/40 backdrop-blur-xs lg:hidden" style="display: none;"></div>

            <aside x-cloak x-show="filterOpen"
                x-transition:enter="transition-transform transform ease-[cubic-bezier(0.32,0.72,0,1)] duration-[350ms] will-change-transform"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition-transform transform ease-[cubic-bezier(0.4,0,1,1)] duration-[280ms] will-change-transform"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                class="fixed inset-y-0 right-0 z-50 w-80 max-w-full bg-white dark:bg-slate-900 border-l border-slate-100 dark:border-slate-800 shadow-2xl p-6 overflow-y-auto lg:static lg:w-64 lg:h-auto lg:shadow-none lg:p-0 lg:bg-transparent lg:dark:bg-transparent lg:border-none lg:overflow-visible lg:translate-x-0 lg:!block">
                <form id="filter-form" method="GET" action="{{ route('products.index') }}"
                    class="space-y-6 lg:bg-white lg:dark:bg-slate-900 lg:border lg:border-slate-100 lg:dark:border-slate-800 lg:shadow-sm lg:rounded-2xl lg:p-6 transition-colors duration-300 lg:sticky lg:top-24">

                    {{-- Drawer Header for Mobile --}}
                    <div
                        class="flex items-center justify-between lg:hidden pb-4 border-b border-slate-100 dark:border-slate-800 mb-2">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                            </svg>
                            <span
                                class="text-sm font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wide">Filter
                                Produk</span>
                        </div>
                        <button type="button" @click="filterOpen = false"
                            class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <h3
                        class="hidden lg:block text-sm font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wide">
                        Filter</h3>

                    {{-- Category --}}
                    <div>
                        <label class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-2 block">Kategori</label>
                        <div x-data="{ 
                                open: false, 
                                selectedValue: '{{ request('category') }}', 
                                selectedLabel: '{{ request('category') ? $categories->firstWhere('slug', request('category'))->name ?? 'Semua Kategori' : 'Semua Kategori' }}'
                             }" class="relative w-full">
                            <input type="hidden" name="category" :value="selectedValue">
                            <button @click="open = !open" type="button"
                                class="w-full flex items-center justify-between pl-4 pr-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all cursor-pointer">
                                <span x-text="selectedLabel"></span>
                                <svg class="h-4 w-4 text-slate-400 dark:text-slate-500 transition-transform duration-200"
                                    :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>
                            <div x-show="open" @click.outside="open = false"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                                class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl py-1 max-h-60 overflow-y-auto"
                                style="display: none;">
                                <button type="button"
                                    @click="selectedValue = ''; selectedLabel = 'Semua Kategori'; open = false"
                                    class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                    :class="selectedValue === '' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">
                                    Semua Kategori
                                </button>
                                @foreach($categories as $cat)
                                    <button type="button"
                                        @click="selectedValue = '{{ $cat->slug }}'; selectedLabel = '{{ $cat->name }}'; open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                        :class="selectedValue === '{{ $cat->slug }}' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">
                                        {{ $cat->name }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Price Range --}}
                    <div>
                        <label class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-2 block">Harga</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min"
                                class="px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all dark:text-slate-200">
                            <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max"
                                class="px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all dark:text-slate-200">
                        </div>
                    </div>

                    {{-- Rating --}}
                    <div>
                        <label class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-2 block">Rating
                            Minimum</label>
                        <div x-data="{ 
                                open: false, 
                                selectedValue: '{{ request('rating') }}', 
                                selectedLabel: '{{ request('rating') ? request('rating') . '+ Bintang' : 'Semua Rating' }}'
                             }" class="relative w-full">
                            <input type="hidden" name="rating" :value="selectedValue">
                            <button @click="open = !open" type="button"
                                class="w-full flex items-center justify-between pl-4 pr-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all cursor-pointer">
                                <span x-text="selectedLabel"></span>
                                <svg class="h-4 w-4 text-slate-400 dark:text-slate-500 transition-transform duration-200"
                                    :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>
                            <div x-show="open" @click.outside="open = false"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                                class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl py-1 overflow-hidden"
                                style="display: none;">
                                <button type="button"
                                    @click="selectedValue = ''; selectedLabel = 'Semua Rating'; open = false"
                                    class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                    :class="selectedValue === '' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">
                                    Semua Rating
                                </button>
                                @for($i = 4; $i >= 1; $i--)
                                    <button type="button"
                                        @click="selectedValue = '{{ $i }}'; selectedLabel = '{{ $i }}+ Bintang'; open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                        :class="selectedValue === '{{ $i }}' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">
                                        {{ $i }}+ Bintang
                                    </button>
                                @endfor
                            </div>
                        </div>
                    </div>

                    {{-- Sort --}}
                    <div>
                        <label class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-2 block">Urutkan</label>
                        <div x-data="{ 
                                open: false, 
                                selectedValue: '{{ request('sort', 'latest') }}', 
                                selectedLabel: '{{ request('sort') == 'price_low' ? 'Harga Terendah' : (request('sort') == 'price_high' ? 'Harga Tertinggi' : (request('sort') == 'popular' ? 'Terpopuler' : (request('sort') == 'rating' ? 'Rating Tertinggi' : 'Terbaru'))) }}'
                             }" class="relative w-full">
                            <input type="hidden" name="sort" :value="selectedValue" id="filter-sort-input">
                            <button @click="open = !open" type="button"
                                class="w-full flex items-center justify-between pl-4 pr-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all cursor-pointer">
                                <span x-text="selectedLabel"></span>
                                <svg class="h-4 w-4 text-slate-400 dark:text-slate-500 transition-transform duration-200"
                                    :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>
                            <div x-show="open" @click.outside="open = false"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                                class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl py-1 overflow-hidden"
                                style="display: none;">
                                <button type="button"
                                    @click="selectedValue = 'latest'; selectedLabel = 'Terbaru'; open = false"
                                    class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                    :class="selectedValue === 'latest' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">Terbaru</button>
                                <button type="button"
                                    @click="selectedValue = 'price_low'; selectedLabel = 'Harga Terendah'; open = false"
                                    class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                    :class="selectedValue === 'price_low' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">Harga
                                    Terendah</button>
                                <button type="button"
                                    @click="selectedValue = 'price_high'; selectedLabel = 'Harga Tertinggi'; open = false"
                                    class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                    :class="selectedValue === 'price_high' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">Harga
                                    Tertinggi</button>
                                <button type="button"
                                    @click="selectedValue = 'popular'; selectedLabel = 'Terpopuler'; open = false"
                                    class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                    :class="selectedValue === 'popular' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">Terpopuler</button>
                                <button type="button"
                                    @click="selectedValue = 'rating'; selectedLabel = 'Rating Tertinggi'; open = false"
                                    class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                    :class="selectedValue === 'rating' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">Rating
                                    Tertinggi</button>
                            </div>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full py-2.5 bg-primary-600 dark:bg-primary-500 text-white text-sm font-semibold rounded-xl hover:bg-primary-700 dark:hover:bg-primary-600 transition-all">Terapkan
                        Filter</button>
                    <a href="{{ route('products.index') }}"
                        class="block text-center text-xs text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">Reset
                        Filter</a>
                </form>
            </aside>

            {{-- Products Grid --}}
            <div class="flex-1">
                @if($products->count())
                    {{-- Header Bar (Menampilkan & Urutkan) --}}
                    <div
                        class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800/80 shadow-sm rounded-2xl p-4 mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 transition-colors duration-300">
                        <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                            <svg class="w-4.5 h-4.5 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                            {{-- The counter lives outside the productPager scope, so it listens for
                                 the window event that scroll-loading fires. Without this it kept
                                 reading "1-12" from the server-rendered page while the grid below
                                 had already grown. `first` stays put (a visitor can land on ?page=2);
                                 only the upper bound tracks how many cards are actually rendered. --}}
                            <span x-data="{
                                    first: {{ $products->firstItem() ?? 0 }},
                                    last: {{ $products->lastItem() ?? 0 }},
                                 }"
                                 @products-loaded.window="last = first + $event.detail.count - 1">Menampilkan <strong
                                    class="px-2 py-0.5 rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-100/80 dark:border-slate-800 font-bold"><span x-text="first">{{ $products->firstItem() ?? 0 }}</span>-<span x-text="last">{{ $products->lastItem() ?? 0 }}</span></strong>
                                dari <strong
                                    class="px-2 py-0.5 rounded-lg bg-primary-50 dark:bg-primary-950/30 text-primary-700 dark:text-primary-400 border border-primary-100/30 font-bold">{{ $products->total() }}</strong>
                                produk</span>
                        </div>

                        <div class="flex items-center justify-between sm:justify-end gap-3 w-full sm:w-auto">
                            {{-- Mobile Filter Trigger Button --}}
                            <button @click="filterOpen = true" type="button"
                                class="lg:hidden flex items-center gap-2 px-4 py-2.5 text-sm font-bold rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all cursor-pointer">
                                <svg class="w-4 h-4 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                                </svg>
                                <span>Filter</span>
                            </button>

                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-1.5 text-sm font-medium text-slate-500 dark:text-slate-400">
                                    <svg class="w-4.5 h-4.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3 7.5 7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5" />
                                    </svg>
                                    <span>Urutkan:</span>
                                </div>
                                <div x-data="{ 
                                    open: false, 
                                    selectedValue: '{{ request('sort', 'latest') }}', 
                                    selectedLabel: '{{ request('sort') == 'price_low' ? 'Harga Terendah' : (request('sort') == 'price_high' ? 'Harga Tertinggi' : (request('sort') == 'popular' ? 'Terpopuler' : (request('sort') == 'rating' ? 'Rating Tertinggi' : 'Terbaru'))) }}'
                                     }" class="relative inline-block w-full sm:w-auto min-w-0 sm:min-w-[160px] text-left">
                                    <button @click="open = !open" type="button"
                                        class="w-full sm:w-auto flex items-center justify-between px-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all cursor-pointer font-medium">
                                        <span x-text="selectedLabel"></span>
                                        <svg class="h-4 w-4 text-slate-400 dark:text-slate-500 transition-transform duration-200"
                                            :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                    <div x-show="open" @click.outside="open = false"
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-95"
                                        class="absolute right-0 z-50 sm:w-auto w-full mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl py-1 overflow-hidden"
                                        style="display: none;">
                                        <button type="button"
                                            @click="selectedValue = 'latest'; selectedLabel = 'Terbaru'; open = false; document.getElementById('filter-sort-input').value = 'latest'; document.getElementById('filter-form').submit();"
                                            class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                            :class="selectedValue === 'latest' ? 'bg-slate-50 dark:bg-slate-800/50 font-bold text-primary-600 dark:text-primary-400' : ''">Terbaru</button>
                                        <button type="button"
                                            @click="selectedValue = 'price_low'; selectedLabel = 'Harga Terendah'; open = false; document.getElementById('filter-sort-input').value = 'price_low'; document.getElementById('filter-form').submit();"
                                            class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                            :class="selectedValue === 'price_low' ? 'bg-slate-50 dark:bg-slate-800/50 font-bold text-primary-600 dark:text-primary-400' : ''">Harga
                                            Terendah</button>
                                        <button type="button"
                                            @click="selectedValue = 'price_high'; selectedLabel = 'Harga Tertinggi'; open = false; document.getElementById('filter-sort-input').value = 'price_high'; document.getElementById('filter-form').submit();"
                                            class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                            :class="selectedValue === 'price_high' ? 'bg-slate-50 dark:bg-slate-800/50 font-bold text-primary-600 dark:text-primary-400' : ''">Harga
                                            Tertinggi</button>
                                        <button type="button"
                                            @click="selectedValue = 'popular'; selectedLabel = 'Terpopuler'; open = false; document.getElementById('filter-sort-input').value = 'popular'; document.getElementById('filter-form').submit();"
                                            class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                            :class="selectedValue === 'popular' ? 'bg-slate-50 dark:bg-slate-800/50 font-bold text-primary-600 dark:text-primary-400' : ''">Terpopuler</button>
                                        <button type="button"
                                            @click="selectedValue = 'rating'; selectedLabel = 'Rating Tertinggi'; open = false; document.getElementById('filter-sort-input').value = 'rating'; document.getElementById('filter-form').submit();"
                                            class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                            :class="selectedValue === 'rating' ? 'bg-slate-50 dark:bg-slate-800/50 font-bold text-primary-600 dark:text-primary-400' : ''">Rating
                                            Tertinggi</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @include('products.partials.active-filters')

                    <div x-data="productPager(@js($products->nextPageUrl()), @js($products->hasPages()))">
                        <div x-ref="grid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-6">
                            @include('products.partials.grid-items', ['products' => $products])
                        </div>

                        {{-- Skeletons keep the grid height stable while a batch is in flight.
                             They fade in, sweep with a shimmer, and mirror the real card
                             shape so the swap to loaded cards is barely perceptible. --}}
                        <div x-show="loading" x-cloak
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-6 mt-3 sm:mt-4 lg:mt-6">
                            <template x-for="i in 4" :key="i">
                                <div class="rounded-3xl border border-slate-100 dark:border-slate-800/60 overflow-hidden">
                                    <div class="aspect-square shimmer-block"></div>
                                    <div class="p-3 sm:p-4 space-y-2.5">
                                        <div class="h-3.5 w-3/4 rounded-md shimmer-block"></div>
                                        <div class="h-3 w-1/2 rounded-md shimmer-block"></div>
                                        <div class="h-6 w-1/3 rounded-md shimmer-block mt-3"></div>
                                        <div class="h-9 w-full rounded-xl shimmer-block mt-1"></div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Tripwire: entering the viewport pulls the next batch in early --}}
                        <div x-ref="sentinel" aria-hidden="true" class="h-px"></div>

                        <div class="mt-8 flex flex-col items-center gap-3" aria-live="polite">
                            <p x-show="error" x-cloak class="text-sm font-semibold text-red-500">
                                Gagal memuat produk. Periksa koneksi Anda.
                            </p>

                            <button type="button" x-show="nextUrl" x-cloak @click="loadMore()" :disabled="loading"
                                class="px-6 py-3 rounded-xl bg-primary-600 hover:bg-primary-700 disabled:opacity-60 disabled:cursor-not-allowed text-white text-sm font-bold transition-colors">
                                <span x-text="loading ? 'Memuat…' : (error ? 'Coba Lagi' : 'Muat Lebih Banyak')"></span>
                            </button>

                            <p x-show="paginated && !nextUrl && !loading" x-cloak
                                class="text-sm text-slate-400 dark:text-slate-500">
                                Semua produk sudah ditampilkan.
                            </p>
                        </div>

                        {{-- Without JS the classic page links remain the only way through --}}
                        <noscript>
                            <div class="mt-8">{{ $products->links() }}</div>
                        </noscript>
                    </div>
                @else
                    {{-- Also shown here: with no results there is no header bar, and these
                         badges are the visitor's only way to undo the filter that emptied
                         the page. --}}
                    @include('products.partials.active-filters')

                    <div class="flex flex-col items-center justify-center py-20 text-center">
                        <svg class="w-16 h-16 text-slate-200 dark:text-slate-700 mb-4" fill="none" viewBox="0 0 24 24"
                            stroke-width="1" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <p class="text-lg font-semibold text-slate-500 transition-colors">Tidak ada produk ditemukan</p>
                        <p class="text-sm text-slate-400 mt-1 transition-colors">Coba ubah filter pencarian Anda</p>

                        {{-- A dead end otherwise: the badges above undo one filter at a time,
                             but a visitor who just wants out needs a single way back to the
                             full catalogue. --}}
                        <a href="{{ route('products.index') }}"
                            class="mt-6 inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold transition-colors">
                            Lihat Semua Jajanan
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('productPager', (initialNextUrl, paginated = false) => ({
                nextUrl: initialNextUrl,
                paginated,
                loading: false,
                error: false,
                observer: null,

                init() {
                    if (!this.nextUrl || !('IntersectionObserver' in window)) return;

                    // Prefetch a screen early so the grid never visibly runs dry.
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
                        const url = new URL(this.nextUrl, window.location.origin);
                        url.searchParams.set('partial', '1');

                        const response = await fetch(url, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        });

                        if (!response.ok) throw new Error(`HTTP ${response.status}`);

                        const { html, next_page_url: next } = await response.json();

                        // Alpine's mutation observer initialises the appended cards,
                        // which is also what hydrates their Livewire wishlist buttons.
                        this.$refs.grid.insertAdjacentHTML('beforeend', html);

                        // Tell the "Menampilkan x-y dari z" counter, which sits outside
                        // this component, how many cards are now on the page.
                        window.dispatchEvent(new CustomEvent('products-loaded', {
                            detail: { count: this.$refs.grid.children.length },
                        }));

                        this.nextUrl = next;
                        if (!this.nextUrl) this.observer?.disconnect();
                    } catch (e) {
                        // Leave nextUrl intact so the button retries the same page.
                        this.error = true;
                    } finally {
                        this.loading = false;
                    }
                },
            }));
        });
    </script>
@endpush