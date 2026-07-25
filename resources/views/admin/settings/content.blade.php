@extends('layouts.admin')

@section('title', 'Pengaturan Toko')
@section('page_title', 'Pengaturan Toko')

@section('content')
    <div class="flex flex-col gap-4 mb-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Pengaturan Toko</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola konten landing page, banner promo, FAQ, informasi toko, kontak, dan footer.</p>
        </div>
    </div>

    @livewire('admin.manage-store-content')
@endsection
