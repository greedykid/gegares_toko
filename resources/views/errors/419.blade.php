@extends('layouts.app')

@section('title', 'Sesi Berakhir')

@section('content')
    <x-error-page
        code="419"
        tone="amber"
        title="Sesi kamu sudah berakhir"
        message="Halaman ini dibuka terlalu lama, jadi token keamanannya kedaluwarsa. Muat ulang halamannya, lalu kirim ulang — isian yang tadi kamu ketik perlu diisi kembali.">

        <x-slot:icon>
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="w-full h-full">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
        </x-slot:icon>

        <x-slot:actions>
            {{-- Reloading is the actual fix: it issues a fresh CSRF token. --}}
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('home') }}"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-primary-600 text-white text-sm font-bold rounded-xl hover:bg-primary-700 transition-all active:scale-[0.98] shadow-lg shadow-primary-600/20">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                Muat Ulang Halaman
            </a>
            @guest
                <a href="{{ route('login') }}"
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-sm font-bold rounded-xl hover:border-primary-300 dark:hover:border-primary-700 transition-all active:scale-[0.98]">
                    Masuk Lagi
                </a>
            @else
                <a href="{{ route('home') }}"
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-sm font-bold rounded-xl hover:border-primary-300 dark:hover:border-primary-700 transition-all active:scale-[0.98]">
                    Ke Beranda
                </a>
            @endguest
        </x-slot:actions>
    </x-error-page>
@endsection
