@extends('layouts.admin')
@section('page_title', 'Moderasi Ulasan')
@section('content')
@php
    $sort = request('sort', 'created_at');
    $dir = request('direction', 'desc');
    
@endphp
<div x-data="{ reviewImage: null }">

{{-- Page header --}}
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Kelola Ulasan</h1>
    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Moderasi ulasan pelanggan dan setujui yang layak tampil.</p>
</div>

{{-- Stats Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5 hover:shadow-md transition-all duration-300">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3h9m-9 3h9M3.375 19.5h17.25c.621 0 1.125-.504 1.125-1.125v-13.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v13.5c0 .621.504 1.125 1.125 1.125z" /></svg>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest transition-colors">Total Ulasan</p>
                <p class="text-xl font-extrabold text-slate-900 dark:text-slate-100 mt-0.5 transition-colors">{{ number_format($totalReviews) }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5 hover:shadow-md transition-all duration-300">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center text-amber-500 dark:text-amber-400 transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
            </div>
            <div>
                <p class="text-[10px] font-bold text-amber-500 dark:text-amber-400 uppercase tracking-widest transition-colors">Pending</p>
                <p class="text-xl font-extrabold text-slate-900 dark:text-slate-100 mt-0.5 transition-colors">{{ number_format($pendingReviews) }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5 hover:shadow-md transition-all duration-300">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" /></svg>
            </div>
            <div>
                <p class="text-[10px] font-bold text-emerald-500 dark:text-emerald-400 uppercase tracking-widest transition-colors">Avg Rating</p>
                <p class="text-xl font-extrabold text-slate-900 dark:text-slate-100 mt-0.5 transition-colors">{{ number_format($avgRating, 1) }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5 hover:shadow-md transition-all duration-300">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-400 transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15a2.25 2.25 0 002.25-2.25V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" /></svg>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest transition-colors">Ulasan Foto</p>
                <p class="text-xl font-extrabold text-slate-900 dark:text-slate-100 mt-0.5 transition-colors">{{ number_format($photoReviews) }}</p>
            </div>
        </div>
    </div>
</div>

@php
    $approvedTab = (string) request('is_approved', '');
    $approvedTabs = ['' => 'Semua', '1' => 'Disetujui', '0' => 'Menunggu'];
@endphp

<div x-data="adminListView('reviews')" :class="grid ? 'admin-grid-view' : ''" class="admin-list-card transition-all duration-300">
    {{-- Controls: search + compact Filter popover + view toggle (left), status quick-filter tabs (right) --}}
    <div class="flex flex-col-reverse gap-3 pb-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <form method="GET" action="{{ route('admin.reviews.index') }}" class="relative flex flex-1 items-center gap-2 sm:flex-none" x-data="{ filterOpen: false, loading: false }">
                <input type="hidden" name="is_approved" value="{{ request('is_approved') }}">
                <input type="hidden" name="sort" value="{{ request('sort') }}">
                <input type="hidden" name="direction" value="{{ request('direction') }}">

                {{-- Search (live AJAX filter) --}}
                <div class="relative flex-1 min-w-0 sm:flex-none">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 dark:text-slate-500">
                        <svg x-show="!loading" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                        <svg x-show="loading" x-cloak class="w-4 h-4 animate-spin text-primary-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    </span>
                    <input type="text" name="search" data-live-search data-target="#reviewsTable" value="{{ request('search') }}" placeholder="Cari ulasan..."
                           autocomplete="off"
                           class="w-full sm:w-80 lg:w-96 h-10 pl-10 pr-3 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all">
                </div>

                {{-- Filter button + popover --}}
                <div class="relative shrink-0">
                    <button type="button" @click="filterOpen = !filterOpen"
                            class="inline-flex items-center gap-1.5 h-10 px-3.5 text-sm font-semibold rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z"/></svg>
                        <span>Filter</span>
                        @if(request()->anyFilled(['rating', 'from_date', 'to_date']))
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

                        {{-- Rating --}}
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">Rating Bintang</label>
                            <div x-data="{ open:false, val:'{{ request('rating') }}', label:'{{ request('rating') ? request('rating') . ' Bintang' : 'Semua Rating' }}' }" class="relative">
                                <input type="hidden" name="rating" :value="val">
                                <button type="button" @click="open=!open"
                                        class="w-full flex items-center justify-between gap-2 px-3 py-2 text-sm rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 hover:border-primary-300 dark:hover:border-primary-700 transition-colors">
                                    <span x-text="label" class="truncate"></span>
                                    <svg class="w-4 h-4 shrink-0 text-slate-400 transition-transform duration-200" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                                </button>
                                <div x-show="open" x-cloak @click.outside="open=false" x-transition.opacity.duration.100ms
                                     class="absolute z-50 mt-1 w-full rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-lg py-1">
                                    <button type="button" @click="val='';label='Semua Rating';open=false" class="w-full text-left px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors" :class="val==='' && 'text-primary-600 dark:text-primary-400 font-semibold'">Semua Rating</button>
                                    @for($i=5; $i>=1; $i--)
                                        <button type="button" @click="val='{{ $i }}';label='{{ $i }} Bintang';open=false" class="w-full text-left px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors" :class="val==='{{ $i }}' && 'text-primary-600 dark:text-primary-400 font-semibold'">{{ $i }} Bintang</button>
                                    @endfor
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-2 pt-1">
                            <button type="submit" class="flex-1 px-3 py-2 bg-primary-600 text-white text-sm font-bold rounded-lg hover:bg-primary-700 transition-colors">Terapkan</button>
                            @if(request()->anyFilled(['search', 'rating', 'from_date', 'to_date']))
                                <a href="{{ route('admin.reviews.index') }}" class="px-3 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-sm font-bold rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">Reset</a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
            <div class="shrink-0">
                @include('admin.partials.view-toggle')
            </div>
        </div>

        <div class="inline-flex items-center h-10 p-1 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 max-w-full overflow-x-auto scrollbar-none shrink-0" role="group" aria-label="Filter ulasan">
            @foreach($approvedTabs as $val => $label)
                <a href="{{ request()->fullUrlWithQuery(['is_approved' => $val, 'page' => null]) }}"
                   class="inline-flex items-center justify-center h-full px-3.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-all {{ (string) $approvedTab === (string) $val ? 'bg-primary-600 text-white shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100/60 dark:hover:bg-slate-800/60' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>
    <div id="reviewsTable">
        @include('admin.reviews._table')
    </div>
    @include('admin.partials.bulk-bar', ['route' => route('admin.reviews.bulk-destroy'), 'noun' => 'ulasan'])
</div>

{{-- Review Image Lightbox Modal --}}
<template x-teleport="body">
    <div x-show="reviewImage" 
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-110 flex items-center justify-center p-4 lg:p-12 overflow-hidden" style="display: none;" @keydown.escape.window="reviewImage = null">
        <div class="absolute inset-0 bg-slate-900/90 backdrop-blur-md" @click="reviewImage = null"></div>
        <button @click="reviewImage = null" class="absolute top-6 right-6 p-3 text-white/70 hover:text-white transition-colors z-120">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
        <div class="relative max-w-5xl max-h-full w-full flex items-center justify-center z-120">
            <img :src="reviewImage" class="max-w-full max-h-[90vh] object-contain rounded-xl shadow-2xl" @click.stop>
        </div>
    </div>
</template>
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
                    fromDateEl.value = instance.formatDate(selectedDates[0], 'Y-m-d');
                    toDateEl.value = instance.formatDate(selectedDates[1], 'Y-m-d');
                } else if (selectedDates.length === 1) {
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
