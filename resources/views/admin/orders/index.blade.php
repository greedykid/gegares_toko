@extends('layouts.admin')
@section('page_title', 'Manajemen Pesanan')
@section('content')
@php
    $sort = request('sort', 'created_at');
    $dir = request('direction', 'desc');
    
    if (!function_exists('sortUrl')) {
        function sortUrl($column, $currentSort, $currentDir) {
            $nextDir = ($currentSort === $column && $currentDir === 'asc') ? 'desc' : 'asc';
            return request()->fullUrlWithQuery(['sort' => $column, 'direction' => $nextDir]);
        }
    }
@endphp
<div x-data="{ 
    showDetail: false, 
    selectedOrder: null,
    trackingData: null,
    loadingTracking: false,
    formatCurrency(val) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
    },
    submitProcessShipping(orderId) {
        const form = document.getElementById('process-shipping-form');
        // Named route → Indonesian path (/admin/pesanan/{id}/proses-pengiriman);
        // the old hardcoded /admin/orders/ path 404'd.
        form.action = '{{ route('admin.orders.process-shipping', ['order' => '__ID__']) }}'.replace('__ID__', orderId);
        form.submit();
    },
    submitMarkRefunded(orderId) {
        if (!confirm('Tandai pesanan ini sudah direfund? Pastikan dana benar-benar sudah dikembalikan ke pelanggan.')) return;
        const form = document.getElementById('mark-refunded-form');
        form.action = '{{ route('admin.orders.mark-refunded', ['order' => '__ID__']) }}'.replace('__ID__', orderId);
        form.submit();
    },
    submitCancelOrder(orderId) {
        if (!confirm('Batalkan pesanan ini? Jika sudah dibooking, pengiriman di Biteship juga dibatalkan. Tindakan ini tidak bisa dibatalkan.')) return;
        const reason = prompt('Alasan pembatalan (opsional):', 'Dibatalkan oleh admin') || 'Dibatalkan oleh admin';
        const form = document.getElementById('cancel-order-form');
        form.action = '{{ route('admin.orders.cancel-shipping', ['order' => '__ID__']) }}'.replace('__ID__', orderId);
        form.querySelector('[name=cancellation_reason]').value = reason;
        form.submit();
    },
    async fetchTracking() {
        if (!this.selectedOrder || !['processing', 'shipped', 'completed'].includes(this.selectedOrder.status)) {
            this.trackingData = null;
            return;
        }

        this.loadingTracking = true;
        try {
            const response = await fetch('{{ route('admin.orders.tracking', ['order' => '__ID__']) }}'.replace('__ID__', this.selectedOrder.id));
            const data = await response.json();
            if (data.success) {
                this.trackingData = data;
            } else {
                this.trackingData = null;
            }
        } catch (e) {
            console.error('Tracking fetch error:', e);
            this.trackingData = null;
        } finally {
            this.loadingTracking = false;
        }
    }
}" x-init="$watch('showDetail', value => { if (value) fetchTracking(); })">
    <div class="flex flex-col gap-4 mb-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Kelola Pesanan</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Pantau dan proses pesanan pelanggan toko Anda.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.orders.report', request()->all()) }}"
               target="_blank"
               class="px-4 py-2.5 bg-primary-600 text-white text-xs font-bold rounded-xl hover:bg-primary-700 transition-all flex items-center gap-2 duration-200">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.617 0-1.11-.461-1.12-1.078L6.34 18m11.32 0a1.152 1.152 0 0 0 1.059-1.086L19.5 8.25m-14 8.75a1.152 1.152 0 0 1-1.059-1.086L3.5 8.25m16 0a2.25 2.25 0 0 0-2.247-2.118H6.247A2.25 2.25 0 0 0 4 8.25m16 0V6a2.25 2.25 0 0 0-2.25-2.25h-7.5A2.25 2.25 0 0 0 8 6v2.25m4-3.037.01-.011m-.01.011-.01-.011m0 .011.011-.011" />
                </svg>
                Cetak Laporan PDF
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5 hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/40 flex items-center justify-center text-blue-600 dark:text-blue-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Total Pesanan</p>
                    <p class="text-xl font-extrabold text-slate-900 dark:text-slate-100 mt-0.5">{{ number_format($totalOrders) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5 hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-900/40 flex items-center justify-center text-amber-600 dark:text-amber-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-amber-500 dark:text-amber-400 uppercase tracking-widest">Pesanan Aktif</p>
                    <p class="text-xl font-extrabold text-slate-900 dark:text-slate-100 mt-0.5">{{ number_format($activeOrders) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5 hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-emerald-500 dark:text-emerald-400 uppercase tracking-widest">Selesai</p>
                    <p class="text-xl font-extrabold text-slate-900 dark:text-slate-100 mt-0.5">{{ number_format($completedOrders) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5 hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-indigo-500 dark:text-indigo-400 uppercase tracking-widest">Total Pendapatan</p>
                    <p class="text-xl font-extrabold text-slate-900 dark:text-slate-100 mt-0.5">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    @php
        $statusTab = request('status', '');
        $statusTabs = ['' => 'Semua', 'pending' => 'Menunggu', 'processing' => 'Diproses', 'shipped' => 'Dikirim', 'completed' => 'Selesai', 'cancelled' => 'Batal'];
    @endphp

    <div x-data="adminListView('orders')" :class="grid ? 'admin-grid-view' : ''" class="admin-list-card transition-all duration-300">
        {{-- Controls: search + compact Filter popover + view toggle (left), status quick-filter tabs (right) --}}
        <div class="flex flex-col-reverse gap-3 pb-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <form method="GET" action="{{ route('admin.orders.index') }}" class="relative flex flex-1 items-center gap-2 sm:flex-none" x-data="{ filterOpen: false, loading: false }">
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                    <input type="hidden" name="direction" value="{{ request('direction') }}">

                    {{-- Search (live AJAX filter) --}}
                    <div class="relative flex-1 min-w-0 sm:flex-none">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 dark:text-slate-500">
                            <svg x-show="!loading" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                            <svg x-show="loading" x-cloak class="w-4 h-4 animate-spin text-primary-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        </span>
                        <input type="text" name="search" data-live-search data-target="#ordersTable" value="{{ request('search') }}" placeholder="Cari pesanan..."
                               autocomplete="off"
                               class="w-full sm:w-80 lg:w-96 h-10 pl-10 pr-3 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all">
                    </div>

                    {{-- Filter button + popover --}}
                    <div class="relative shrink-0">
                        <button type="button" @click="filterOpen = !filterOpen"
                                class="inline-flex items-center gap-1.5 h-10 px-3.5 text-sm font-semibold rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z"/></svg>
                            <span>Filter</span>
                            @if(request()->anyFilled(['payment_status', 'from_date', 'to_date']))
                                <span class="w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                            @endif
                        </button>

                        <div x-show="filterOpen" x-cloak @click.outside="(e) => { if (!e.target || !e.target.closest || !e.target.closest('.flatpickr-calendar')) filterOpen = false; }"
                             x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             class="absolute right-0 sm:right-auto sm:left-0 top-full mt-2 z-50 w-[min(18rem,calc(100vw-2.5rem))] sm:w-80 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl p-3.5 space-y-3">
                            
                            {{-- Periode Tanggal --}}
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">Periode Tanggal</label>
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 dark:text-slate-500 z-10">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                        </svg>
                                    </div>
                                    <input type="text" id="date_range_picker" 
                                           placeholder="Pilih rentang tanggal..." 
                                           readonly
                                           class="w-full pl-10 pr-3.5 py-2 text-sm rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-colors cursor-pointer">
                                    <input type="hidden" name="from_date" id="from_date" value="{{ request('from_date') }}">
                                    <input type="hidden" name="to_date" id="to_date" value="{{ request('to_date') }}">
                                </div>
                            </div>

                            {{-- Status Pembayaran --}}
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">Status Pembayaran</label>
                                <div x-data="{ open:false, val:'{{ request('payment_status') }}', label:'{{ request('payment_status') == 'pending' ? 'Pending' : (request('payment_status') == 'paid' ? 'Dibayar' : (request('payment_status') == 'failed' ? 'Gagal' : (request('payment_status') == 'expired' ? 'Expired' : 'Semua Status Pembayaran'))) }}' }" class="relative">
                                    <input type="hidden" name="payment_status" :value="val">
                                    <button type="button" @click="open=!open"
                                            class="w-full flex items-center justify-between gap-2 px-3 py-2 text-sm rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 hover:border-primary-300 dark:hover:border-primary-700 transition-colors">
                                        <span x-text="label" class="truncate"></span>
                                        <svg class="w-4 h-4 shrink-0 text-slate-400 transition-transform duration-200" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                                    </button>
                                    <div x-show="open" x-cloak @click.outside="open=false" x-transition.opacity.duration.100ms
                                         class="absolute z-50 mt-1 w-full rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-lg py-1">
                                        <button type="button" @click="val='';label='Semua Status Pembayaran';open=false" class="w-full text-left px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors" :class="val==='' && 'text-primary-600 dark:text-primary-400 font-semibold'">Semua Status Pembayaran</button>
                                        <button type="button" @click="val='pending';label='Pending';open=false" class="w-full text-left px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors" :class="val==='pending' && 'text-primary-600 dark:text-primary-400 font-semibold'">Pending</button>
                                        <button type="button" @click="val='paid';label='Dibayar';open=false" class="w-full text-left px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors" :class="val==='paid' && 'text-primary-600 dark:text-primary-400 font-semibold'">Dibayar</button>
                                        <button type="button" @click="val='failed';label='Gagal';open=false" class="w-full text-left px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors" :class="val==='failed' && 'text-primary-600 dark:text-primary-400 font-semibold'">Gagal</button>
                                        <button type="button" @click="val='expired';label='Expired';open=false" class="w-full text-left px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors" :class="val==='expired' && 'text-primary-600 dark:text-primary-400 font-semibold'">Expired</button>
                                    </div>
                                </div>
                            </div>

                            <div class="flex gap-2 pt-1">
                                <button type="submit" class="flex-1 px-3 py-2 bg-primary-600 text-white text-sm font-bold rounded-lg hover:bg-primary-700 transition-colors">Terapkan</button>
                                @if(request()->anyFilled(['search', 'payment_status', 'from_date', 'to_date']))
                                    <a href="{{ route('admin.orders.index') }}" class="px-3 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-sm font-bold rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">Reset</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
                <div class="shrink-0">
                    @include('admin.partials.view-toggle')
                </div>
            </div>

            <div class="flex items-center gap-1 overflow-x-auto scrollbar-none -mx-1 px-1">
                @foreach($statusTabs as $val => $label)
                    <a href="{{ request()->fullUrlWithQuery(['status' => $val, 'page' => null]) }}"
                       class="shrink-0 px-3.5 py-1.5 rounded-lg text-sm font-semibold transition-colors {{ (string) $statusTab === (string) $val ? 'bg-primary-600 text-white' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>
    <div id="ordersTable">
        @include('admin.orders._table')
    </div>
</div>

    {{-- Detail Order Modal --}}
    <div x-show="showDetail" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display: none;"
         x-transition.opacity>
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showDetail = false"></div>
        
        <div x-show="showDetail"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             class="relative bg-white dark:bg-slate-900 rounded-3xl w-full max-w-2xl max-h-[90vh] overflow-hidden shadow-2xl flex flex-col border border-slate-200 dark:border-slate-800 transition-all duration-300">
            
            {{-- Modal Header --}}
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between shrink-0 bg-slate-50/50 dark:bg-slate-800/50 transition-colors">
                <div>
                    <h3 class="text-lg font-extrabold text-slate-900 dark:text-slate-100" x-text="selectedOrder?.order_number"></h3>
                    <p class="text-xs text-slate-500 font-medium mt-0.5" x-text="'Dipesan pada ' + new Date(selectedOrder?.created_at).toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'})"></p>
                </div>
                <button @click="showDetail = false" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="flex-1 overflow-y-auto p-6 space-y-6 custom-scrollbar">
                {{-- NEW: Prominent Tracking Number Bar (Solid) --}}
                <template x-if="selectedOrder?.tracking_number">
                    <div class="p-4 rounded-2xl bg-slate-950 dark:bg-black flex items-center justify-between overflow-hidden shadow-sm border border-slate-800 transition-colors">
                        <div>
                            <p class="text-[10px] font-black text-slate-450 dark:text-slate-550 uppercase tracking-[0.2em] mb-1 transition-colors">Nomor Resi Pelacakan</p>
                            <h2 class="text-xl font-mono font-black text-white tracking-wider select-all transition-colors" x-text="selectedOrder.tracking_number"></h2>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            <span class="px-2.5 py-1 rounded-lg bg-white/10 dark:bg-white/5 text-white text-[10px] font-bold uppercase tracking-widest transition-colors" x-text="selectedOrder?.shipping_courier?.toUpperCase() ?? 'KURIER'"></span>
                            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-700 uppercase transition-colors" x-text="selectedOrder?.shipping_service?.toUpperCase() ?? 'REGULAR'"></span>
                        </div>
                    </div>
                </template>

                {{-- Customer & Shipping --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <h4 class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest transition-colors">Informasi Pelanggan</h4>
                        <div class="p-4 rounded-2xl bg-slate-50/50 dark:bg-slate-950/30 border border-slate-100 dark:border-slate-800/80 shadow-sm transition-all duration-300">
                            <p class="text-sm font-extrabold text-slate-900 dark:text-slate-100 transition-colors" x-text="selectedOrder?.user?.name"></p>
                            <p class="text-xs text-slate-500 dark:text-slate-500 mt-0.5" x-text="selectedOrder?.user?.email"></p>
                            <template x-if="selectedOrder?.address?.phone">
                                <div class="flex items-center gap-2 mt-2">
                                    <p class="text-xs text-slate-500 dark:text-slate-500" x-text="selectedOrder.address.phone"></p>
                                    <a :href="`https://wa.me/${selectedOrder.address.phone.replace(/[^0-9]/g, '')}`" 
                                       target="_blank"
                                       class="p-1 rounded-md bg-emerald-100/50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-200/50 dark:hover:bg-emerald-900 transition-colors"
                                       title="Tanya via WA">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                                    </a>
                                </div>
                            </template>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <h4 class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest transition-colors">Alamat Pengiriman</h4>
                        <div class="p-4 rounded-2xl bg-slate-50/50 dark:bg-slate-950/30 border border-slate-100 dark:border-slate-800/80 shadow-sm transition-all duration-300">
                            <p class="text-sm font-extrabold text-slate-900 dark:text-slate-100 transition-colors" x-text="selectedOrder?.address?.recipient_name"></p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed transition-colors" 
                               x-text="`${selectedOrder?.address?.address_line}, ${selectedOrder?.address?.city}, ${selectedOrder?.address?.province} - ${selectedOrder?.address?.postal_code}`">
                            </p>
                            <div class="mt-3 inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-650 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900/30 text-[10px] font-extrabold uppercase tracking-tight transition-colors">
                                <span x-text="selectedOrder?.shipping_courier?.toUpperCase() ?? 'KURIER'"></span>
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-300 dark:bg-indigo-700"></span>
                                <span x-text="selectedOrder?.shipping_service?.toUpperCase() ?? 'REGULAR'"></span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <h4 class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest transition-colors">Metode Pembayaran</h4>
                        <div class="p-4 rounded-2xl bg-slate-50/50 dark:bg-slate-950/30 border border-slate-100 dark:border-slate-800/80 shadow-sm flex items-center justify-between transition-colors">
                            <span class="text-xs font-extrabold text-slate-900 dark:text-slate-100 uppercase tracking-wider transition-colors" x-text="selectedOrder?.payment_method?.toUpperCase() || 'PAKASIR'"></span>
                            <div class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-tighter transition-colors border"
                                 :class="{
                                     'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-650 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/30': selectedOrder?.payment_status === 'paid',
                                     'bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border-amber-100 dark:border-amber-900/30': selectedOrder?.payment_status === 'pending',
                                     'bg-red-50 dark:bg-red-950/50 text-red-655 dark:text-red-400 border-red-100 dark:border-red-900/30': selectedOrder?.payment_status === 'failed' || selectedOrder?.payment_status === 'expired',
                                     'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 border-slate-200 dark:border-slate-700': !selectedOrder?.payment_status
                                 }"
                                 x-text="selectedOrder?.payment_status === 'unpaid' ? 'BELUM BAYAR' : (selectedOrder?.payment_status?.toUpperCase() || 'BELUM BAYAR')"></div>
                        </div>
                    </div>

                    {{-- Customer's own note --}}
                    <template x-if="selectedOrder?.notes">
                        <div class="space-y-2">
                            <h4 class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest transition-colors">Catatan Pelanggan</h4>
                            <div class="p-4 rounded-2xl bg-slate-50/50 dark:bg-slate-950/30 border border-slate-100 dark:border-slate-800/80 shadow-sm transition-colors">
                                <p class="text-[11px] text-slate-650 dark:text-slate-350 leading-relaxed italic" x-text="`'${selectedOrder.notes}'`"></p>
                            </div>
                        </div>
                    </template>

                    {{-- System trail: written by the app only, so it can be trusted. --}}
                    <template x-if="selectedOrder?.admin_note">
                        <div class="space-y-2">
                            <h4 class="text-[10px] font-bold text-amber-500 dark:text-amber-400 uppercase tracking-widest transition-colors">Catatan Sistem</h4>
                            <div class="p-4 rounded-2xl bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-900/40 shadow-sm transition-colors">
                                <p class="text-[11px] font-semibold text-amber-700 dark:text-amber-400 leading-relaxed" x-text="selectedOrder.admin_note"></p>
                            </div>
                        </div>
                    </template>

                    {{-- NEW: Driver Info Section --}}
                    <template x-if="trackingData">
                        <div class="space-y-2 md:col-span-2">
                            <h4 class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest transition-colors">Informasi Kurir</h4>
                            <div class="p-4 rounded-2xl bg-slate-50/50 dark:bg-slate-950/30 border border-slate-100 dark:border-slate-800/80 shadow-sm relative overflow-hidden transition-all duration-300">
                                <div class="flex items-center gap-4 relative z-10">
                                    <div class="w-12 h-12 rounded-xl border-2 border-white dark:border-slate-800 shadow-sm overflow-hidden bg-white dark:bg-slate-900 shrink-0 transition-colors">
                                        {{-- Biteship does not always send a photo; the tile stays empty rather than showing a stand-in face. --}}
                                        <img x-show="trackingData.courier.photo" :src="trackingData.courier.photo" class="w-full h-full object-cover">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-extrabold text-slate-900 dark:text-slate-100 truncate transition-colors" x-text="trackingData.courier.name"></p>
                                            <span class="px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/50 text-emerald-650 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/30 text-[9px] font-black uppercase tracking-tighter transition-colors" x-text="trackingData.courier.plate_number"></span>
                                        </div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <div class="flex items-center gap-1.5 text-[10px] font-bold text-slate-500 dark:text-slate-400 transition-colors">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" /></svg>
                                                <span x-text="trackingData.courier.phone"></span>
                                                <a :href="`https://wa.me/${trackingData.courier.phone.replace(/[^0-9]/g, '')}`" 
                                                   target="_blank"
                                                   class="p-0.5 rounded bg-emerald-100/50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-200/50 dark:hover:bg-emerald-900 transition-colors"
                                                   title="Hubungi Kurir via WA">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                                                 </a>
                                             </div>
                                             <span class="w-1.5 h-1.5 rounded-full bg-slate-200 dark:bg-slate-800 transition-colors"></span>
                                             <p class="text-[10px] font-black text-emerald-700 dark:text-emerald-400 uppercase transition-colors">STATUS: <span x-text="trackingData.status_label || 'SEDANG DIPROSES'"></span></p>
                                         </div>
                                     </div>
                                     <div class="shrink-0 ml-2 flex flex-col gap-2">
                                         <a :href="trackingData.link" target="_blank" 
                                            class="p-2 rounded-xl bg-white dark:bg-slate-800 text-emerald-600 dark:text-emerald-400 shadow-sm border border-slate-100 dark:border-slate-800/80 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 transition-all duration-200"
                                            title="Lacak Live">
                                             <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                                         </a>
                                         <button type="button" @click="fetchTracking()" 
                                                 class="p-2 rounded-xl bg-white dark:bg-slate-800 text-slate-400 dark:text-slate-500 shadow-sm border border-slate-100 dark:border-slate-800/80 hover:text-primary-600 dark:hover:text-primary-400 transition-all duration-200"
                                                 title="Sinkronkan Status">
                                             <svg class="w-5 h-5" :class="{ 'animate-spin': loadingTracking }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                                         </button>
                                     </div>
                                 </div>
                                 {{-- Decorative Background Icon --}}
                                 <div class="absolute -right-4 -bottom-4 opacity-[0.03] dark:opacity-[0.07] rotate-12 transition-opacity">
                                     <svg class="w-24 h-24 text-slate-900 dark:text-slate-400" fill="currentColor" viewBox="0 0 24 24"><path d="M19,10H17V7c0-1.1-.9-2-2-2H9C7.9,5,7,5.9,7,7v3H5c-1.1,0-2,.9-2,2v5h2c0,1.66,1.34,3,3,3s3-1.34,3-3h2c0,1.66,1.34,3,3,3s3-1.34,3-3h2v-5C21,10.9,20.1,10,19,10z M8,18c-0.55,0-1-0.45-1-1s0.45-1,1-1s1,0.45,1,1S8.55,18,8,18z M17,18c-0.55,0-1-0.45-1-1s0.45-1,1-1s1,0.45,1,1S17.55,18,17,18z M19,13h-4v-2h4V13z"/></svg>
                                 </div>
                             </div>

                             {{-- Shipment History Timeline --}}
                             <div class="mt-4 border-l-2 border-emerald-100 dark:border-emerald-900/50 ml-6 pl-6 space-y-4 transition-colors">
                                 <template x-for="(event, index) in trackingData.history" :key="index + '-' + event.status">
                                     <div class="relative">
                                         <div class="absolute -left-[31px] top-1.5 w-2.5 h-2.5 rounded-full bg-emerald-500 dark:bg-emerald-600 border-2 border-white dark:border-slate-900 shadow-sm transition-all duration-300"></div>
                                         <div class="flex flex-col">
                                             <p class="text-[10px] font-black text-emerald-800 dark:text-emerald-300 uppercase tracking-tighter transition-colors" x-text="event.note"></p>
                                             <p class="text-[9px] text-slate-400 dark:text-slate-600 font-medium transition-colors" x-text="event.time"></p>
                                         </div>
                                     </div>
                                 </template>
                             </div>
                         </div>
                     </template>
                </div>

                {{-- Order Items --}}
                <div class="space-y-3">
                    <h4 class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest transition-colors">Detail Produk</h4>
                    {{-- Scrolls inside its own box on narrow screens instead of
                         clipping the Total column. --}}
                    <div class="rounded-2xl border border-slate-100 dark:border-slate-800 overflow-x-auto transition-all duration-300">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-800/50">
                                <tr>
                                    <th class="px-3 sm:px-4 py-2 text-left font-bold text-slate-600 dark:text-slate-400 text-xs transition-colors">Produk</th>
                                    <th class="px-3 sm:px-4 py-2 text-center font-bold text-slate-600 dark:text-slate-400 text-xs w-16 whitespace-nowrap transition-colors">Qty</th>
                                    <th class="px-3 sm:px-4 py-2 text-right font-bold text-slate-600 dark:text-slate-400 text-xs whitespace-nowrap transition-colors">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                                <template x-for="item in selectedOrder?.items" :key="item.id">
                                    <tr>
                                        <td class="px-3 sm:px-4 py-3 min-w-[170px]">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-lg overflow-hidden shrink-0 border border-slate-100 bg-slate-50">
                                                    <img :src="item.product?.image ? '/storage/' + item.product.image : 'https://placehold.co/100x100?text=' + encodeURIComponent(item.product_name)" 
                                                         class="w-full h-full object-cover"
                                                         :alt="item.product_name">
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="font-bold text-slate-900 dark:text-slate-100 truncate transition-colors" x-text="item.product_name"></p>
                                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5 transition-colors" x-text="formatCurrency(item.product_price)"></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 sm:px-4 py-3 text-center font-medium text-slate-600 dark:text-slate-400 whitespace-nowrap transition-colors" x-text="item.quantity"></td>
                                        <td class="px-3 sm:px-4 py-3 text-right font-bold text-slate-900 dark:text-slate-100 whitespace-nowrap transition-colors" x-text="formatCurrency(item.subtotal)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Cost Summary --}}
                <div class="flex justify-end pt-4">
                    <div class="w-full max-w-[240px] space-y-3">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-500 dark:text-slate-500 transition-colors">Subtotal</span>
                            <span class="font-bold text-slate-900 dark:text-slate-100 transition-colors" x-text="formatCurrency(selectedOrder?.subtotal || 0)"></span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-500 dark:text-slate-500 transition-colors">Ongkos Kirim</span>
                            <span class="font-bold text-slate-900 dark:text-slate-100 transition-colors" x-text="formatCurrency(selectedOrder?.shipping_cost || 0)"></span>
                        </div>
                        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 transition-colors flex justify-between items-center">
                            <span class="text-base font-extrabold text-slate-900 dark:text-slate-100 transition-colors">Total</span>
                            <span class="text-base font-extrabold text-primary-600 dark:text-primary-400 transition-colors" x-text="formatCurrency(selectedOrder?.total || 0)"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            {{-- Stacks on mobile so the status and the actions each get a full row. --}}
            <div class="px-4 sm:px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 transition-colors flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shrink-0">
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest transition-colors">Status:</span>
                        <span class="px-3 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-widest bg-white dark:bg-slate-950 shadow-sm border border-slate-100 dark:border-slate-800 transition-all" 
                              x-text="selectedOrder?.status_label"
                              :class="{
                                  'text-emerald-600 dark:text-emerald-400': selectedOrder?.status === 'completed',
                                  'text-amber-500 dark:text-amber-400': selectedOrder?.status === 'processing' || selectedOrder?.status === 'shipped',
                                  'text-orange-500 dark:text-orange-400': selectedOrder?.status === 'pending',
                                  'text-red-500 dark:text-red-400': selectedOrder?.status === 'cancelled'
                              }"></span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 sm:gap-3 w-full sm:w-auto">
                    {{-- Courier booking is automatic once payment settles. These two
                         buttons are the admin escape hatches when it did not work:
                         re-book when the booking never landed, re-search when Biteship
                         booked it but could not find a driver. --}}
                    <template x-if="selectedOrder?.status === 'processing' && !selectedOrder?.biteship_order_id">
                        <button type="button"
                                @click="submitProcessShipping(selectedOrder.id)"
                                class="flex-1 sm:flex-none justify-center whitespace-nowrap px-3 py-2 sm:px-5 bg-indigo-600 text-white text-[11px] sm:text-sm font-bold rounded-xl hover:bg-indigo-700 transition-all shadow-sm shadow-indigo-200 flex items-center gap-1.5 sm:gap-2">
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0 1 21.485 12 59.77 59.77 0 0 1 3.27 20.876L5.999 12zm0 0h7.5" /></svg>
                            Booking Ulang ke Biteship
                        </button>
                    </template>

                    <template x-if="selectedOrder?.status === 'processing' && trackingData?.status === 'courier_not_found'">
                        <button type="button"
                                @click="submitProcessShipping(selectedOrder.id)"
                                class="flex-1 sm:flex-none justify-center whitespace-nowrap px-3 py-2 sm:px-5 bg-emerald-600 text-white text-[11px] sm:text-sm font-bold rounded-xl hover:bg-emerald-700 transition-all shadow-sm shadow-emerald-200 flex items-center gap-1.5 sm:gap-2">
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                            Cari Ulang Kurir
                        </button>
                    </template>

                    {{-- Cancelled but already paid: the shop is still holding the
                         customer's money until someone records the refund. --}}
                    <template x-if="selectedOrder?.status === 'cancelled' && selectedOrder?.payment_status === 'paid' && !selectedOrder?.refunded_at">
                        <button type="button"
                                @click="submitMarkRefunded(selectedOrder.id)"
                                class="flex-1 sm:flex-none justify-center whitespace-nowrap px-3 py-2 sm:px-5 bg-amber-500 text-white text-[11px] sm:text-sm font-bold rounded-xl hover:bg-amber-600 transition-all shadow-sm shadow-amber-200 flex items-center gap-1.5 sm:gap-2">
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
                            Tandai Sudah Direfund
                        </button>
                    </template>

                    {{-- Cancel: the one real courier action Biteship's API exposes.
                         Only orders still in fulfilment (processing/shipped) can be cancelled. --}}
                    <template x-if="['processing', 'shipped'].includes(selectedOrder?.status)">
                        <button type="button"
                                @click="submitCancelOrder(selectedOrder.id)"
                                class="flex-1 sm:flex-none justify-center whitespace-nowrap px-3 py-2 sm:px-5 bg-red-600 text-white text-[11px] sm:text-sm font-bold rounded-xl hover:bg-red-700 transition-all shadow-sm shadow-red-200 flex items-center gap-1.5 sm:gap-2">
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                            Batalkan Pesanan
                        </button>
                    </template>
                    <button @click="showDetail = false"
                            class="flex-1 sm:flex-none whitespace-nowrap px-3 py-2 sm:px-5 bg-slate-900 dark:bg-slate-800 text-white text-[11px] sm:text-sm font-bold rounded-xl hover:bg-slate-800 dark:hover:bg-slate-700 transition-all duration-200">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Hidden form for Biteship shipping process --}}
    <form id="process-shipping-form" method="POST" style="display: none;">
        @csrf
    </form>

    {{-- Hidden form for cancelling an order (and its Biteship shipment) --}}
    <form id="cancel-order-form" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="cancellation_reason" value="">
    </form>

    {{-- Hidden form for recording a refund --}}
    <form id="mark-refunded-form" method="POST" style="display: none;">
        @csrf @method('PATCH')
    </form>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/flatpickr/dist/flatpickr.min.css">
<style>
    /* Custom styling for flatpickr calendar */
    .flatpickr-calendar {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
        border-radius: 1rem !important;
        font-family: 'Quicksand', sans-serif !important;
    }
    .dark .flatpickr-calendar {
        background: #0f172a !important; /* bg-slate-900 */
        border: 1px solid #1e293b !important; /* border-slate-800 */
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.3) !important;
    }
    .flatpickr-months .flatpickr-month {
        color: #0f172a !important;
        background: transparent !important;
    }
    .dark .flatpickr-months .flatpickr-month {
        color: #f1f5f9 !important;
        background: transparent !important;
    }
    .flatpickr-current-month .flatpickr-monthDropdown-months {
        font-weight: 700 !important;
        color: inherit !important;
        background: transparent !important;
        border: none !important;
        border-radius: 0.375rem !important;
        padding: 2px 6px !important;
        cursor: pointer !important;
    }
    .flatpickr-current-month .flatpickr-monthDropdown-months:hover {
        background: rgba(0, 0, 0, 0.05) !important;
    }
    .dark .flatpickr-current-month .flatpickr-monthDropdown-months:hover {
        background: rgba(255, 255, 255, 0.05) !important;
    }
    .flatpickr-monthDropdown-months option {
        background-color: #ffffff !important;
        color: #0f172a !important;
    }
    .dark .flatpickr-monthDropdown-months option {
        background-color: #0f172a !important;
        color: #cbd5e1 !important;
    }
    .flatpickr-current-month input.cur-year {
        font-weight: 700 !important;
        color: inherit !important;
        background: transparent !important;
        border-radius: 0.375rem !important;
        padding: 2px 6px !important;
    }
    .flatpickr-current-month input.cur-year:hover {
        background: rgba(0, 0, 0, 0.05) !important;
    }
    .dark .flatpickr-current-month input.cur-year:hover {
        background: rgba(255, 255, 255, 0.05) !important;
    }
    .flatpickr-months .flatpickr-prev-month, .flatpickr-months .flatpickr-next-month {
        color: #475569 !important;
        fill: currentColor !important;
    }
    .dark .flatpickr-months .flatpickr-prev-month, .dark .flatpickr-months .flatpickr-next-month {
        color: #cbd5e1 !important;
        fill: currentColor !important;
    }
    .flatpickr-weekday {
        font-weight: 700 !important;
        color: #94a3b8 !important;
    }
    .flatpickr-day {
        color: #334155 !important;
        border-radius: 0.5rem !important;
        font-weight: 600 !important;
    }
    .dark .flatpickr-day {
        color: #cbd5e1 !important;
    }
    .flatpickr-day:hover {
        background: #f1f5f9 !important;
    }
    .dark .flatpickr-day:hover {
        background: #1e293b !important;
    }
    .flatpickr-day.today {
        border-color: #0a5050 !important;
        color: #0a5050 !important;
    }
    .dark .flatpickr-day.today {
        border-color: #337373 !important;
        color: #337373 !important;
    }
    .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange {
        background: #0a5050 !important;
        border-color: #0a5050 !important;
        color: #ffffff !important;
    }
    .dark .flatpickr-day.selected, .dark .flatpickr-day.startRange, .dark .flatpickr-day.endRange {
        background: #0a5050 !important;
        border-color: #0a5050 !important;
        color: #ffffff !important;
    }
    .flatpickr-day.selected:hover, .flatpickr-day.startRange:hover, .flatpickr-day.endRange:hover {
        background: #084040 !important;
        border-color: #084040 !important;
    }
    .flatpickr-day.inRange {
        background: rgba(10, 80, 80, 0.1) !important;
        box-shadow: none !important;
        border-radius: 0 !important;
    }
    .dark .flatpickr-day.inRange {
        background: rgba(10, 80, 80, 0.2) !important;
        box-shadow: none !important;
        border-radius: 0 !important;
    }
    .flatpickr-day.flatpickr-disabled, .flatpickr-day.notAllowed {
        color: #cbd5e1 !important;
    }
    .dark .flatpickr-day.flatpickr-disabled, .dark .flatpickr-day.notAllowed {
        color: #475569 !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/flatpickr/dist/flatpickr.min.js"></script>
<script src="https://unpkg.com/flatpickr/dist/l10n/id.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const fromDateEl = document.getElementById('from_date');
        const toDateEl = document.getElementById('to_date');
        const dateRangeInput = document.getElementById('date_range_picker');

        // Initial date values
        let defaultDates = [];
        if (fromDateEl.value) {
            defaultDates.push(fromDateEl.value);
        }
        if (toDateEl.value) {
            defaultDates.push(toDateEl.value);
        }

        flatpickr(dateRangeInput, {
            mode: 'range',
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'j M Y',
            locale: 'id',
            defaultDate: defaultDates,
            onClose: function(selectedDates, dateStr, instance) {
                if (selectedDates.length === 2) {
                    // Set values to hidden inputs
                    fromDateEl.value = instance.formatDate(selectedDates[0], 'Y-m-d');
                    toDateEl.value = instance.formatDate(selectedDates[1], 'Y-m-d');
                } else if (selectedDates.length === 1) {
                    // If only one date is selected, set it for both (or handle accordingly)
                    fromDateEl.value = instance.formatDate(selectedDates[0], 'Y-m-d');
                    toDateEl.value = instance.formatDate(selectedDates[0], 'Y-m-d');
                } else {
                    fromDateEl.value = '';
                    toDateEl.value = '';
                }
            }
        });
    });
</script>
@endpush
