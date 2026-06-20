@extends('layouts.auth')
@section('title', 'Reset Password')
@section('content')

    <div>
        <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight leading-none mb-2">Buat Password Baru</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 font-medium mb-8">Silakan masukkan password baru Anda di bawah ini.</p>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50">
                @foreach($errors->all() as $error)
                    <p class="text-sm font-semibold text-red-700 dark:text-red-400">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form x-data="{ loading: false }" @submit="loading = true" method="POST" action="{{ route('password.update') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            
            <div>
                <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5 ml-0.5">Email</label>
                <input type="email" id="email" name="email" value="{{ $email ?? old('email') }}" required readonly
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-900 text-slate-500 dark:text-slate-400 focus:outline-none cursor-not-allowed"
                    placeholder="nama@email.com">
            </div>

            <div x-data="{ show: false }">
                <label for="password" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5 ml-0.5">Password Baru</label>
                <div class="relative">
                    <input :type="show ? 'text' : 'password'" id="password" name="password" required autofocus
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all duration-200"
                        placeholder="••••••••">
                    <button type="button" @click="show = !show"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none p-1 transition-colors">
                        <template x-if="!show">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </template>
                        <template x-if="show">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </template>
                    </button>
                </div>
            </div>

            <div x-data="{ show_confirm: false }">
                <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5 ml-0.5">Konfirmasi Password Baru</label>
                <div class="relative">
                    <input :type="show_confirm ? 'text' : 'password'" id="password_confirmation" name="password_confirmation" required
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all duration-200"
                        placeholder="••••••••">
                    <button type="button" @click="show_confirm = !show_confirm"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none p-1 transition-colors">
                        <template x-if="!show_confirm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </template>
                        <template x-if="show_confirm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </template>
                    </button>
                </div>
            </div>

            <button type="submit"
                :disabled="loading"
                :class="loading ? 'opacity-70 cursor-not-allowed' : ''"
                class="w-full py-3 bg-primary-600 text-white font-bold rounded-xl hover:bg-primary-700 hover:shadow-lg hover:shadow-primary-600/10 active:scale-98 transition-all duration-200 flex items-center justify-center gap-2">
                <svg x-show="loading" class="animate-spin w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display:none;">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span x-text="loading ? 'Menyimpan...' : 'Perbarui Password'">Perbarui Password</span>
            </button>
        </form>
    </div>

@endsection
