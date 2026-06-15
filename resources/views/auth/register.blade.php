@extends('layouts.auth')
@section('title', 'Daftar')
@section('form_width_class', 'max-w-3xl')

@section('content')
<div>
    <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight leading-none mb-2">Buat Akun Baru</h1>
    <p class="text-sm text-slate-500 dark:text-slate-400 font-medium mb-8">Daftar untuk mulai belanja jajanan pasar tradisional premium.</p>

    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50">
            @foreach($errors->all() as $error)
                <p class="text-sm font-semibold text-red-750 dark:text-red-400">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form id="register-form" x-data="{ loading: false }" @submit="loading = true" method="POST" action="{{ route('register') }}" class="space-y-6">
        @csrf
        <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
            {{-- Kolom Kiri: Informasi Profil --}}
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700 dark:text-slate-350 mb-1.5 ml-0.5">Nama Lengkap</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all font-medium" placeholder="Nama lengkap Anda">
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-350 mb-1.5 ml-0.5">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all font-medium" placeholder="nama@email.com">
                </div>
                
                <div>
                    <label for="phone" class="block text-sm font-semibold text-slate-700 dark:text-slate-350 mb-1.5 ml-0.5">Nomor WhatsApp</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required
                           inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all font-medium" placeholder="62812...">
                </div>
            </div>
            
            {{-- Kolom Kanan: Keamanan Akun --}}
            <div class="space-y-4">
                <div x-data="{ show: false }">
                    <label for="password" class="block text-sm font-semibold text-slate-700 dark:text-slate-350 mb-1.5 ml-0.5">Password</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" id="password" name="password" required
                               class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all font-medium" placeholder="Minimal 8 karakter">
                        <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-350 focus:outline-none p-1 transition-colors">
                            <template x-if="!show">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </template>
                            <template x-if="show">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </template>
                        </button>
                    </div>
                </div>
                
                <div x-data="{ show: false }">
                    <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 dark:text-slate-350 mb-1.5 ml-0.5">Konfirmasi Password</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" id="password_confirmation" name="password_confirmation" required
                               class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all font-medium" placeholder="Ulangi password">
                        <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-350 focus:outline-none p-1 transition-colors">
                            <template x-if="!show">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </template>
                            <template x-if="show">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </template>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="pt-4">
            <button type="submit"
                :disabled="loading"
                :class="loading ? 'opacity-70 cursor-not-allowed' : ''"
                class="w-full py-3.5 bg-primary-600 text-white font-bold rounded-xl hover:bg-primary-700 hover:shadow-lg hover:shadow-primary-600/10 active:scale-98 transition-all duration-200 mt-2 flex items-center justify-center gap-2">
                <svg x-show="loading" class="animate-spin w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display:none;">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span x-text="loading ? 'Daftar...' : 'Daftar'">Daftar</span>
            </button>

            {{-- Social Login Separator --}}
            <div class="relative py-4">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-100 dark:border-slate-900"></div>
                </div>
                <div class="relative flex justify-center text-xs uppercase">
                    <span class="bg-slate-50 dark:bg-slate-950 px-3.5 text-slate-400 dark:text-slate-500 font-black tracking-widest transition-colors">Atau</span>
                </div>
            </div>

            {{-- Google Login Button --}}
            <a href="{{ route('auth.google') }}" 
               class="flex items-center justify-center gap-3 w-full py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-semibold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-all duration-200 shadow-sm">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24">
                    <path fill="#ea4335" d="M5.2662 9.76451C6.1982 6.95542 8.8542 4.90909 12 4.90909C13.6909 4.90909 15.2182 5.50909 16.4182 6.49091L19.9091 3C17.7818 1.14545 15.0545 0 12 0C7.27273 0 3.19091 2.69091 1.24545 6.65455L5.2662 9.76451Z"/>
                    <path fill="#34a853" d="M16.0409 18.0136C14.8705 18.7159 13.4841 19.0909 12 19.0909C8.8542 19.0909 6.1982 17.0455 5.2662 14.2364L1.24545 17.3455C3.19091 21.3091 7.27273 24 12 24C15.0545 24 17.7818 23.0182 19.9318 21.3273L16.0409 18.0136Z"/>
                    <path fill="#4285f4" d="M23.64 12.2182C23.64 11.3364 23.5545 10.5182 23.3909 9.70909H12V14.2473H18.5182C18.2364 15.7455 17.3909 17.0273 16.1261 17.8909L20.0171 21.2045C22.28 19.1182 23.64 16.0091 23.64 12.2182Z"/>
                    <path fill="#fbbc05" d="M5.2662 14.2364C5.0182 13.5273 4.8818 12.7727 4.8818 12C4.8818 11.2273 5.0182 10.4727 5.2662 9.76364L1.24545 6.65455C0.445455 8.27273 0 10.0818 0 12C0 13.9182 0.445455 15.7273 1.24545 17.3455L5.2662 14.2364Z"/>
                </svg>
                <span>Daftar dengan Google</span>
            </a>
        </div>
    </form>
    
    <p class="mt-8 text-center text-sm text-slate-500 dark:text-slate-400 font-medium">Sudah punya akun? <a href="{{ route('login') }}" class="font-bold text-primary-600 dark:text-primary-400 hover:text-primary-700 underline">Masuk</a></p>
</div>
@endsection

@push('scripts')
@if(!app()->environment('local') && config('services.recaptcha.site') && config('services.recaptcha.secret'))
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site') }}"></script>
    <script>
        document.getElementById('register-form').addEventListener('submit', function(e) {
            e.preventDefault();
            grecaptcha.ready(function() {
                grecaptcha.execute("{{ config('services.recaptcha.site') }}", {action: 'register'}).then(function(token) {
                    document.getElementById('g-recaptcha-response').value = token;
                    document.getElementById('register-form').submit();
                }).catch(function(error) {
                    document.getElementById('g-recaptcha-response').value = 'error';
                    document.getElementById('register-form').submit();
                });
            });
        });
    </script>
@else
    <script>
        document.getElementById('register-form').addEventListener('submit', function(e) {
            document.getElementById('g-recaptcha-response').value = 'local-bypass';
        });
    </script>
@endif
@endpush
