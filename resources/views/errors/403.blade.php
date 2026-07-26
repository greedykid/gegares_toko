@extends('layouts.app')

@section('title', 'Akses Ditolak')

@section('content')
    <x-error-page
        code="403"
        tone="amber"
        title="Kamu tidak punya akses ke halaman ini"
        :message="$exception?->getMessage() ?: 'Halaman ini hanya untuk pemiliknya. Kalau kamu merasa seharusnya bisa membukanya, coba masuk dengan akun yang benar.'">

        <x-slot:icon>
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="w-full h-full">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
            </svg>
        </x-slot:icon>

        <x-slot:actions>
            <a href="{{ route('home') }}"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-primary-600 text-white text-sm font-bold rounded-xl hover:bg-primary-700 transition-all active:scale-[0.98] shadow-lg shadow-primary-600/20">
                Kembali ke Beranda
            </a>
            @guest
                <a href="{{ route('login') }}"
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-sm font-bold rounded-xl hover:border-primary-300 dark:hover:border-primary-700 transition-all active:scale-[0.98]">
                    Masuk ke Akun
                </a>
            @endguest
        </x-slot:actions>
    </x-error-page>
@endsection
