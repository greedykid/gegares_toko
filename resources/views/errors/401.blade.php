@extends('layouts.app')

@section('title', 'Perlu Masuk Dulu')

@section('content')
    <x-error-page
        code="401"
        tone="amber"
        title="Kamu perlu masuk dulu"
        message="Halaman ini hanya bisa dibuka setelah kamu masuk ke akun Gegares. Sesi sebelumnya mungkin juga sudah berakhir.">

        <x-slot:icon>
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="w-full h-full">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
            </svg>
        </x-slot:icon>

        <x-slot:actions>
            <a href="{{ route('login') }}"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-primary-600 text-white text-sm font-bold rounded-xl hover:bg-primary-700 transition-all active:scale-[0.98] shadow-lg shadow-primary-600/20">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75"/></svg>
                Masuk ke Akun
            </a>
            <a href="{{ route('register') }}"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-sm font-bold rounded-xl hover:border-primary-300 dark:hover:border-primary-700 transition-all active:scale-[0.98]">
                Daftar Akun Baru
            </a>
        </x-slot:actions>
    </x-error-page>
@endsection
