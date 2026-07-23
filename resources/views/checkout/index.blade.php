@extends('layouts.app')
@section('title', 'Checkout')
@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12" x-data="checkoutFlow" data-address-id="{{ $addresses->where('is_primary', true)->first()?->id ?? $addresses->first()?->id ?? '' }}">
    
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('home') }}" class="flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-slate-900 shadow-sm ring-1 ring-slate-200 dark:ring-slate-800 text-slate-500 hover:text-primary-600 dark:text-slate-400 dark:hover:text-primary-400 transition-all hover:-translate-x-1">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        </a>
        <div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Checkout</h1>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mt-1">Selesaikan pesanan Anda dengan aman.</p>
        </div>
    </div>

    {{-- When the shop is shut the parcel cannot be collected until it opens, so
         the customer confirms that before paying rather than discovering it
         afterwards. Inside opening hours this never fires. --}}
    <form method="POST" action="{{ route('checkout.store') }}" id="checkoutForm" x-ref="checkoutForm"
          @submit="
              if ((storeOpen && !courierOpensAt) || closedAcknowledged) { loading = true; return; }
              $event.preventDefault();
              showClosedModal = true;
          ">
        @csrf
        {{-- Hidden Fields --}}
        <input type="hidden" name="address_id" x-model="addressId">
        <input type="hidden" name="shipping_courier" x-model="shippingCourier">
        <input type="hidden" name="shipping_service" x-model="shippingService">
        <input type="hidden" name="shipping_cost" x-model="shippingCost">
        <input type="hidden" name="payment_method" x-model="paymentMethod">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 xl:gap-10">
            {{-- Left Column: Forms --}}
            <div class="lg:col-span-7 xl:col-span-8 space-y-6">
                
                {{-- Address Section --}}
                <div class="bg-white dark:bg-slate-900/60 rounded-3xl ring-1 ring-slate-200/50 dark:ring-slate-800/80 shadow-sm overflow-hidden relative">
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-linear-to-r from-primary-400 to-emerald-400"></div>
                    <div class="p-6 sm:p-8">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 rounded-2xl bg-primary-50 dark:bg-primary-900/40 flex items-center justify-center text-primary-600 dark:text-primary-400 shrink-0">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Alamat Pengiriman</h2>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Pilih alamat tujuan pengiriman pesanan Anda.</p>
                            </div>
                        </div>

                        @if($errors->any())
                            <div class="mb-6 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 p-4 rounded-2xl text-sm ring-1 ring-red-200 dark:ring-red-900/50">
                                <ul class="list-disc pl-5 font-medium space-y-1">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @livewire('manage-addresses', ['selectedAddressId' => old('address_id', $addresses->where('is_primary', true)->first()?->id ?? $addresses->first()?->id)])
                    </div>
                </div>

                {{-- Shipping Section --}}
                <div class="bg-white dark:bg-slate-900/60 rounded-3xl ring-1 ring-slate-200/50 dark:ring-slate-800/80 shadow-sm p-6 sm:p-8">
                    @livewire('select-shipping', ['cartItems' => $cartItems])
                </div>

                {{-- Payment Method --}}
                <div class="bg-white dark:bg-slate-900/60 rounded-3xl ring-1 ring-slate-200/50 dark:ring-slate-800/80 shadow-sm overflow-hidden">
                    <div class="p-6 sm:p-8">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-500 shrink-0">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" /></svg>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Metode Pembayaran</h2>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Pilih metode pembayaran yang Anda inginkan.</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-4">
                            <label class="relative flex items-center p-5 rounded-2xl border-2 transition-all duration-300 cursor-pointer group hover:shadow-md"
                                   :class="paymentMethod === 'pakasir' ? 'border-primary-500 bg-primary-50/50 dark:bg-primary-900/20' : 'border-slate-100 dark:border-slate-800 hover:border-slate-200 dark:hover:border-slate-700 bg-white dark:bg-slate-950/50'">
                                <input type="radio" name="payment_method_select" value="pakasir" class="hidden" @change="paymentMethod = 'pakasir'" :checked="paymentMethod === 'pakasir'">
                                
                                <div class="flex items-center gap-5 w-full">
                                    <div class="w-12 h-12 rounded-xl bg-white dark:bg-slate-900 border border-slate-150 dark:border-slate-800 p-2 flex items-center justify-center shrink-0 shadow-2xs">
                                        <img src="{{ asset('images/pakasir.png') }}" alt="Pakasir" class="w-full h-full object-contain">
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-base font-bold text-slate-900 dark:text-white">Pakasir (Otomatis)</p>
                                        <div class="flex flex-wrap gap-2 mt-2">
                                            <span class="text-[10px] font-bold uppercase tracking-wider bg-white dark:bg-slate-800 ring-1 ring-slate-200 dark:ring-slate-700 text-slate-600 dark:text-slate-300 px-2.5 py-1 rounded-md">QRIS / E-Wallet</span>
                                            <span class="text-[10px] font-bold uppercase tracking-wider bg-white dark:bg-slate-800 ring-1 ring-slate-200 dark:ring-slate-700 text-slate-600 dark:text-slate-300 px-2.5 py-1 rounded-md">Virtual Account</span>
                                        </div>
                                    </div>
                                    <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-300 shrink-0"
                                         :class="paymentMethod === 'pakasir' ? 'border-primary-500 bg-primary-500' : 'border-slate-300 dark:border-slate-600'">
                                        <svg x-show="paymentMethod === 'pakasir'" class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="bg-white dark:bg-slate-900/60 rounded-3xl ring-1 ring-slate-200/50 dark:ring-slate-800/80 shadow-sm p-6 sm:p-8">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Catatan Pesanan (Opsional)</h2>
                    <textarea name="notes" rows="3" placeholder="Misal: Tolong dibungkus rapi, atau jangan terlalu pedas..."
                              class="w-full px-5 py-4 rounded-xl border-2 border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/50 text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-0 focus:border-primary-500 dark:focus:border-primary-500 transition-colors resize-none"></textarea>
                </div>
            </div>

            {{-- Right Column: Summary --}}
            <div class="lg:col-span-5 xl:col-span-4">
                <div class="bg-white dark:bg-slate-900/80 rounded-3xl ring-1 ring-slate-200/50 dark:ring-slate-800/80 shadow-lg shadow-slate-200/30 dark:shadow-none p-6 sm:p-8 sticky top-28 xl:top-32">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-6">Ringkasan Belanja</h2>
                    
                    {{-- Scrollable Items --}}
                    <div class="space-y-4 mb-6 max-h-[320px] overflow-y-auto pr-2 custom-scrollbar">
                        @foreach($cartItems as $item)
                            <div class="flex items-center gap-4 group">
                                <div class="w-16 h-16 rounded-2xl bg-slate-50 dark:bg-slate-900 ring-1 ring-slate-100 dark:ring-slate-800 shrink-0 overflow-hidden relative">
                                    @if($item['image'])
                                        <img src="{{ asset('storage/' . $item['image']) }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-slate-100 dark:bg-slate-800 text-slate-400">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                                        </div>
                                    @endif
                                    <div class="absolute top-0 right-0 w-6 h-6 bg-slate-900/70 text-white text-[10px] font-bold flex items-center justify-center backdrop-blur-sm rounded-bl-xl">
                                        {{ $item['quantity'] }}x
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0 py-1">
                                    <p class="text-[13px] font-bold text-slate-800 dark:text-slate-200 truncate leading-tight">{{ $item['name'] }}</p>
                                    @if(!empty($item['variant_name']))
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Varian: <span class="font-semibold">{{ $item['variant_name'] }}</span></p>
                                    @endif
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                                </div>
                                <div class="text-sm font-black text-slate-900 dark:text-white shrink-0">
                                    Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Totals --}}
                    <div class="border-t border-dashed border-slate-200 dark:border-slate-700/60 pt-5 mt-2 space-y-3">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Subtotal Produk</span>
                            <span class="font-bold text-slate-700 dark:text-slate-300">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>

                        @if($discountAmount > 0)
                        <div class="flex justify-between items-center text-sm">
                            <div class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-bold tracking-tight">
                                <span>Potongan Kupon</span>
                                <span class="text-[9px] px-1.5 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-900/30 uppercase tracking-tighter">{{ $coupon['code'] }}</span>
                            </div>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">- Rp {{ number_format($discountAmount, 0, ',', '.') }}</span>
                        </div>
                        @endif

                        <div class="flex justify-between items-center text-sm">
                            <div class="flex items-center gap-1.5 text-slate-500 dark:text-slate-400 font-medium">
                                Biaya Pengiriman
                                <span x-show="shippingInfo" x-text="shippingInfo" class="text-[10px] font-bold uppercase tracking-wider bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 px-2 py-0.5 rounded-md" style="display:none;"></span>
                            </div>
                            <span class="font-bold text-slate-700 dark:text-slate-300">
                                <span x-show="shippingCost > 0" style="display:none;">Rp <span x-text="new Intl.NumberFormat('id-ID').format(shippingCost)"></span></span>
                                <span x-show="shippingCost === 0" class="text-xs text-amber-500 dark:text-amber-400 italic">Pilih pengiriman</span>
                            </span>
                        </div>
                    </div>

                    {{-- Grand Total --}}
                    <div class="bg-primary-50 dark:bg-primary-900/20 rounded-2xl p-5 mt-6 border border-primary-100 dark:border-primary-800/30">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-bold text-primary-900 dark:text-primary-100">Total Pembayaran</span>
                            <span class="text-xl font-black text-primary-600 dark:text-primary-400">Rp <span x-text="new Intl.NumberFormat('id-ID').format({{ $subtotal }} - {{ $discountAmount }} + (shippingCost || 0))"></span></span>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit" 
                        class="relative group overflow-hidden w-full mt-6 flex items-center justify-center gap-2 py-4 bg-primary-600 text-white font-bold rounded-2xl shadow-lg shadow-primary-500/30 dark:shadow-none hover:bg-primary-700 transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed" 
                        x-bind:disabled="shippingCost === 0 || !paymentMethod || loading"
                        :class="loading ? 'opacity-70 cursor-not-allowed' : ''">
                        <svg x-show="loading" class="animate-spin w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display:none;">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg x-show="!loading" class="relative z-10 w-5 h-5 transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3"/>
                        </svg>
                        <span class="relative z-10" x-text="loading ? 'Memproses Pesanan...' : 'Bayar Pesanan'">Bayar Pesanan</span>
                        <div x-show="!loading" class="absolute inset-0 h-full w-full bg-linear-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:animate-[shimmer_1.5s_infinite]"></div>
                    </button>

                    <div x-show="shippingCost === 0 || !paymentMethod" class="flex items-center gap-2 mt-4 p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50" style="display:none;">
                       <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <p class="text-[11px] font-bold leading-snug" x-text="!paymentMethod ? 'Pilih metode pembayaran terlebih dahulu.' : 'Selesaikan data alamat dan pengiriman untuk melanjutkan.'"></p>
                    </div>
                    
                    {{-- Guarantee / Security Badges --}}
                    <div class="flex items-center justify-center gap-4 mt-8 opacity-60">
                        <div class="flex items-center gap-1.5 grayscale">
                            <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z"/></svg>
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Garansi 100%</span>
                        </div>
                        <div class="flex items-center gap-1.5 grayscale">
                            <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Secure Pay</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Closed-shop confirmation. Not a blocker: the order is still welcome, the
         customer just gets to know the parcel waits for opening before they pay. --}}
    {{-- z-100 and centred on every screen, matching the other modals in the app.
         At z-[60] and anchored to the bottom on mobile it sat under the chat
         widget (z-50 but painted later) and clear of the sticky nav, which read
         as a panel stuck to the bottom of the page rather than a dialog. --}}
    <div x-show="showClosedModal" x-cloak
         class="fixed inset-0 z-100 flex items-center justify-center p-4"
         x-transition.opacity role="dialog" aria-modal="true" aria-labelledby="closedModalTitle">

        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showClosedModal = false"></div>

        <div class="relative w-full max-w-md max-h-[85vh] overflow-y-auto bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-2xl p-6 sm:p-7"
             @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <div class="flex items-start gap-4">
                <div class="w-11 h-11 rounded-2xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3 id="closedModalTitle" class="text-base font-black text-slate-900 dark:text-slate-100"
                        x-text="storeOpen ? 'Kurir Belum Bisa Menjemput' : 'Toko Sedang Tutup'">Toko Sedang Tutup</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1.5 leading-relaxed">
                        <template x-if="!storeOpen">
                            <span>Pesanan Kakak tetap kami terima dan langsung kami siapkan. Hanya saja kurir
                                baru bisa menjemput setelah toko buka, yaitu
                                <span class="font-bold text-slate-900 dark:text-slate-200" x-text="storeOpensAt"></span>.</span>
                        </template>
                        <template x-if="storeOpen">
                            <span>Pesanan Kakak tetap kami terima dan langsung kami siapkan. Hanya saja layanan
                                <span class="font-bold text-slate-900 dark:text-slate-200" x-text="shippingInfo"></span>
                                sudah melewati batas jemput hari ini, sehingga baru dijemput
                                <span class="font-bold text-slate-900 dark:text-slate-200" x-text="courierOpensAt"></span>.</span>
                        </template>
                    </p>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-2 leading-relaxed">
                        Lanjutkan pembayaran sekarang?
                    </p>
                </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row gap-2.5 mt-6">
                <button type="button" @click="showClosedModal = false"
                        class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all">
                    Nanti Saja
                </button>
                <button type="button" @click="confirmClosedOrder()"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold shadow-lg shadow-primary-500/20 transition-all">
                    Ya, Lanjutkan Bayar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function registerCheckoutFlow() {
    if (typeof Alpine !== 'undefined' && !Alpine.Components?.has?.('checkoutFlow')) {
        Alpine.data('checkoutFlow', () => ({
            addressId: '',
            shippingCost: 0,
            shippingCourier: '',
            shippingService: '',
            shippingInfo: '',
            paymentMethod: 'pakasir',
            loading: false,

            // Closed-shop confirmation. `storeOpen` is decided server-side so a
            // page left open across closing time still asks before charging.
            storeOpen: {{ $storeOpen ? 'true' : 'false' }},
            storeOpensAt: @json($storeOpensAt?->translatedFormat('l, d M') . ($storeOpensAt ? ' pukul '.$storeOpensAt->format('H:i').' WIB' : '')),
            // When set, the chosen courier is past its pickup cutoff today; filled
            // from the shipping selection so we can warn even while the shop is open.
            courierOpensAt: '',
            showClosedModal: false,
            closedAcknowledged: false,

            confirmClosedOrder() {
                this.closedAcknowledged = true;
                this.showClosedModal = false;
                this.loading = true;
                this.$refs.checkoutForm.submit();
            },

            init() {
                this.addressId = this.$el.dataset.addressId;
                
                window.addEventListener('addressSelected', (event) => {
                    this.addressId = event.detail.addressId;
                });

                window.addEventListener('shippingRateSelected', (event) => {
                    const data = event.detail.selection;
                    if (data) {
                        const [courier, service, price] = data.split('|');
                        this.shippingCourier = courier;
                        this.shippingService = service;
                        this.shippingCost = parseInt(price);
                        this.shippingInfo = `${courier.toUpperCase()} ${service}`;
                        this.courierOpensAt = event.detail.pickupOpensAt || '';
                    } else {
                        this.shippingCourier = '';
                        this.shippingService = '';
                        this.shippingCost = 0;
                        this.shippingInfo = '';
                        this.courierOpensAt = '';
                    }
                });
            }
        }));
    }
}

// Register on alpine:init (fresh page load)
document.addEventListener('alpine:init', registerCheckoutFlow);

// Register immediately if Alpine is already loaded
if (typeof Alpine !== 'undefined') {
    registerCheckoutFlow();
}
</script>
<style>
    /* Custom scrollbar for order items */
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 20px;
    }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #334155;
    }
</style>
@endpush
