@extends('layouts.admin')

@section('title', 'Lokasi Toko')
@section('page_title', 'Lokasi Toko')

@section('content')
<div class="flex flex-col gap-4 mb-6 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Lokasi Toko</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola alamat fisik toko dan titik penjemputan (<span class="italic">shipper address</span>) untuk kurir pengiriman.</p>
    </div>
</div>

<div class="flex flex-col w-full mx-auto space-y-6">

    {{-- Livewire Component --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-800 overflow-hidden transition-all duration-300">
        <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3 transition-colors">
            <div class="w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center text-primary-600 dark:text-primary-400 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
            </div>
            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider transition-colors">Konfigurasi Lokasi Toko</h4>
        </div>
        <div class="p-6">
            @livewire('admin.manage-store-address')
        </div>
    </div>

    {{-- Tips Card --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-indigo-50/50 dark:bg-indigo-950/20 rounded-2xl p-6 border border-indigo-100/50 dark:border-indigo-900/30 flex gap-4 transition-all">
            <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center shrink-0 transition-colors">
                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div>
                <h5 class="font-bold text-indigo-900 dark:text-indigo-300 text-sm transition-colors">Gunakan Pinpoint Precise</h5>
                <p class="text-indigo-700/70 dark:text-indigo-400/60 text-xs mt-1 transition-colors">Pastikan titik merah di peta sesuai dengan lokasi gudang/gerai Anda agar kurir tidak kesulitan saat melakukan penjemputan.</p>
            </div>
        </div>
        <div class="bg-emerald-50/50 dark:bg-emerald-950/20 rounded-2xl p-6 border border-emerald-100/50 dark:border-emerald-900/30 flex gap-4 transition-all">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center shrink-0 transition-colors">
                <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" /></svg>
            </div>
            <div>
                <h5 class="font-bold text-emerald-900 dark:text-emerald-300 text-sm transition-colors">Sinkronisasi Biteship</h5>
                <p class="text-emerald-700/70 dark:text-emerald-400/60 text-xs mt-1 transition-colors">Setiap alamat yang Anda simpan akan otomatis didaftarkan sebagai "Location" di Biteship untuk mempercepat proses pembuatan pesanan.</p>
            </div>
        </div>
    </div>
</div>
@endsection
