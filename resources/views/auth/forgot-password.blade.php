@extends('layouts.auth')
@section('title', 'Lupa Password')
@section('content')

    <div>
        <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight leading-none mb-2">Lupa Password</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 font-medium mb-8">Masukkan alamat email Anda untuk menerima tautan reset password.</p>

        @if(session('status'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800/50">
                <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-400">{{ session('status') }}</p>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50">
                @foreach($errors->all() as $error)
                    <p class="text-sm font-semibold text-red-700 dark:text-red-400">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form x-data="{ loading: false }" @submit="loading = true" method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5 ml-0.5">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all duration-200"
                    placeholder="nama@email.com">
            </div>

            <button type="submit"
                :disabled="loading"
                :class="loading ? 'opacity-70 cursor-not-allowed' : ''"
                class="w-full py-3 bg-primary-600 text-white font-bold rounded-xl hover:bg-primary-700 hover:shadow-lg hover:shadow-primary-600/10 active:scale-98 transition-all duration-200 flex items-center justify-center gap-2">
                <svg x-show="loading" class="animate-spin w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display:none;">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span x-text="loading ? 'Mengirim...' : 'Kirim Tautan Reset'">Kirim Tautan Reset</span>
            </button>
        </form>

        <p class="mt-8 text-center text-sm text-slate-500 dark:text-slate-400 font-medium">
            Kembali ke halaman <a href="{{ route('login') }}"
                class="font-bold text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300 underline">Masuk</a>
        </p>
    </div>

@endsection
