{{-- Product Card — enhanced variant used on the /products listing page only --}}
@props(['product'])

<div class="group relative flex flex-col bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800/60 hover:border-primary-200 dark:hover:border-primary-700/60 shadow-sm hover:shadow-xl hover:shadow-primary-200/40 dark:hover:shadow-black/40 hover:-translate-y-1.5 transition-all duration-500 ease-out overflow-hidden {{ $product->isOutOfStock() ? 'product-out-of-stock' : '' }}">

    {{-- Image Container --}}
    <a href="{{ route('products.show', $product) }}" class="block aspect-square overflow-hidden bg-slate-50 dark:bg-slate-800 relative">
        @if($product->image)
            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                 width="400" height="300"
                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-out {{ $product->isOutOfStock() ? 'grayscale' : '' }}"
                 loading="lazy">
        @else
            @include('components.image-placeholder')
        @endif

        {{-- Bottom gradient for legibility --}}
        <div class="absolute inset-x-0 bottom-0 h-20 bg-linear-to-t from-black/30 to-transparent opacity-60 group-hover:opacity-80 transition-opacity duration-500"></div>

        {{-- Top-left badges (stack) --}}
        <div class="absolute top-3 left-3 flex flex-col items-start gap-1.5">
            @if($product->isOutOfStock())
                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-bold bg-red-500 text-white rounded-lg shadow-sm uppercase tracking-wide">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z" clip-rule="evenodd"/></svg>
                    Habis
                </span>
            @elseif($product->isLowStock())
                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-bold bg-amber-500 text-white rounded-lg shadow-sm uppercase tracking-wide">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 6a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 6Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
                    Sisa {{ $product->stock }}
                </span>
            @endif

            @if($product->is_featured && !$product->isOutOfStock())
                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-bold bg-primary-600/95 text-white rounded-lg shadow-sm uppercase tracking-wide backdrop-blur-sm">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    Favorit
                </span>
            @endif
        </div>

        {{-- Category pill --}}
        @if($product->category)
            <div class="absolute bottom-3 left-3">
                <span class="px-2 py-0.5 text-[9px] font-bold bg-white/90 dark:bg-slate-900/80 backdrop-blur-sm text-primary-700 dark:text-primary-300 rounded-md uppercase tracking-wider shadow-sm">{{ $product->category->name }}</span>
            </div>
        @endif

    </a>

    {{-- Wishlist Heart --}}
    <livewire:toggle-wishlist :productId="$product->id" :key="'grid-wishlist-' . $product->id" />

    {{-- Content --}}
    <div class="flex flex-col flex-1 p-3 sm:p-4">
        {{-- Name --}}
        <a href="{{ route('products.show', $product) }}">
            <h3 class="text-[13px] sm:text-sm font-bold text-slate-800 dark:text-slate-100 line-clamp-1 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors duration-300">{{ $product->name }}</h3>
        </a>

        {{-- Rating --}}
        <div class="flex items-center flex-nowrap gap-1.5 mt-1.5 overflow-hidden">
            <div class="flex items-center gap-px">
                @for($i = 1; $i <= 5; $i++)
                    @if(($product->rating_count ?? 0) > 0 && $i <= round($product->rating_avg ?? 0))
                        <svg class="w-3 h-3 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @else
                        <svg class="w-3 h-3 text-slate-200 dark:text-slate-700" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endif
                @endfor
            </div>
            @if(($product->rating_count ?? 0) > 0)
                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-medium whitespace-nowrap">{{ number_format($product->rating_avg ?? 0, 1) }} ({{ $product->rating_count }})</span>
            @else
                <span class="text-[9px] text-slate-400 dark:text-slate-600 font-medium italic whitespace-nowrap truncate">Belum ada ulasan</span>
            @endif
        </div>

        {{-- Price --}}
        <div class="mt-3 pt-3 border-t border-slate-50 dark:border-slate-800/50">
            <div class="flex items-baseline gap-1">
                <span class="text-base font-black text-slate-900 dark:text-slate-100">{{ $product->formatted_price }}</span>
                <span class="text-[10px] font-medium text-slate-400 dark:text-slate-500">/ porsi</span>
            </div>
        </div>

        {{-- CTA (full-width) --}}
        <div class="mt-3">
            @if($product->isOutOfStock())
                <span class="flex items-center justify-center w-full px-3 py-2.5 text-[11px] font-bold bg-slate-50 dark:bg-slate-800 text-slate-400 dark:text-slate-500 rounded-xl uppercase tracking-wider cursor-not-allowed">Stok Habis</span>
            @else
                @auth
                    <button
                        data-product-id="{{ $product->id }}"
                        onclick="Livewire.dispatch('add-to-cart', { productId: this.dataset.productId })"
                        class="flex items-center justify-center gap-1.5 sm:gap-2 w-full px-2 sm:px-3 py-2.5 rounded-xl bg-primary-700 text-white text-[11px] sm:text-xs font-bold whitespace-nowrap hover:bg-primary-800 transition-all duration-300 active:scale-[0.98] shadow-sm shadow-primary-700/20"
                        title="Tambah ke Keranjang"
                        aria-label="Beli {{ $product->name }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                        <span>Tambah<span class="hidden sm:inline"> ke Keranjang</span></span>
                    </button>
                @else
                    <a href="{{ route('login') }}"
                       class="flex items-center justify-center gap-1.5 sm:gap-2 w-full px-2 sm:px-3 py-2.5 rounded-xl bg-primary-700 text-white text-[11px] sm:text-xs font-bold whitespace-nowrap hover:bg-primary-800 transition-all duration-300 active:scale-[0.98] shadow-sm shadow-primary-700/20"
                       aria-label="Beli {{ $product->name }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                        <span>Tambah<span class="hidden sm:inline"> ke Keranjang</span></span>
                    </a>
                @endauth
            @endif
        </div>
    </div>
</div>
