@extends('layouts.app')

@section('title', 'Lengkapi Profil')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4 py-12 bg-slate-50 dark:bg-slate-950 transition-colors duration-300">
    <div class="w-full max-w-md">
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-36 h-36 rounded-4xl bg-white dark:bg-slate-900 shadow-md mb-8 border border-slate-100 dark:border-slate-800 p-5 overflow-hidden group">
                <img src="{{ asset('images/logo.png') }}" alt="Gegares Logo" class="w-full h-full object-contain group-hover:scale-110 group-hover:rotate-6 transition-transform duration-500">
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight">Satu Langkah Lagi</h1>
            <p class="mt-3 text-slate-500 dark:text-slate-400 text-sm leading-relaxed px-4">
                Halo, <span class="font-bold text-slate-800 dark:text-slate-200">{{ $user->name }}</span>! Untuk kemudahan koordinasi pengiriman pesanan, kami membutuhkan nomor WhatsApp Anda.
            </p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-2xl shadow-slate-200/50 dark:shadow-none border border-slate-100 dark:border-slate-800 p-10 transition-all duration-300">
            @if($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/30">
                    @foreach($errors->all() as $error)
                        <p class="text-xs font-bold text-red-600 dark:text-red-400">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('settings.update-complete-profile') }}" method="POST" class="space-y-8">
                @csrf
                <div>
                    <label for="phone" class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-3 ml-1">Nomor WhatsApp Aktif</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="text-sm font-bold text-slate-400 dark:text-slate-500 group-focus-within:text-primary-500 transition-colors">+62</span>
                        </div>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required autofocus
                               class="w-full pl-14 pr-4 py-4 rounded-2xl border-2 border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all font-bold text-lg" 
                               placeholder="81234567890">
                    </div>
                    <p class="mt-3 text-[10px] text-slate-400 dark:text-slate-500 italic ml-1">
                        * Nomor ini akan digunakan kurir untuk menghubungi Anda saat pengiriman.
                    </p>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-4.5 bg-primary-600 hover:bg-primary-700 text-white font-black rounded-2xl active:scale-[0.98] transition-all flex items-center justify-center gap-3">
                        <span>Selesaikan Pendaftaran</span>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-10 text-center">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="text-xs font-bold text-slate-400 dark:text-slate-600 hover:text-red-500 transition-colors">
                    Nanti saja, Keluar Dulu
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
