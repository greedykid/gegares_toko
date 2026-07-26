@extends('layouts.app')

@section('title', 'Halaman Tidak Ditemukan')

@section('content')
    <x-error-page
        code="404"
        title="Halaman ini tidak ada"
        message="Alamatnya mungkin salah ketik, atau halamannya sudah dipindahkan. Produk yang pernah ada juga bisa sudah tidak dijual lagi.">

        <x-slot:icon>
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="w-full h-full">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
            </svg>
        </x-slot:icon>

        <x-slot:actions>
            <a href="{{ route('home') }}"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-primary-600 text-white text-sm font-bold rounded-xl hover:bg-primary-700 transition-all active:scale-[0.98] shadow-lg shadow-primary-600/20">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/></svg>
                Kembali ke Beranda
            </a>
            <a href="{{ route('products.index') }}"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-sm font-bold rounded-xl hover:border-primary-300 dark:hover:border-primary-700 transition-all active:scale-[0.98]">
                Jelajahi Produk
            </a>
        </x-slot:actions>
    </x-error-page>
@endsection
