@extends('layouts.admin')
@section('page_title', 'Manajemen Kupon')
@section('content')
@php
    $sort = request('sort', 'created_at');
    $dir = request('direction', 'desc');
    
@endphp

@php
    $statusTab = (string) request('is_active', '');
    $statusTabs = ['' => 'Semua', '1' => 'Aktif', '0' => 'Nonaktif'];
@endphp

<div class="flex flex-col gap-4 mb-6 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Kelola Kupon</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola kode diskon untuk meningkatkan penjualan.</p>
    </div>
    <button x-data="" x-on:click="$dispatch('open-modal', 'create-coupon')" class="inline-flex items-center gap-2 h-10 px-5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl transition-colors shrink-0">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        Tambah Kupon
    </button>
</div>

@if(session('success'))
<div class="mb-6 bg-emerald-50 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-400 p-4 rounded-xl border border-emerald-100 dark:border-emerald-800 flex items-center gap-3">
    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
    <p class="text-sm font-medium">{{ session('success') }}</p>
</div>
@endif

<div x-data="adminListView('coupons')" :class="grid ? 'admin-grid-view' : ''" class="admin-list-card transition-all duration-300">
    <div class="flex flex-col-reverse gap-3 pb-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <form method="GET" action="{{ route('admin.coupons.index') }}" class="relative flex flex-1 items-center gap-2 sm:flex-none" x-data="{ loading: false }">
                <input type="hidden" name="is_active" value="{{ request('is_active') }}">
                <input type="hidden" name="sort" value="{{ request('sort') }}">
                <input type="hidden" name="direction" value="{{ request('direction') }}">
                <div class="relative flex-1 min-w-0 sm:flex-none">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 dark:text-slate-500">
                        <svg x-show="!loading" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                        <svg x-show="loading" x-cloak class="w-4 h-4 animate-spin text-primary-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    </span>
                    <input type="text" name="search" data-live-search data-target="#couponsTable" value="{{ request('search') }}" placeholder="Cari kode kupon..." autocomplete="off"
                           class="w-full sm:w-72 h-10 pl-10 pr-3 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all">
                </div>
            </form>
            <div class="shrink-0">
                @include('admin.partials.view-toggle')
            </div>
        </div>
        <div class="inline-flex items-center h-10 p-1 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 max-w-full overflow-x-auto scrollbar-none shrink-0" role="group" aria-label="Filter status">
            @foreach($statusTabs as $val => $label)
                <a href="{{ request()->fullUrlWithQuery(['is_active' => $val, 'page' => null]) }}"
                   class="inline-flex items-center justify-center h-full px-3.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-all {{ (string) $statusTab === (string) $val ? 'bg-primary-600 text-white shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100/60 dark:hover:bg-slate-800/60' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>
    <div id="couponsTable">
        @include('admin.coupons._table')
    </div>
    @include('admin.partials.bulk-bar', ['route' => route('admin.coupons.bulk-destroy'), 'noun' => 'kupon'])
</div>

{{-- Modal Create --}}
<div x-data="{ show: false }" x-init="$watch('show', value => { if (value) $nextTick(() => initCouponDatePickers($el)) })" x-show="show" 
     @open-modal.window="if ($event.detail === 'create-coupon') show = true" 
     @close-modal.window="show = false" 
     @keydown.escape.window="show = false" 
     class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="show" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="absolute inset-0 transition-opacity bg-slate-900/80 backdrop-blur-md" @click="show = false"></div>

                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        
        <div x-show="show" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative z-10 inline-block w-full max-w-lg overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl rounded-2xl">
            
            {{-- Modal Header --}}
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white leading-none">Tambah Kupon</h3>
                <button @click="show = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('admin.coupons.store') }}" method="POST" class="flex flex-col min-h-0 overflow-hidden">
                @csrf
                <div class="p-6 space-y-4 text-left">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Kode Kupon (Unik)</label>
                        <input type="text" name="code" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all uppercase placeholder-slate-400" placeholder="CHINGU2026">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tipe Diskon</label>
                            <div x-data="{ 
                                    open: false, 
                                    selectedValue: 'fixed',
                                    selectedLabel: 'Nominal (Rp)'
                                 }" 
                                 class="relative">
                                <input type="hidden" name="type" :value="selectedValue">
                                <button @click="open = !open" type="button" 
                                        class="w-full flex items-center justify-between pl-4 pr-3 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 cursor-pointer transition-all">
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
                                    <button type="button" @click="selectedValue = 'fixed'; selectedLabel = 'Nominal (Rp)'; open = false"
                                            class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                            :class="selectedValue === 'fixed' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">Nominal (Rp)</button>
                                    <button type="button" @click="selectedValue = 'percent'; selectedLabel = 'Persentase (%)'; open = false"
                                            class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                            :class="selectedValue === 'percent' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">Persentase (%)</button>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nilai Diskon</label>
                            <input type="number" name="value" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all placeholder-slate-400" placeholder="10 / 10000">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Min. Belanja</label>
                            <input type="number" name="min_purchase" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all placeholder-slate-400" placeholder="Pilihan">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Batas Kuota</label>
                            <input type="number" name="usage_limit" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all placeholder-slate-400" placeholder="Tak terbatas">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Mulai Berlaku</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 dark:text-slate-500 z-10">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                    </svg>
                                </div>
                                <input type="text" name="start_date" readonly class="coupon-datetime-picker w-full pl-10 pr-3.5 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all cursor-pointer" placeholder="Pilih tanggal & jam mulai...">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Batas Berlaku</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 dark:text-slate-500 z-10">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                </div>
                                <input type="text" name="end_date" readonly class="coupon-datetime-picker w-full pl-10 pr-3.5 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all cursor-pointer" placeholder="Pilih tanggal & jam selesai...">
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-white dark:bg-slate-900 flex items-center justify-center shadow-sm text-primary-600 dark:text-primary-400 shrink-0">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <span class="text-sm font-bold text-slate-900 dark:text-slate-100">Status Kupon</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0 select-none">
                                <input type="checkbox" name="is_active" value="1" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-5 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-transform peer-checked:bg-primary-600"></div>
                            </label>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2.5 pl-12 leading-relaxed">Tentukan apakah kupon ini aktif digunakan</p>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3 bg-white dark:bg-slate-900">
                    <button type="button" @click="show = false" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition-colors">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-primary-600 text-white text-sm font-semibold rounded-xl hover:bg-primary-700 transition-all duration-200">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/flatpickr/dist/flatpickr.min.css">
<style>
    /* Custom styling for flatpickr calendar & timepicker */
    .flatpickr-calendar {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
        border-radius: 1rem !important;
        font-family: 'Quicksand', sans-serif !important;
    }
    .dark .flatpickr-calendar {
        background: #0f172a !important;
        border: 1px solid #1e293b !important;
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
    .flatpickr-time {
        border-top: 1px solid #e2e8f0 !important;
    }
    .dark .flatpickr-time {
        border-top: 1px solid #1e293b !important;
    }
    .flatpickr-time input {
        color: #0f172a !important;
        font-weight: 700 !important;
    }
    .dark .flatpickr-time input {
        color: #f1f5f9 !important;
        font-weight: 700 !important;
    }
    .flatpickr-time .flatpickr-time-separator, .flatpickr-time .flatpickr-am-pm {
        color: #64748b !important;
        font-weight: 700 !important;
    }
    .dark .flatpickr-time .flatpickr-time-separator, .dark .flatpickr-time .flatpickr-am-pm {
        color: #94a3b8 !important;
        font-weight: 700 !important;
    }
    .flatpickr-time input:hover, .flatpickr-time .flatpickr-am-pm:hover,
    .flatpickr-time input:focus, .flatpickr-time .flatpickr-am-pm:focus {
        background: #f1f5f9 !important;
    }
    .dark .flatpickr-time input:hover, .dark .flatpickr-time .flatpickr-am-pm:hover,
    .dark .flatpickr-time input:focus, .dark .flatpickr-time .flatpickr-am-pm:focus {
        background: #1e293b !important;
    }
</style>
<script src="https://unpkg.com/flatpickr/dist/flatpickr.min.js"></script>
<script src="https://unpkg.com/flatpickr/dist/l10n/id.js"></script>
<script>
    function initCouponDatePickers(container = document) {
        if (typeof flatpickr === 'undefined') return;
        container.querySelectorAll('.coupon-datetime-picker').forEach(function(el) {
            if (el._flatpickr) return;
            flatpickr(el, {
                enableTime: true,
                time_24hr: true,
                dateFormat: "Y-m-d H:i",
                altInput: true,
                altFormat: "j F Y, H:i",
                locale: "id",
                allowInput: false,
                disableMobile: true
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        initCouponDatePickers();
    });
</script>
@endsection
