@extends('layouts.app')

@section('title', 'Terlalu Banyak Percobaan')

@php
    // Throttle responses carry Retry-After. Telling the customer how long to wait
    // is the difference between a usable page and one that invites them to keep
    // hammering the form.
    $retryAfter = null;

    if (isset($exception) && $exception && method_exists($exception, 'getHeaders')) {
        $retryAfter = $exception->getHeaders()['Retry-After'] ?? null;
    }

    $wait = match (true) {
        ! is_numeric($retryAfter) => null,
        (int) $retryAfter < 60 => (int) $retryAfter.' detik',
        default => ceil($retryAfter / 60).' menit',
    };
@endphp

@section('content')
    <x-error-page
        code="429"
        tone="amber"
        title="Terlalu banyak percobaan"
        :message="$wait
            ? 'Permintaanmu kami tahan sementara demi keamanan akun. Coba lagi dalam '.$wait.'.'
            : 'Permintaanmu kami tahan sementara demi keamanan akun. Tunggu sebentar, lalu coba lagi.'">

        <x-slot:icon>
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="w-full h-full">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
        </x-slot:icon>

        <x-slot:actions>
            <a href="{{ route('home') }}"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-primary-600 text-white text-sm font-bold rounded-xl hover:bg-primary-700 transition-all active:scale-[0.98] shadow-lg shadow-primary-600/20">
                Kembali ke Beranda
            </a>
            @guest
                <a href="{{ route('password.request') }}"
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-sm font-bold rounded-xl hover:border-primary-300 dark:hover:border-primary-700 transition-all active:scale-[0.98]">
                    Lupa Kata Sandi?
                </a>
            @endguest
        </x-slot:actions>
    </x-error-page>
@endsection
