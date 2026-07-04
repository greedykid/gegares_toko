<div x-data="{ isOpen: false }"
    @toggle-cart.window="isOpen = !isOpen"
    x-effect="
        document.querySelector('main') && (isOpen ? document.querySelector('main').classList.add('cart-open-blur') : document.querySelector('main').classList.remove('cart-open-blur'));
        document.documentElement && (isOpen ? document.documentElement.classList.add('overflow-hidden') : document.documentElement.classList.remove('overflow-hidden'));
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
                <div class="w-9 h-9 rounded-xl bg-primary-50 dark:bg-primary-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-slate-100 leading-none">Keranjang</h2>
                    @if(!empty($items))
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5 font-medium">{{ count($items) }} produk</p>
                    @endif
                </div>
            </div>
            <button @click="isOpen = false" class="p-2 rounded-xl text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all active:scale-95">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- ─── Items ─── --}}
        <div class="flex-1 overflow-y-auto custom-scrollbar px-5 py-4">
            @if(empty($items))
                {{-- Empty State --}}
                <div class="flex flex-col items-center justify-center h-full text-center px-6">
                    <div class="w-20 h-20 rounded-2xl bg-slate-50 dark:bg-slate-800/50 flex items-center justify-center mb-5">
                        <svg class="w-10 h-10 text-slate-200 dark:text-slate-700" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                    </div>
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Keranjang masih kosong</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Yuk temukan jajanan favorit kamu!</p>
                    <a href="{{ route('products.index') }}" @click="isOpen = false"
                       class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white text-sm font-bold rounded-xl hover:bg-primary-700 transition-all active:scale-95">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z"/></svg>
                        Mulai Belanja
                    </a>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($items as $cartKey => $item)
                        <div class="group rounded-2xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800/60 overflow-hidden transition-all hover:border-primary-200 dark:hover:border-primary-800 hover:shadow-sm" wire:key="cart-item-{{ $cartKey }}">
                            {{-- Product Info (Clickable) --}}
                            <a href="{{ route('products.show', $item['slug'] ?? $item['product_id']) }}" @click="isOpen = false"
                               class="flex gap-3 p-3 cursor-pointer">
                                {{-- Image --}}
                                <div class="w-16 h-16 rounded-xl overflow-hidden bg-slate-50 dark:bg-slate-800 shrink-0 ring-1 ring-slate-100 dark:ring-slate-700/50">
                                    @if($item['image'])
                                        <img src="{{ asset('storage/' . $item['image']) }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg class="w-6 h-6 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 18.75V5.25A2.25 2.25 0 0 1 5.25 3h13.5A2.25 2.25 0 0 1 21 5.25v13.5"/></svg>
                                        </div>
                                    @endif
                                </div>
                                {{-- Details --}}
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-[13px] font-bold text-slate-900 dark:text-slate-100 truncate group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">{{ $item['name'] }}</h4>
                                    @if(!empty($item['variant_name']))
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Varian: <span class="font-semibold">{{ $item['variant_name'] }}</span></p>
                                    @endif
                                    <p class="text-[13px] font-extrabold text-primary-600 dark:text-primary-400 mt-0.5">Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                                    @if($item['stock'] <= 5 && $item['stock'] > 0)
                                        <span class="inline-flex items-center gap-1 mt-1 px-1.5 py-0.5 rounded bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 text-[9px] font-bold uppercase">
                                            <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 6a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 6Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
                                            Sisa {{ $item['stock'] }}
                                        </span>
                                    @endif
                                </div>
                            </a>

                            {{-- Actions Bar --}}
                            <div class="flex items-center justify-between px-3 py-2 border-t border-slate-50 dark:border-slate-800/40 bg-slate-50/50 dark:bg-slate-800/20">
                                {{-- Quantity Controls with Manual Input --}}
                                <div class="flex items-center gap-0.5">
                                    <button wire:click="updateQuantity('{{ $cartKey }}', {{ $item['quantity'] - 1 }})"
                                            class="w-8 h-8 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:border-primary-300 dark:hover:border-primary-600 hover:text-primary-600 dark:hover:text-primary-400 transition-all active:scale-95 text-sm font-bold">-</button>
                                    <input type="number" 
                                           wire:key="qty-input-{{ $cartKey }}-{{ $item['quantity'] }}"
                                           value="{{ $item['quantity'] }}" 
                                           min="1" 
                                           max="{{ $item['stock'] }}"
                                           x-on:input="if(Number($event.target.value) > {{ $item['stock'] }}) $event.target.value = {{ $item['stock'] }}"
                                           wire:change="updateQuantity('{{ $cartKey }}', $event.target.value)"
                                           class="w-10 h-8 text-center text-[13px] font-bold text-slate-800 dark:text-slate-100 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                    <button wire:click="updateQuantity('{{ $cartKey }}', {{ $item['quantity'] + 1 }})"
                                            @if($item['quantity'] >= $item['stock']) disabled @endif
                                            class="w-8 h-8 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:border-primary-300 dark:hover:border-primary-600 hover:text-primary-600 dark:hover:text-primary-400 transition-all active:scale-95 text-sm font-bold disabled:opacity-40 disabled:cursor-not-allowed">+</button>
                                </div>

                                {{-- Item Subtotal + Remove --}}
                                <div class="flex items-center gap-2">
                                    <p class="text-[12px] font-bold text-slate-600 dark:text-slate-300">Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</p>
                                    <button wire:click="removeItem('{{ $cartKey }}')" 
                                            class="p-1.5 rounded-lg text-slate-300 dark:text-slate-600 hover:text-red-500 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 transition-all active:scale-90" 
                                            title="Hapus dari keranjang">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ─── Coupon Section ─── --}}
        @if(!empty($items))
        <div class="px-5 py-4 border-t border-slate-50 dark:border-slate-800/40 bg-slate-50/30 dark:bg-slate-800/10">
            @if($coupon)
                <div class="flex items-center justify-between p-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800/50 transition-all duration-300">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-800 flex items-center justify-center">
                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-12v.75m0 3v.75m0 3v.75m0 3V18M3.25 6.75a.75.75 0 0 0-.75.75v1.5a.75.75 0 0 0 .75.75h1.25v-3H3.25Zm16.25 0v3h1.25a.75.75 0 0 0 .75-.75v-1.5a.75.75 0 0 0-.75-.75h-1.25ZM3.25 13.5a.75.75 0 0 0-.75.75v1.5a.75.75 0 0 0 .75.75h1.25v-3H3.25Zm16.25 0v3h1.25a.75.75 0 0 0 .75-.75v-1.5a.75.75 0 0 0-.75-.75h-1.25ZM7.5 6.75h9v3h-9v-3Zm0 6.75h9v3h-9v-3Z" /></svg>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold text-emerald-700 dark:text-emerald-400 leading-none">{{ $coupon['code'] }}</p>
                            <p class="text-[9px] text-emerald-600/70 dark:text-emerald-500/70 mt-0.5 font-medium">Kupon Berhasil Terpasang</p>
                        </div>
                    </div>
                    <button wire:click="removeCoupon" class="p-1 rounded-lg text-emerald-400 hover:text-emerald-600 hover:bg-emerald-100 dark:hover:bg-emerald-800 transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @else
                <div class="flex items-center gap-2">
                    <input type="text" 
                           wire:model="couponCode"
                           wire:keydown.enter="applyCoupon"
                           placeholder="Ada kode promo?" 
                           class="flex-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-xs font-medium text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                    <button wire:click="applyCoupon"
                            class="px-4 py-2.5 bg-slate-900 dark:bg-slate-100 dark:text-slate-900 text-white text-xs font-bold rounded-xl hover:bg-slate-800 dark:hover:bg-white transition-all active:scale-95 disabled:opacity-50">
                        Klaim
                    </button>
                </div>
            @endif
        </div>
        @endif

        {{-- ─── Footer ─── --}}
        @if(!empty($items))
            <div class="border-t border-slate-100 dark:border-slate-800/60 px-5 py-4 bg-white dark:bg-slate-950">
                {{-- Summary --}}
                <div class="space-y-2 mb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-medium tracking-tight uppercase tracking-wider">Ringkasan Pesanan</span>
                        <span class="text-xs text-slate-400 dark:text-slate-500 font-medium">{{ collect($items)->sum('quantity') }} item</span>
                    </div>
                    
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">Subtotal</span>
                        <span class="text-sm font-bold text-slate-900 dark:text-slate-100">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                    </div>

                    @if($discount > 0)
                    <div class="flex items-center justify-between pt-0.5">
                        <span class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">Potongan Diskon</span>
                        <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">- Rp {{ number_format($discount, 0, ',', '.') }}</span>
                    </div>
                    @endif

                    <div class="flex items-center justify-between pt-3 mt-1 border-t border-slate-100 dark:border-slate-800/60">
                        <span class="text-sm font-bold text-slate-900 dark:text-slate-100">Total Bayar</span>
                        <span class="text-xl font-black text-primary-600 dark:text-primary-400 tracking-tight">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- Checkout Button --}}
                <a href="{{ route('checkout.index') }}" @click="isOpen = false"
                   class="flex items-center justify-center gap-2.5 w-full py-3.5 bg-primary-600 text-white font-bold rounded-xl hover:bg-primary-700 transition-all duration-200 active:scale-[0.98] shadow-lg shadow-primary-600/20">
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/></svg>
                    Lanjut ke Checkout
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                </a>
            </div>
        @endif
    </div>
</div>
