<div x-data="{ isOpen: false }"
    @toggle-wishlist.window="isOpen = !isOpen"
    x-effect="
        document.querySelector('main') && (isOpen ? document.querySelector('main').classList.add('cart-open-blur') : document.querySelector('main').classList.remove('cart-open-blur'));
        document.body && (isOpen ? document.body.classList.add('overflow-hidden') : document.body.classList.remove('overflow-hidden'));
    "
    x-cloak
    class="relative z-50">
    
    {{-- Overlay --}}
    <div x-show="isOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-950/40 backdrop-blur-xs transition-opacity cursor-pointer z-40"
         @click="isOpen = false"></div>

    {{-- Drawer Panel --}}
    <div x-show="isOpen"
         x-transition:enter="transform transition ease-in-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transform transition ease-in-out duration-300"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="fixed top-0 right-0 z-50 h-full w-full max-w-md bg-white dark:bg-slate-950 shadow-2xl flex flex-col">
        
        {{-- ─── Header ─── --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-800/60 bg-white dark:bg-slate-950">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-900/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-500 dark:text-red-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="m11.645 20.91-.007-.003-.022-.012a15.247 15.247 0 0 1-.383-.218 25.18 25.18 0 0 1-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.579 5.579 0 0 1 12 5.052 5.579 5.579 0 0 1 16.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 0 1-4.244 3.17 15.247 15.247 0 0 1-.383.219l-.022.012-.007.004-.003.001Z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-slate-100 leading-none">Wishlist</h2>
                    @if(!empty($items) && $items->isNotEmpty())
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5 font-medium">{{ $items->count() }} produk tersimpan</p>
                    @endif
                </div>
            </div>
            <button @click="isOpen = false" class="p-2 rounded-xl text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all active:scale-95">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- ─── Items ─── --}}
        <div class="flex-1 overflow-y-auto custom-scrollbar px-5 py-4">
            @if(empty($items) || $items->isEmpty())
                {{-- Empty State --}}
                <div class="flex flex-col items-center justify-center h-full text-center px-6">
                    <div class="w-20 h-20 rounded-2xl bg-slate-50 dark:bg-slate-800/50 flex items-center justify-center mb-5">
                        <svg class="w-10 h-10 text-slate-200 dark:text-slate-700" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                    </div>
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Wishlist masih kosong</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Simpan produk favorit untuk dibeli nanti!</p>
                    <a href="{{ route('products.index') }}" @click="isOpen = false"
                       class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white text-sm font-bold rounded-xl hover:bg-primary-700 transition-all active:scale-95">
                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z"/></svg>
                        Cari Produk Favorit
                    </a>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($items as $item)
                        @php $product = $item->product @endphp
                        @if($product)
                        <div class="group rounded-2xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800/60 overflow-hidden transition-all hover:border-red-200 dark:hover:border-red-800/40 hover:shadow-sm" wire:key="wishlist-item-{{ $item->id }}">
                            {{-- Product Info (Clickable) --}}
                            <a href="{{ route('products.show', $product->slug) }}" @click="isOpen = false"
                               class="flex gap-3 p-3 cursor-pointer">
                                {{-- Image --}}
                                <div class="w-16 h-16 rounded-xl overflow-hidden bg-slate-50 dark:bg-slate-800 shrink-0 ring-1 ring-slate-100 dark:ring-slate-700/50">
                                    @if($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg class="w-6 h-6 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 18.75V5.25A2.25 2.25 0 0 1 5.25 3h13.5A2.25 2.25 0 0 1 21 5.25v13.5"/></svg>
                                        </div>
                                    @endif
                                </div>
                                {{-- Details --}}
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-[13px] font-bold text-slate-900 dark:text-slate-100 truncate group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">{{ $product->name }}</h4>
                                    <p class="text-[13px] font-extrabold text-primary-600 dark:text-primary-400 mt-0.5">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                                    @if($product->stock <= 0)
                                        <span class="inline-flex items-center gap-1 mt-1 px-1.5 py-0.5 rounded bg-red-50 dark:bg-red-900/20 text-red-500 dark:text-red-400 text-[9px] font-bold uppercase">Stok Habis</span>
                                    @elseif($product->stock <= 5)
                                        <span class="inline-flex items-center gap-1 mt-1 px-1.5 py-0.5 rounded bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 text-[9px] font-bold uppercase">
                                            <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 6a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 6Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
                                            Sisa {{ $product->stock }}
                                        </span>
                                    @endif
                                </div>
                            </a>

                            {{-- Actions Bar --}}
                            <div class="flex items-center justify-between px-3 py-2 border-t border-slate-50 dark:border-slate-800/40 bg-slate-50/50 dark:bg-slate-800/20">
                                @if($product->stock > 0)
                                    <button wire:click="addToCart({{ $product->id }}, {{ $item->id }})" 
                                            class="flex-1 flex items-center justify-center gap-1.5 py-1.5 bg-primary-600 text-white text-[10px] font-bold rounded-lg hover:bg-primary-700 transition-all active:scale-95">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                                        + Keranjang
                                    </button>
                                @else
                                    <button disabled class="flex-1 flex items-center justify-center py-1.5 bg-slate-100 dark:bg-slate-700 text-slate-400 dark:text-slate-500 text-[10px] font-bold rounded-lg cursor-not-allowed">Stok Habis</button>
                                @endif
                                <button wire:click="removeItem({{ $item->id }})" 
                                        class="ml-1.5 p-1.5 rounded-lg text-slate-300 dark:text-slate-600 hover:text-red-500 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 transition-all active:scale-90" 
                                        title="Hapus dari wishlist">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                </button>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ─── Footer ─── --}}
        @if(!empty($items) && $items->isNotEmpty())
            <div class="border-t border-slate-100 dark:border-slate-800/60 px-5 py-4 bg-white dark:bg-slate-950">
                <a href="{{ route('products.index') }}" @click="isOpen = false"
                   class="flex items-center justify-center gap-2.5 w-full py-3.5 bg-primary-600 text-white font-bold rounded-xl hover:bg-primary-700 transition-all duration-200 active:scale-[0.98] shadow-lg shadow-primary-600/20">
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z"/></svg>
                    Lanjut Belanja
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                </a>
            </div>
        @endif
    </div>
</div>
