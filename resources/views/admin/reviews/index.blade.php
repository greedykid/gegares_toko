@extends('layouts.admin')
@section('page_title', 'Moderasi Ulasan')
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
<div x-data="{ reviewImage: null }">

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

{{-- Page header --}}
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Kelola Ulasan</h1>
    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Moderasi ulasan pelanggan dan setujui yang layak tampil.</p>
</div>

{{-- Filters --}}
<div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-4 mb-6 transition-all duration-300">
    <form method="GET" action="{{ route('admin.reviews.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-7 gap-4 items-end">
        <div class="lg:col-span-2">
            <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5 ml-1 transition-colors">Cari Ulasan</label>
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Produk atau pengguna..."
                       data-live-search data-target="#reviewsTable" autocomplete="off"
                       class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all">
                <div class="absolute left-3 top-2.5 text-slate-400 dark:text-slate-600 transition-colors">
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                </div>
            </div>
        </div>
        <div class="lg:col-span-2">
            <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5 ml-1 transition-colors">Periode</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-600">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                </div>
                <input type="text" id="date_range_picker"
                       placeholder="Pilih rentang tanggal..."
                       readonly
                       class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all cursor-pointer">
                <input type="hidden" name="from_date" id="from_date" value="{{ request('from_date') }}">
                <input type="hidden" name="to_date" id="to_date" value="{{ request('to_date') }}">
            </div>
        </div>
        <div>
            <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5 ml-1 transition-colors">Rating</label>
            <div x-data="{ 
                    open: false, 
                    selectedValue: '{{ request('rating') }}', 
                    selectedLabel: '{{ request('rating') ? request('rating') . ' Bintang' : 'Semua' }}'
                 }" 
                 class="relative w-full">
                <input type="hidden" name="rating" :value="selectedValue">
                <button @click="open = !open" type="button" 
                        class="w-full flex items-center justify-between pl-3 pr-3 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 cursor-pointer transition-all">
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
                    <button type="button" @click="selectedValue = ''; selectedLabel = 'Semua'; open = false; $nextTick(() => { $el.closest('form').submit() })"
                            class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                            :class="selectedValue === '' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">
                        Semua
                    </button>
                    @for($i=5; $i>=1; $i--)
                        <button type="button" @click="selectedValue = '{{ $i }}'; selectedLabel = '{{ $i }} Bintang'; open = false; $nextTick(() => { $el.closest('form').submit() })"
                                class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                :class="selectedValue === '{{ $i }}' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">
                            {{ $i }} Bintang
                        </button>
                    @endfor
                </div>
            </div>
        </div>
        <div>
            <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5 ml-1 transition-colors">Status</label>
            <div x-data="{ 
                    open: false, 
                    selectedValue: '{{ request('is_approved') }}', 
                    selectedLabel: '{{ request('is_approved') === '1' ? 'Disetujui' : (request('is_approved') === '0' ? 'Pending' : 'Semua') }}'
                 }" 
                 class="relative w-full">
                <input type="hidden" name="is_approved" :value="selectedValue">
                <button @click="open = !open" type="button" 
                        class="w-full flex items-center justify-between pl-3 pr-3 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 cursor-pointer transition-all">
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
                    <button type="button" @click="selectedValue = ''; selectedLabel = 'Semua'; open = false; $nextTick(() => { $el.closest('form').submit() })"
                            class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                            :class="selectedValue === '' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">
                        Semua
                    </button>
                    <button type="button" @click="selectedValue = '1'; selectedLabel = 'Disetujui'; open = false; $nextTick(() => { $el.closest('form').submit() })"
                            class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                            :class="selectedValue === '1' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">
                        Disetujui
                    </button>
                    <button type="button" @click="selectedValue = '0'; selectedLabel = 'Pending'; open = false; $nextTick(() => { $el.closest('form').submit() })"
                            class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                            :class="selectedValue === '0' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">
                        Pending
                    </button>
                </div>
            </div>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 px-4 py-2 bg-slate-800 dark:bg-primary-600 text-white text-sm font-bold rounded-xl hover:bg-slate-900 dark:hover:bg-primary-700 shadow-sm transition-all">Filter</button>
            <a href="{{ route('admin.reviews.index') }}" class="px-3 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-sm font-bold rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-all flex items-center justify-center" title="Reset">
                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
            </a>
        </div>
    </form>
</div>

{{-- Filter Info & Active Badges --}}
@if(request()->anyFilled(['search', 'rating', 'is_approved']) || (request('from_date') && request('from_date') != now()->subMonth()->format('Y-m-d')) || (request('to_date') && request('to_date') != now()->format('Y-m-d')))
<div class="flex flex-wrap items-center gap-2 mb-6 ml-1">
    <span class="text-xs font-bold text-slate-400 dark:text-slate-500 mr-2 transition-colors uppercase tracking-wider">Menampilkan {{ $reviews->total() }} ulasan untuk:</span>
    
    @if(request('search'))
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-primary-50 dark:bg-primary-950/40 text-primary-700 dark:text-primary-400 text-[10px] font-bold rounded-lg border border-primary-100 dark:border-primary-900 uppercase transition-all">
            Cari: "{{ request('search') }}"
        </span>
    @endif

    @if(request('rating'))
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 text-[10px] font-bold rounded-lg border border-amber-100 dark:border-amber-900 uppercase transition-all">
            {{ request('rating') }} Bintang
        </span>
    @endif

    @if(request('is_approved') !== null && request('is_approved') !== '')
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-400 text-[10px] font-bold rounded-lg border border-indigo-100 dark:border-indigo-900 uppercase transition-all">
            Status: {{ request('is_approved') == '1' ? 'Disetujui' : 'Pending' }}
        </span>
    @endif

    @if(request('from_date') || request('to_date'))
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-400 text-[10px] font-bold rounded-lg border border-slate-200 dark:border-slate-700 uppercase transition-all">
            Periode: {{ \Carbon\Carbon::parse(request('from_date'))->format('d M') }} - {{ \Carbon\Carbon::parse(request('to_date'))->format('d M Y') }}
        </span>
    @endif

    <a href="{{ route('admin.reviews.index') }}" class="text-[10px] font-bold text-red-500 dark:text-red-400 hover:text-red-700 underline ml-2 transition-colors">Bersihkan Semua</a>
</div>
@endif

@php $approvedTab = request('is_approved', ''); $approvedTabs = ['' => 'Semua', '1' => 'Disetujui', '0' => 'Menunggu']; @endphp
<div x-data="adminListView('reviews')" :class="grid ? 'admin-grid-view' : ''" class="admin-list-card transition-all duration-300">
    <div class="flex flex-col-reverse gap-3 pb-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="shrink-0">
            @include('admin.partials.view-toggle')
        </div>
        <div class="flex items-center gap-1 overflow-x-auto scrollbar-none -mx-1 px-1">
            @foreach($approvedTabs as $val => $label)
                <a href="{{ request()->fullUrlWithQuery(['is_approved' => $val, 'page' => null]) }}"
                   class="shrink-0 px-3.5 py-1.5 rounded-lg text-sm font-semibold transition-colors {{ $approvedTab === $val ? 'bg-primary-600 text-white shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">{{ $label }}</a>
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
