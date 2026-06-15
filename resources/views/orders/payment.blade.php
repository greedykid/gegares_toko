@extends('layouts.app')
@section('title', 'Pembayaran')
@section('content')
    <div class="max-w-lg mx-auto px-4 py-12 lg:py-20 text-center">
        @if($order->payment_status === 'paid')
            {{-- Success State --}}
            <div
                class="bg-white dark:bg-slate-900/60 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-2xl p-8 sm:p-12 relative overflow-hidden ring-1 ring-slate-200/50 dark:ring-slate-800/80">
                {{-- Success Confetti Line --}}
                <div class="absolute top-0 left-0 w-full h-2 bg-linear-to-r from-emerald-300 via-emerald-500 to-teal-500"></div>

                <div
                    class="w-24 h-24 mx-auto rounded-full bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center mb-8 relative shadow-inner">
                    <div class="absolute inset-0 rounded-full bg-emerald-100 dark:bg-emerald-800/20 animate-ping opacity-30">
                    </div>
                    <div class="absolute inset-2 rounded-full bg-emerald-100 dark:bg-emerald-800/40"></div>
                    <svg class="w-12 h-12 text-emerald-500 relative z-10 drop-shadow-sm" fill="none" viewBox="0 0 24 24"
                        stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                </div>

                <h1 class="text-3xl font-black text-slate-900 dark:text-white mb-3 tracking-tight">Pembayaran Berhasil!</h1>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-8 leading-relaxed">Terima kasih banyak,
                    pesanan Anda <strong class="text-slate-700 dark:text-slate-300">{{ $order->order_number }}</strong> sudah
                    kami terima dan mulai diproses untuk segera dikirim.</p>

                <div class="space-y-4">
                    <a href="{{ route('orders.show', $order) }}"
                        class="flex items-center justify-center gap-2.5 w-full py-4 bg-primary-600 text-white font-bold rounded-2xl dark:shadow-none hover:bg-primary-700 transition-all transform hover:-translate-y-0.5 active:scale-95 group">
                        Cek Detail Pesanan
                        <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24"
                            stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3" />
                        </svg>
                    </a>
                    <a href="{{ route('home') }}"
                        class="flex items-center justify-center gap-2.5 w-full py-4 bg-transparent text-slate-600 dark:text-slate-400 font-bold rounded-2xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all active:scale-95">
                        Kembali Belanja
                    </a>
                </div>
            </div>
        @else
            {{-- Waiting State --}}
            <div
                class="bg-white dark:bg-slate-900/60 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-2xl p-8 sm:p-10 ring-1 ring-slate-200/50 dark:ring-slate-800/80 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1.5 bg-linear-to-r from-amber-300 via-amber-500 to-orange-400"></div>

                <div
                    class="w-20 h-20 mx-auto rounded-3xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center mb-6 shadow-inner ring-1 ring-amber-100 dark:ring-amber-900/50 relative">
                    <div class="absolute inset-0 rounded-3xl bg-amber-100 flex dark:bg-amber-800/20 animate-ping opacity-20">
                    </div>
                    <svg class="w-10 h-10 text-amber-500 drop-shadow-sm" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>

                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white mb-2 tracking-tight">Penyelesaian Pembayaran
                </h1>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Silakan lanjut ke proses pembayaran agar
                    pesanan Anda dapat segera kami siapkan.</p>

                <div
                    class="mt-8 p-6 rounded-2xl bg-slate-50/80 dark:bg-slate-950/40 border border-slate-200/60 dark:border-slate-800 ring-1 ring-slate-100/50 dark:ring-slate-800/50 backdrop-blur-sm">
                    <p class="text-[11px] text-slate-400 dark:text-slate-500 uppercase tracking-widest font-black mb-1.5">No.
                        Pesanan</p>
                    <div class="flex items-center justify-center gap-2">
                        <p class="text-lg font-bold text-slate-700 dark:text-slate-200">{{ $order->order_number }}</p>
                        <div
                            class="px-2 py-0.5 rounded pl-1.5 border border-amber-200 dark:border-amber-900/50 bg-amber-50 dark:bg-amber-900/20">
                            <span class="w-1.5 h-1.5 inline-block mr-0.5 rounded-full bg-amber-500 animate-pulse"></span>
                            <span class="text-[10px] uppercase font-bold text-amber-600 dark:text-amber-500">Belum Bayar</span>
                        </div>
                    </div>

                    <div class="w-full h-px bg-dashed border-t border-dashed border-slate-200 dark:border-slate-700 my-5"></div>

                    <p class="text-[11px] text-slate-400 dark:text-slate-500 uppercase tracking-widest font-black mb-1">Total
                        Tagihan</p>
                    <p class="text-4xl font-black text-primary-600 dark:text-primary-400 tracking-tight">
                        {{ $order->formatted_total }}</p>
                </div>

                @if($order->pakasir_link)
                    <a href="{{ $order->pakasir_link }}"
                        class="group relative w-full mt-8 flex items-center justify-center gap-2 py-4 bg-primary-600 text-white font-bold rounded-2xl shadow-xl shadow-primary-500/30 dark:shadow-none hover:bg-primary-700 transition-all transform hover:-translate-y-0.5 active:scale-95 overflow-hidden">
                        <span class="relative z-10">Lanjutkan Pembayaran Sekarang</span>
                        <svg class="relative z-10 w-5 h-5 transition-transform group-hover:translate-x-1" fill="none"
                            viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                        <div
                            class="absolute inset-0 h-full w-full bg-linear-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:animate-[shimmer_1.5s_infinite]">
                        </div>
                    </a>

                    <button x-data="{ loading: false }"
                        @click="loading = true; window.location.href = '{{ route('orders.payment', $order) }}?t=' + Date.now();"
                        :disabled="loading"
                        :class="loading ? 'opacity-70 cursor-not-allowed' : ''"
                        class="w-full mt-3 flex items-center justify-center gap-2 py-3.5 bg-slate-100 dark:bg-slate-800/60 text-slate-700 dark:text-slate-300 font-bold rounded-2xl border border-slate-200/60 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all active:scale-95">
                        <svg x-show="loading" class="animate-spin w-4.5 h-4.5 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display:none;">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg x-show="!loading" class="w-4.5 h-4.5 text-slate-500 animate-spin-hover" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        <span x-text="loading ? 'Memverifikasi...' : 'Saya Sudah Bayar (Cek Status)'">Saya Sudah Bayar (Cek Status)</span>
                    </button>
                @endif

                <a href="{{ route('orders.index') }}"
                    class="inline-flex items-center gap-1.5 mt-6 text-sm font-bold text-slate-400 dark:text-slate-500 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Kembali ke Pesanan
                </a>
            </div>
        @endif
    </div>

    @if($order->payment_status !== 'paid')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Reload the page if loaded via back-forward cache (bfcache)
                window.addEventListener('pageshow', function(event) {
                    if (event.persisted) {
                        window.location.reload();
                    }
                });
            });
        </script>
    @endif
@endsection