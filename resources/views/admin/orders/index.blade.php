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
    <div class="flex justify-end mb-6">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.orders.report', request()->all()) }}" 
               target="_blank"
               class="px-4 py-2.5 bg-primary-600 text-white text-xs font-bold rounded-xl hover:bg-primary-700 hover:shadow-md hover:shadow-primary-600/10 transition-all flex items-center gap-2 transform hover:-translate-y-0.5 duration-200">
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

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-4 mb-6 transition-all duration-300">
        <form action="{{ route('admin.orders.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-7 gap-4 items-end">
            <div class="lg:col-span-2">
                <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5 ml-1">Cari Pesanan</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="No. Pesanan / Nama..." 
                           class="w-full pl-10 pr-4 py-2 text-sm border border-slate-200 dark:border-slate-800 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all outline-none bg-slate-50/30 dark:bg-slate-950/50 text-slate-900 dark:text-slate-100">
                </div>
            </div>
            
            <div class="lg:col-span-2">
                <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5 ml-1">Periode</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                    </div>
                    <input type="text" id="date_range_picker" 
                           placeholder="Pilih rentang tanggal..." 
                           readonly
                           class="w-full pl-10 pr-4 py-2 text-sm border border-slate-200 dark:border-slate-800 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all outline-none bg-slate-50/30 dark:bg-slate-950/50 text-slate-900 dark:text-slate-100 cursor-pointer">
                    <input type="hidden" name="from_date" id="from_date" value="{{ request('from_date') }}">
                    <input type="hidden" name="to_date" id="to_date" value="{{ request('to_date') }}">
                </div>
            </div>

            <div class="lg:col-span-1">
                <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5 ml-1">Status</label>
                <div x-data="{ 
                        open: false, 
                        selectedValue: '{{ request('status') }}', 
                        selectedLabel: '{{ request('status') == 'pending' ? 'Menunggu' : (request('status') == 'paid' ? 'Dibayar' : (request('status') == 'processing' ? 'Diproses' : (request('status') == 'shipped' ? 'Dikirim' : (request('status') == 'completed' ? 'Selesai' : (request('status') == 'cancelled' ? 'Batal' : 'Semua'))))) }}'
                     }" 
                     class="relative w-full">
                    <input type="hidden" name="status" :value="selectedValue">
                    <button @click="open = !open" type="button" 
                            class="w-full flex items-center justify-between pl-3 pr-3 py-2 text-sm border border-slate-200 dark:border-slate-800 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all outline-none bg-slate-50/30 dark:bg-slate-950/50 text-slate-900 dark:text-slate-100 cursor-pointer">
                        <span x-text="selectedLabel"></span>
                        <svg class="h-4 w-4 text-slate-400 dark:text-slate-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open" 
                         @click.outside="open = false" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl py-1 overflow-hidden"
                         style="display: none;">
                        <button type="button" @click="selectedValue = ''; selectedLabel = 'Semua'; open = false"
                                class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                :class="selectedValue === '' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">Semua</button>
                        <button type="button" @click="selectedValue = 'pending'; selectedLabel = 'Menunggu'; open = false"
                                class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                :class="selectedValue === 'pending' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">Menunggu</button>
                        <button type="button" @click="selectedValue = 'paid'; selectedLabel = 'Dibayar'; open = false"
                                class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                :class="selectedValue === 'paid' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">Dibayar</button>
                        <button type="button" @click="selectedValue = 'processing'; selectedLabel = 'Diproses'; open = false"
                                class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                :class="selectedValue === 'processing' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">Diproses</button>
                        <button type="button" @click="selectedValue = 'shipped'; selectedLabel = 'Dikirim'; open = false"
                                class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                :class="selectedValue === 'shipped' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">Dikirim</button>
                        <button type="button" @click="selectedValue = 'completed'; selectedLabel = 'Selesai'; open = false"
                                class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                :class="selectedValue === 'completed' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">Selesai</button>
                        <button type="button" @click="selectedValue = 'cancelled'; selectedLabel = 'Batal'; open = false"
                                class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                :class="selectedValue === 'cancelled' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">Batal</button>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5 ml-1">Bayar</label>
                <div x-data="{ 
                        open: false, 
                        selectedValue: '{{ request('payment_status') }}', 
                        selectedLabel: '{{ request('payment_status') == 'pending' ? 'Pending' : (request('payment_status') == 'paid' ? 'Dibayar' : (request('payment_status') == 'failed' ? 'Gagal' : (request('payment_status') == 'expired' ? 'Expired' : 'Semua'))) }}'
                     }" 
                     class="relative w-full">
                    <input type="hidden" name="payment_status" :value="selectedValue">
                    <button @click="open = !open" type="button" 
                            class="w-full flex items-center justify-between pl-3 pr-3 py-2 text-sm border border-slate-200 dark:border-slate-800 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all outline-none bg-slate-50/30 dark:bg-slate-950/50 text-slate-900 dark:text-slate-100 cursor-pointer">
                        <span x-text="selectedLabel"></span>
                        <svg class="h-4 w-4 text-slate-400 dark:text-slate-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open" 
                         @click.outside="open = false" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl py-1 overflow-hidden"
                         style="display: none;">
                        <button type="button" @click="selectedValue = ''; selectedLabel = 'Semua'; open = false"
                                class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                :class="selectedValue === '' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">Semua</button>
                        <button type="button" @click="selectedValue = 'pending'; selectedLabel = 'Pending'; open = false"
                                class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                :class="selectedValue === 'pending' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">Pending</button>
                        <button type="button" @click="selectedValue = 'paid'; selectedLabel = 'Dibayar'; open = false"
                                class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                :class="selectedValue === 'paid' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">Dibayar</button>
                        <button type="button" @click="selectedValue = 'failed'; selectedLabel = 'Gagal'; open = false"
                                class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                :class="selectedValue === 'failed' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">Gagal</button>
                        <button type="button" @click="selectedValue = 'expired'; selectedLabel = 'Expired'; open = false"
                                class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                :class="selectedValue === 'expired' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">Expired</button>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1 flex items-center gap-2">
                <button type="submit" class="flex-1 px-5 py-2 bg-slate-800 dark:bg-slate-700 text-white text-sm font-bold rounded-xl hover:bg-slate-900 dark:hover:bg-slate-600 shadow-sm transition-all whitespace-nowrap duration-200">Filter</button>
                @if(request()->anyFilled(['search', 'status', 'payment_status', 'from_date', 'to_date']))
                <a href="{{ route('admin.orders.index') }}" class="px-3 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-sm font-bold rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-all flex items-center justify-center duration-200" title="Reset">
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                </a>
                @endif
            </div>
        </form>
    </div>
    <div class="flex items-center justify-between mb-4 px-1">
        <div class="flex items-center gap-2">
            <span class="text-sm font-medium text-slate-500">Menampilkan {{ $orders->total() }} pesanan</span>
            @if(request()->anyFilled(['search', 'status', 'payment_status', 'from_date', 'to_date']))
                <span class="text-slate-300">|</span>
                <div class="flex flex-wrap items-center gap-1.5">
                    @if(request('search'))
                        <span class="px-2 py-0.5 bg-primary-50 text-primary-600 text-[10px] font-bold rounded-lg border border-primary-100 italic">"{{ request('search') }}"</span>
                    @endif
                    @if(request('status'))
                        <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-[10px] font-bold rounded-lg border border-slate-200">Status: {{ ucfirst(request('status')) }}</span>
                    @endif
                    @if(request('payment_status'))
                        <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-[10px] font-bold rounded-lg border border-slate-200">Bayar: {{ ucfirst(request('payment_status')) }}</span>
                    @endif
                    @if(request('from_date') || request('to_date'))
                        <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-[10px] font-bold rounded-lg border border-slate-200">
                            {{ request('from_date') ? \Carbon\Carbon::parse(request('from_date'))->format('d/m/Y') : '...' }} - 
                            {{ request('to_date') ? \Carbon\Carbon::parse(request('to_date'))->format('d/m/Y') : '...' }}
                        </span>
                    @endif
                </div>
            @endif
        </div>
    </div>
<div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden transition-all duration-300">
    <div class="overflow-x-auto lg:overflow-x-visible custom-scrollbar">
        <table class="w-full">
            <thead>
                <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40">
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest rounded-tl-2xl">
                        <a href="{{ sortUrl('order_number', $sort, $dir) }}" class="inline-flex items-center gap-1 group hover:text-slate-600 dark:hover:text-slate-350 transition-colors">
                            No. Pesanan
                            @if($sort === 'order_number')
                                @if($dir === 'asc')
                                    <svg class="w-3 h-3 text-primary-500 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/></svg>
                                @else
                                    <svg class="w-3 h-3 text-primary-500 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                @endif
                            @else
                                <svg class="w-3 h-3 text-slate-300 dark:text-slate-600 group-hover:text-slate-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"/></svg>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                        Pelanggan
                    </th>
                    <th class="px-6 py-4 text-right text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                        <a href="{{ sortUrl('total', $sort, $dir) }}" class="inline-flex items-center justify-end gap-1 group hover:text-slate-600 dark:hover:text-slate-350 transition-colors w-full">
                            Total
                            @if($sort === 'total')
                                @if($dir === 'asc')
                                    <svg class="w-3 h-3 text-primary-500 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/></svg>
                                @else
                                    <svg class="w-3 h-3 text-primary-500 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                @endif
                            @else
                                <svg class="w-3 h-3 text-slate-300 dark:text-slate-600 group-hover:text-slate-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"/></svg>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                        <a href="{{ sortUrl('status', $sort, $dir) }}" class="inline-flex items-center justify-center gap-1 group hover:text-slate-600 dark:hover:text-slate-350 transition-colors mx-auto">
                            Status
                            @if($sort === 'status')
                                @if($dir === 'asc')
                                    <svg class="w-3 h-3 text-primary-500 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/></svg>
                                @else
                                    <svg class="w-3 h-3 text-primary-500 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                @endif
                            @else
                                <svg class="w-3 h-3 text-slate-300 dark:text-slate-600 group-hover:text-slate-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"/></svg>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                        <a href="{{ sortUrl('payment_status', $sort, $dir) }}" class="inline-flex items-center justify-center gap-1 group hover:text-slate-600 dark:hover:text-slate-350 transition-colors mx-auto">
                            Pembayaran
                            @if($sort === 'payment_status')
                                @if($dir === 'asc')
                                    <svg class="w-3 h-3 text-primary-500 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/></svg>
                                @else
                                    <svg class="w-3 h-3 text-primary-500 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                @endif
                            @else
                                <svg class="w-3 h-3 text-slate-300 dark:text-slate-600 group-hover:text-slate-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"/></svg>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-4 text-right text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                        <a href="{{ sortUrl('created_at', $sort, $dir) }}" class="inline-flex items-center justify-end gap-1 group hover:text-slate-600 dark:hover:text-slate-350 transition-colors w-full">
                            Tanggal
                            @if($sort === 'created_at')
                                @if($dir === 'asc')
                                    <svg class="w-3 h-3 text-primary-500 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/></svg>
                                @else
                                    <svg class="w-3 h-3 text-primary-500 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                @endif
                            @else
                                <svg class="w-3 h-3 text-slate-300 dark:text-slate-600 group-hover:text-slate-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"/></svg>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-4 text-right text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest rounded-tr-2xl">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/60 dark:divide-slate-800/60">
                @forelse($orders as $order)
                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/20 transition-colors">
                    <td class="px-6 py-4 text-sm font-bold text-slate-900 dark:text-slate-100">
                        {{ $order->order_number }}
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-650 dark:text-slate-350">
                        {{ $order->user->name ?? '-' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-right font-bold text-slate-900 dark:text-slate-100">
                        {{ $order->formatted_total }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex justify-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold {{ match($order->status_color) { 'green','emerald' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-650 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-950/30', 'red' => 'bg-red-50 dark:bg-red-950/40 text-red-655 dark:text-red-400 border border-red-200/50 dark:border-red-950/30', 'orange' => 'bg-orange-50 dark:bg-orange-950/40 text-orange-655 dark:text-orange-400 border border-orange-200/50 dark:border-orange-950/30', 'yellow' => 'bg-yellow-50 dark:bg-yellow-950/40 text-yellow-655 dark:text-yellow-400 border border-yellow-200/50 dark:border-yellow-950/30', default => 'bg-blue-50 dark:bg-blue-950/40 text-blue-655 dark:text-blue-400 border border-blue-200/50 dark:border-blue-950/30' } }}">
                                {{ $order->status_label }}
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex justify-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $order->payment_status === 'paid' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-250/30 dark:border-emerald-900/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500' }}">
                                @if($order->payment_status === 'paid')
                                    {{ $order->payment_method ? strtoupper($order->payment_method) : 'PAID' }}
                                @else
                                    {{ $order->payment_status === 'unpaid' ? 'BELUM BAYAR' : strtoupper($order->payment_status) }}
                                @endif
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-right text-slate-400 dark:text-slate-500">
                        {{ $order->created_at->format('d/m/Y') }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @if($order->tracking_number || $order->courier_tracking_id)
                            <div class="relative group/tooltip inline-flex">
                                <a href="{{ $order->tracking_url }}" 
                                   target="_blank"
                                   class="p-2 rounded-xl text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 hover:bg-emerald-50 dark:hover:bg-emerald-950/60 transition-all hover:shadow-sm"
                                   title="Lacak Pengiriman">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                                </a>
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-slate-900 text-white text-[10px] font-bold rounded-lg shadow-xl opacity-0 group-hover/tooltip:opacity-100 transition-all pointer-events-none whitespace-nowrap z-50">
                                    Lacak Pengiriman
                                    <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></div>
                                </div>
                            </div>
                            @endif
                            <div class="relative group/tooltip inline-flex">
                                <button @click="selectedOrder = {{ $order->toJson() }}; showDetail = true"
                                        class="p-2 rounded-xl text-indigo-650 dark:text-indigo-400 hover:text-indigo-750 hover:bg-indigo-50 dark:hover:bg-indigo-950/60 transition-all hover:shadow-sm"
                                        title="Lihat Detail">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                </button>
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-slate-900 text-white text-[10px] font-bold rounded-lg shadow-xl opacity-0 group-hover/tooltip:opacity-100 transition-all pointer-events-none whitespace-nowrap z-50">
                                    Lihat Detail
                                    <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-20 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-16 h-16 bg-slate-50 dark:bg-slate-800 rounded-2xl flex items-center justify-center text-slate-300 dark:text-slate-600 mb-4 transition-colors">
                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                            </div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Tidak ada pesanan ditemukan</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-500 mt-1">Coba sesuaikan filter atau hapus pencarian Anda.</p>
                            @if(request()->anyFilled(['search', 'status', 'payment_status']))
                                <a href="{{ route('admin.orders.index') }}" class="mt-4 px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-bold rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-all duration-200">Reset Filter</a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-3 border-t border-slate-50 dark:border-slate-800 bg-white dark:bg-slate-900">{{ $orders->links() }}</div>
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
                            <span class="text-xs font-extrabold text-slate-900 dark:text-slate-100 uppercase tracking-wider transition-colors" x-text="selectedOrder?.payment_method?.toUpperCase() || 'MIDTRANS'"></span>
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

                    {{-- NEW: Order Notes --}}
                    <template x-if="selectedOrder?.notes">
                        <div class="space-y-2">
                            <h4 class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest transition-colors">Catatan Pesanan</h4>
                            <div class="p-4 rounded-2xl bg-slate-50/50 dark:bg-slate-950/30 border border-slate-100 dark:border-slate-800/80 shadow-sm transition-colors">
                                <p class="text-[11px] text-slate-650 dark:text-slate-350 leading-relaxed italic" x-text="`'${selectedOrder.notes}'`"></p>
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
                                        <img :src="trackingData.courier.photo" class="w-full h-full object-cover">
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
                    <div class="rounded-2xl border border-slate-100 dark:border-slate-800 overflow-hidden transition-all duration-300">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-800/50">
                                <tr>
                                    <th class="px-4 py-2 text-left font-bold text-slate-600 dark:text-slate-400 text-xs transition-colors">Produk</th>
                                    <th class="px-4 py-2 text-center font-bold text-slate-600 dark:text-slate-400 text-xs w-20 transition-colors">Qty</th>
                                    <th class="px-4 py-2 text-right font-bold text-slate-600 dark:text-slate-400 text-xs w-32 transition-colors">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                                <template x-for="item in selectedOrder?.items" :key="item.id">
                                    <tr>
                                        <td class="px-4 py-3 min-w-[200px]">
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
                                        <td class="px-4 py-3 text-center font-medium text-slate-600 dark:text-slate-400 transition-colors" x-text="item.quantity"></td>
                                        <td class="px-4 py-3 text-right font-bold text-slate-900 dark:text-slate-100 transition-colors" x-text="formatCurrency(item.subtotal)"></td>
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
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 transition-colors flex items-center justify-between shrink-0">
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest transition-colors">Status:</span>
                        <span class="px-3 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-widest bg-white dark:bg-slate-950 shadow-sm border border-slate-100 dark:border-slate-800 transition-all" 
                              x-text="selectedOrder?.status_label"
                              :class="{
                                  'text-emerald-600 dark:text-emerald-400': selectedOrder?.status === 'completed' || selectedOrder?.status === 'paid',
                                  'text-amber-500 dark:text-amber-400': selectedOrder?.status === 'processing' || selectedOrder?.status === 'shipped',
                                  'text-orange-500 dark:text-orange-400': selectedOrder?.status === 'pending' || selectedOrder?.status === 'awaiting_payment',
                                  'text-red-500 dark:text-red-400': selectedOrder?.status === 'cancelled'
                              }"></span>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    {{-- Courier booking is automatic once payment settles, so the manual
                         "Proses ke Biteship" button was removed. The retry below stays
                         as an admin escape hatch when a courier cannot be found. --}}
                    <template x-if="selectedOrder?.status === 'processing' && trackingData?.status === 'courier_not_found'">
                        <button type="button"
                                @click="submitProcessShipping(selectedOrder.id)"
                                class="px-3 py-1.5 sm:px-5 sm:py-2 bg-emerald-600 text-white text-[11px] sm:text-sm font-bold rounded-xl hover:bg-emerald-700 transition-all shadow-sm shadow-emerald-200 flex items-center gap-1.5 sm:gap-2">
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                            Cari Ulang Kurir
                        </button>
                    </template>

                    {{-- Cancel: the one real courier action Biteship's API exposes.
                         Only orders still in fulfilment (processing/shipped) can be cancelled. --}}
                    <template x-if="['processing', 'shipped'].includes(selectedOrder?.status)">
                        <button type="button"
                                @click="submitCancelOrder(selectedOrder.id)"
                                class="px-3 py-1.5 sm:px-5 sm:py-2 bg-red-600 text-white text-[11px] sm:text-sm font-bold rounded-xl hover:bg-red-700 transition-all shadow-sm shadow-red-200 flex items-center gap-1.5 sm:gap-2">
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                            Batalkan Pesanan
                        </button>
                    </template>
                    <button @click="showDetail = false"
                            class="px-3 py-1.5 sm:px-5 sm:py-2 bg-slate-900 dark:bg-slate-800 text-white text-[11px] sm:text-sm font-bold rounded-xl hover:bg-slate-800 dark:hover:bg-slate-700 transition-all duration-200">Tutup</button>
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
