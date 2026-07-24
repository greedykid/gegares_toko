<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', 'Gegares - Jajanan pasar dan kue tradisional Indonesia terlengkap. Pesan online, antar ke rumah.')">
    <title>@yield('title', 'Gegares') — Jajanan Pasar</title>

    {{-- Favicons. The .ico stays first: browsers still request /favicon.ico blindly. --}}
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <meta name="theme-color" content="#2a6f2c">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300..700&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak]{display:none !important;}
        .cart-open-blur {
            filter: blur(6px) saturate(0.95);
            transition: filter 200ms ease-in-out;
        }
    </style>
    <script>
        const theme = localStorage.getItem('theme') || 'system';
        if (theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    @livewireStyles
    @stack('styles')

    {{-- SEO / Open Graph Tags --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('og_title', 'Gegares — Jajanan Pasar Tradisional Premium')">
    <meta property="og:description" content="@yield('og_description', 'Pesan jajanan pasar tradisional premium online. Dibuat fresh setiap hari dengan bahan terbaik.')">
    <meta property="og:image" content="@yield('og_image', asset('images/logo.png'))">

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', 'Gegares — Jajanan Pasar Tradisional Premium')">
    <meta name="twitter:description" content="@yield('og_description', 'Pesan jajanan pasar tradisional premium online. Dibuat fresh setiap hari.')">
    <meta name="twitter:image" content="@yield('og_image', asset('images/logo.png'))">
</head>
    <body class="min-h-screen flex flex-col bg-white dark:bg-slate-950 transition-colors duration-300" 
      x-data="{ 
        mobileMenu: false,
        theme: localStorage.getItem('theme') || 'system',
        isDark() {
            if (this.theme === 'system') {
                return window.matchMedia('(prefers-color-scheme: dark)').matches;
            }
            return this.theme === 'dark';
        },
        setTheme(newTheme) {
            this.theme = newTheme;
            localStorage.setItem('theme', newTheme);
            this.applyTheme();
            this.$dispatch('theme-changed', newTheme);
        },
        applyTheme() {
            if (this.isDark()) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        },
        toggleTheme() {
            if (this.theme === 'system') {
                this.setTheme('light');
            } else if (this.theme === 'light') {
                this.setTheme('dark');
            } else {
                this.setTheme('system');
            }
        }
      }"
      x-init="applyTheme()"
      @theme-update.window="setTheme($event.detail)"
      x-on:lived.window="applyTheme()">

    {{-- ─── NAVBAR ─── --}}
    <nav class="sticky top-0 z-40 bg-white dark:bg-slate-950 border-b border-slate-100 dark:border-slate-800/60 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 lg:h-20">

                {{-- Left Side: Logo & Navigation --}}
                <div class="flex items-center gap-6 lg:gap-10">
                    {{-- Left: Logo --}}
                    <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                        <img src="{{ asset('images/logo.png') }}" alt="Gegares Logo" width="48" height="48" class="w-12 h-12 object-contain transition-transform duration-500 group-hover:scale-105 group-hover:rotate-12 dark:brightness-110">
                        <span class="hidden sm:block text-xl lg:text-2xl font-black tracking-tight text-slate-900 dark:text-white transition-colors duration-200 group-hover:text-primary-600 dark:group-hover:text-primary-400">gegares</span>
                    </a>

                    {{-- Nav Links (Desktop) --}}
                    <div class="hidden md:flex items-center gap-1 lg:gap-2">
                        @php $nav = [
                            ['route' => 'home', 'label' => 'Beranda'],
                            ['route' => 'products.index', 'label' => 'Produk'],
                            ['route' => 'about', 'label' => 'Tentang'],
                            ['route' => 'contact', 'label' => 'Kontak'],
                        ]; @endphp
                        @foreach($nav as $item)
                            @php $isActive = request()->routeIs($item['route']); @endphp
                            <a href="{{ route($item['route']) }}"
                               class="relative inline-flex items-center px-4 py-2 text-[13px] font-bold uppercase tracking-wide transition-all duration-300 hover:-translate-y-0.5 active:translate-y-0 group
                                      {{ $isActive ? 'text-primary-600 dark:text-primary-400' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' }}">
                                <span class="relative z-10 transition-transform duration-300 group-hover:scale-[1.03]">{{ $item['label'] }}</span>
                                {{-- Animated Underline --}}
                                <span class="absolute bottom-0 left-1/2 -translate-x-1/2 h-[3px] rounded-full bg-linear-to-r from-primary-500 to-primary-600 dark:from-primary-400 dark:to-primary-500 transition-all duration-300 ease-out
                                             {{ $isActive ? 'w-6 group-hover:w-8 opacity-100 shadow-sm shadow-primary-500/50' : 'w-0 opacity-0 group-hover:w-5 group-hover:opacity-100' }}"></span>
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Right: Auth / User Actions --}}
                <div class="flex items-center gap-1 sm:gap-2">
                    {{-- Search --}}
                    <button @click="$dispatch('open-search')" aria-label="Cari" class="hidden md:flex items-center justify-center w-10 h-10 rounded-xl text-slate-500 dark:text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-slate-100 dark:hover:bg-slate-900 transition-all duration-200 active:scale-90" title="Cari">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.35-4.35"/></svg>
                    </button>

                    {{-- Theme Toggle --}}
                    <button @click="toggleTheme()" aria-label="Ganti Tema" class="hidden md:flex items-center justify-center w-10 h-10 rounded-xl text-slate-500 dark:text-slate-400 hover:text-amber-500 dark:hover:text-amber-400 hover:bg-slate-100 dark:hover:bg-slate-900 transition-all duration-200 active:scale-90" title="Ganti Tema">
                        {{-- Sun --}}
                        <svg id="theme-toggle-light" x-show="theme === 'light'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0z"/></svg>
                        {{-- Moon --}}
                        <svg id="theme-toggle-dark" x-show="theme === 'dark'" class="w-5 h-5" x-cloak fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998z"/></svg>
                        {{-- System --}}
                        <svg id="theme-toggle-system" x-show="theme === 'system'" class="w-5 h-5" x-cloak fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25"/></svg>
                        <script>
                            (function() {
                                const t = localStorage.getItem('theme') || 'system';
                                if (t !== 'light') document.getElementById('theme-toggle-light').style.display = 'none';
                                if (t !== 'dark') document.getElementById('theme-toggle-dark').style.display = 'none';
                                if (t !== 'system') document.getElementById('theme-toggle-system').style.display = 'none';
                            })();
                        </script>
                    </button>
                    @auth
                        {{-- Wishlist --}}
                        <div class="hidden md:flex relative items-center">
                            @livewire('wishlist-icon')
                        </div>
                    @endauth

                    {{-- Cart (visible on all screens, guests included — the cart is
                         session-based and login is only needed at the final checkout step) --}}
                    <div class="relative inline-flex items-center">
                        @livewire('cart-icon')
                    </div>

                    @auth
                        {{-- Notifications --}}
                        <div class="relative inline-flex items-center">
                            @livewire('notification-dropdown')
                        </div>

                        {{-- Divider --}}
                        <div class="hidden md:flex w-px h-5 bg-slate-200 dark:bg-slate-800 mx-1.5"></div>

                        {{-- User Dropdown --}}
                        <div class="hidden md:flex items-center">
                            <div class="relative ml-1" x-data="{ open: false }">
                                <button @click="open = !open" class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-900 border border-transparent hover:border-slate-200/50 dark:hover:border-slate-800 transition-all duration-200 active:scale-90 group">
                                    @if(auth()->user()->avatar_url)
                                        <img src="{{ auth()->user()->avatar_url }}" class="w-8 h-8 rounded-lg object-cover shadow-xs ring-2 ring-transparent group-hover:ring-primary-450 dark:group-hover:ring-primary-500 transition-all">
                                    @else
                                        <div class="w-8 h-8 rounded-lg bg-linear-to-br from-primary-500 to-primary-600 flex items-center justify-center text-white text-sm font-extrabold shadow-xs ring-2 ring-transparent group-hover:ring-primary-450 dark:group-hover:ring-primary-500 transition-all duration-300">
                                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <svg class="hidden sm:block w-3.5 h-3.5 text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300 transition-transform duration-300" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                </button>
                                
                                {{-- Dropdown Menu --}}
                                <div x-show="open" @click.away="open = false"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 scale-95 origin-top-right -translate-y-2"
                                     x-transition:enter-end="opacity-100 scale-100 origin-top-right translate-y-0"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 scale-100 origin-top-right translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-95 origin-top-right -translate-y-2"
                                     class="absolute right-0 mt-3 w-64 bg-white dark:bg-slate-900 rounded-2xl shadow-xl ring-1 ring-slate-100 dark:ring-slate-800 overflow-hidden z-50" style="display:none;">
                                    
                                    {{-- User Info Header --}}
                                    <div class="px-5 py-4 border-b border-slate-50 dark:border-slate-800/60 bg-slate-50/50 dark:bg-slate-800/20">
                                        <p class="text-sm font-bold text-slate-900 dark:text-slate-100 truncate">{{ auth()->user()->name }}</p>
                                        <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400 truncate mt-0.5">{{ auth()->user()->email }}</p>
                                    </div>

                                    <div class="p-2 space-y-0.5">
                                        <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-bold text-slate-600 dark:text-slate-300 hover:bg-primary-50 dark:hover:bg-primary-900/30 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                            </svg>
                                            Pengaturan Akun
                                        </a>
                                        <a href="{{ route('orders.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-bold text-slate-600 dark:text-slate-300 hover:bg-primary-50 dark:hover:bg-primary-900/30 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                                            Pesanan Saya
                                        </a>
                                        @if(auth()->user()->isAdmin())
                                            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-bold text-slate-600 dark:text-slate-300 hover:bg-primary-50 dark:hover:bg-primary-900/30 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75"/></svg>
                                                Admin Dashboard
                                            </a>
                                        @endif
                                    </div>
                                    <div class="px-2 py-2 border-t border-slate-100 dark:border-slate-800/60 bg-slate-50/30 dark:bg-slate-900/50">
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="flex items-center justify-center gap-2.5 w-full px-4 py-3 rounded-xl text-xs font-bold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                                                Keluar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="hidden md:flex items-center gap-2">
                            <a href="{{ route('login') }}" class="px-4 py-2 text-[13px] font-bold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition-colors duration-200">Masuk</a>
                            <a href="{{ route('register') }}" class="px-5 py-2.5 text-[13px] font-bold bg-primary-700 text-white rounded-xl hover:bg-primary-800 active:scale-95 transition-all duration-200 shadow-sm shadow-primary-700/20">Daftar</a>
                        </div>
                    @endauth

                    {{-- Mobile Menu Toggle --}}
                    <button @click="mobileMenu = !mobileMenu" aria-label="Menu Utama" class="md:hidden flex items-center justify-center w-10 h-10 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all active:scale-95">
                        <svg x-show="!mobileMenu" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                        <svg x-show="mobileMenu" style="display:none" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            {{-- Mobile Side Drawer Menu --}}
            <div x-show="mobileMenu" class="relative z-50 md:hidden" role="dialog" aria-modal="true" x-cloak style="display: none;">
                {{-- Backdrop --}}
                <div x-show="mobileMenu" 
                     x-transition:enter="transition-opacity ease-linear duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition-opacity ease-linear duration-300"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity cursor-pointer z-40"
                     @click="mobileMenu = false"></div>

                {{-- Drawer Panel --}}
                <div x-show="mobileMenu"
                     x-transition:enter="transform transition ease-in-out duration-300"
                     x-transition:enter-start="translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transform transition ease-in-out duration-300"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="translate-x-full"
                     class="fixed top-0 right-0 z-50 h-full w-full max-w-sm bg-white/95 dark:bg-slate-950/95 backdrop-blur-xl shadow-2xl flex flex-col border-l border-slate-100 dark:border-slate-900">
                    
                    {{-- Header --}}
                    <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100/80 dark:border-slate-900/60 bg-white/50 dark:bg-slate-950/50 backdrop-blur-md">
                        <a href="{{ route('home') }}" class="flex items-center gap-2.5" @click="mobileMenu = false">
                            <div class="w-8 h-8 rounded-lg bg-primary-50 dark:bg-primary-900/30 flex items-center justify-center">
                                <img src="{{ asset('images/logo.png') }}" alt="Gegares Logo" width="24" height="24" class="w-6 h-6 object-contain dark:brightness-110">
                            </div>
                            <span class="text-lg font-black tracking-tight text-slate-900 dark:text-white">gegares</span>
                        </a>
                        <button @click="mobileMenu = false" class="p-2 rounded-xl text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-900 transition-all border border-slate-100/50 dark:border-slate-900/50 active:scale-95">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" d="M6 18 18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Drawer Content --}}
                    <div class="flex-1 overflow-y-auto custom-scrollbar px-6 py-6 space-y-6">
                        {{-- 1. Search Bar --}}
                        <div class="relative">
                            <button @click="mobileMenu = false; $dispatch('open-search')" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/40 text-slate-400 dark:text-slate-500 hover:border-primary-400 dark:hover:border-primary-800 hover:bg-slate-100/50 dark:hover:bg-slate-900/60 hover:text-slate-600 dark:hover:text-slate-300 transition-all duration-200">
                                <svg class="w-4 h-4 text-slate-400 dark:text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.35-4.35"/></svg>
                                <span class="text-xs font-bold text-left">Cari jajanan pasar...</span>
                            </button>
                        </div>

                        {{-- 2. User Profile Card or Auth Buttons --}}
                        <div class="p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30">
                            @auth
                                <div class="flex items-center gap-3.5 mb-4">
                                    @if(auth()->user()->avatar_url)
                                        <img src="{{ auth()->user()->avatar_url }}" class="w-12 h-12 rounded-xl object-cover shadow-sm ring-2 ring-primary-100 dark:ring-primary-950">
                                    @else
                                        <div class="w-12 h-12 rounded-xl bg-linear-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-base font-black shadow-sm ring-2 ring-primary-100 dark:ring-primary-950">
                                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="text-sm font-black text-slate-900 dark:text-slate-100 truncate tracking-tight">{{ auth()->user()->name }}</p>
                                        <p class="text-[11px] font-bold text-slate-400 dark:text-slate-400 truncate mt-0.5">{{ auth()->user()->email }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2.5">
                                    <a href="{{ route('settings.index') }}" @click="mobileMenu = false" class="group flex items-center justify-center gap-2 p-3 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-primary-500 dark:hover:border-primary-500 hover:bg-primary-500/5 dark:hover:bg-primary-500/5 text-slate-700 dark:text-slate-300 hover:text-primary-600 dark:hover:text-primary-400 transition-all duration-250 active:scale-97">
                                        <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 group-hover:text-primary-500 dark:group-hover:text-primary-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                        </svg>
                                        <span class="text-[10px] font-black uppercase tracking-wider">Profil</span>
                                    </a>
                                    <a href="{{ route('orders.index') }}" @click="mobileMenu = false" class="group flex items-center justify-center gap-2 p-3 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-primary-500 dark:hover:border-primary-500 hover:bg-primary-500/5 dark:hover:bg-primary-500/5 text-slate-700 dark:text-slate-300 hover:text-primary-600 dark:hover:text-primary-400 transition-all duration-250 active:scale-97">
                                        <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 group-hover:text-primary-500 dark:group-hover:text-primary-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                                        <span class="text-[10px] font-black uppercase tracking-wider">Pesanan</span>
                                    </a>
                                </div>
                            @else
                                <div class="grid grid-cols-2 gap-3">
                                    <a href="{{ route('login') }}" @click="mobileMenu = false" class="flex items-center justify-center px-4 py-3 text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 active:scale-97 transition-all rounded-xl">Masuk</a>
                                    <a href="{{ route('register') }}" @click="mobileMenu = false" class="flex items-center justify-center px-4 py-3 text-xs font-bold bg-primary-600 text-white hover:bg-primary-700 active:scale-97 transition-all rounded-xl shadow-sm shadow-primary-500/10">Daftar</a>
                                </div>
                            @endauth
                        </div>

                        {{-- 3. Navigation Links --}}
                        <div class="space-y-1">
                            <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest px-3 mb-2.5">Menu Utama</p>
                            @foreach($nav as $item)
                                @php $isActive = request()->routeIs($item['route']); @endphp
                                <a href="{{ route($item['route']) }}" @click="mobileMenu = false"
                                   class="group flex items-center justify-between px-3.5 py-3 rounded-xl text-sm font-bold transition-all duration-200 
                                          {{ $isActive ? 'bg-primary-500/10 text-primary-600 dark:text-primary-400 font-black' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-900/40 hover:text-slate-900 dark:hover:text-white' }}">
                                    <div class="flex items-center gap-3.5">
                                        @if($item['route'] === 'home')
                                            <svg class="w-5 h-5 {{ $isActive ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                                        @elseif($item['route'] === 'products.index')
                                            <svg class="w-5 h-5 {{ $isActive ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                                        @elseif($item['route'] === 'about')
                                            <svg class="w-5 h-5 {{ $isActive ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8h.01" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 12v4" />
                                            </svg>
                                        @elseif($item['route'] === 'contact')
                                            <svg class="w-5 h-5 {{ $isActive ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-2.824-1.28-5.716-4.172-7-7l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                                        @endif
                                        <span>{{ $item['label'] }}</span>
                                    </div>
                                    <svg class="w-4 h-4 opacity-40 {{ $isActive ? 'text-primary-600 dark:text-primary-400 opacity-80 translate-x-0.5' : 'group-hover:translate-x-1 group-hover:opacity-75' }} transition-all" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                                </a>
                            @endforeach
                        </div>

                        {{-- 4. Favorites & Activity Links --}}
                        @auth
                            <div class="space-y-1 pt-4 border-t border-slate-200/80 dark:border-slate-800/60">
                                <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest px-3 mb-2.5">Aktivitas Saya</p>
                                
                                {{-- Wishlist Link --}}
                                <a href="#" @click.prevent="mobileMenu = false; $dispatch('toggle-wishlist')"
                                   class="group flex items-center justify-between px-3.5 py-3 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100/80 dark:hover:bg-slate-900/40 hover:text-slate-900 dark:hover:text-white transition-all">
                                    <div class="flex items-center gap-3.5">
                                        <svg class="w-5 h-5 text-slate-400 dark:text-slate-500 group-hover:text-red-500 dark:group-hover:text-red-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                                        <span>Wishlist</span>
                                    </div>
                                    <svg class="w-4 h-4 opacity-40 group-hover:translate-x-1 group-hover:opacity-75 transition-all" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                                </a>

                                {{-- Notifications Link --}}
                                <a href="{{ route('settings.index') }}#notifications" @click="mobileMenu = false"
                                   class="group flex items-center justify-between px-3.5 py-3 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100/80 dark:hover:bg-slate-900/40 hover:text-slate-900 dark:hover:text-white transition-all">
                                    <div class="flex items-center gap-3.5">
                                        <svg class="w-5 h-5 text-slate-400 dark:text-slate-500 group-hover:text-blue-500 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                                        </svg>
                                        <span>Notifikasi</span>
                                    </div>
                                    <svg class="w-4 h-4 opacity-40 group-hover:translate-x-1 group-hover:opacity-75 transition-all" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                                </a>
                            </div>
                        @endauth

                        {{-- 5. Quick Theme Toggle Card --}}
                        <div class="space-y-1 pt-4 border-t border-slate-200/80 dark:border-slate-800/60">
                            <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest px-3 mb-2.5">Preferensi Tampilan</p>
                            <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50/50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800">
                                <span class="text-xs font-bold text-slate-600 dark:text-slate-300 pl-2">Mode Tampilan</span>
                                
                                <div class="flex gap-1 bg-slate-200/40 dark:bg-slate-800/80 p-0.5 rounded-lg border border-slate-200/10">
                                    <button @click="setTheme('light')" :class="theme === 'light' ? 'bg-white dark:bg-slate-700 text-amber-500 shadow-sm border border-slate-100 dark:border-slate-600' : 'text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'" class="p-2 rounded-md transition-all active:scale-90">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0z"/></svg>
                                    </button>
                                    <button @click="setTheme('dark')" :class="theme === 'dark' ? 'bg-white dark:bg-slate-700 text-indigo-500 shadow-sm border border-slate-100 dark:border-slate-600' : 'text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'" class="p-2 rounded-md transition-all active:scale-90">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998z"/></svg>
                                    </button>
                                    <button @click="setTheme('system')" :class="theme === 'system' ? 'bg-white dark:bg-slate-700 text-primary-500 shadow-sm border border-slate-100 dark:border-slate-600' : 'text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'" class="p-2 rounded-md transition-all active:scale-90">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 6. Footer (Admin & Logout) --}}
                    <div class="px-6 py-5 border-t border-slate-200/80 dark:border-slate-800/60 bg-slate-50/50 dark:bg-slate-900/30 text-left">
                        @auth
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" @click="mobileMenu = false"
                                   class="flex items-center gap-3 px-3.5 py-3 rounded-xl text-xs font-bold text-primary-600 dark:text-primary-400 hover:bg-primary-500/10 hover:text-primary-700 dark:hover:text-primary-300 transition-colors mb-3 border border-dashed border-primary-500/20">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75"/></svg>
                                    <span>Admin Dashboard</span>
                                </a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center justify-center gap-2.5 w-full px-4 py-3 rounded-xl text-xs font-bold text-red-655 dark:text-red-400 bg-red-500/10 dark:bg-red-500/5 hover:bg-red-500/15 active:scale-97 transition-all">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                                    <span>Keluar</span>
                                </button>
                            </form>
                        @else
                            <div class="text-center text-[10px] font-semibold text-slate-500 dark:text-slate-500">
                                &copy; {{ date('Y') }} Gegares. Semua hak cipta dilindungi.
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </nav>

    {{-- ─── TOAST NOTIFICATIONS ─── --}}
    <div x-data="toastManager()" @toast.window="addToast($event.detail)"
         class="fixed top-20 right-4 z-100 flex flex-col gap-3 w-80">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="toast.visible"
                 x-transition:enter="toast-enter" x-transition:leave="toast-exit"
                 :class="{
                    'bg-emerald-50 dark:bg-emerald-950/80 border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-400': toast.type === 'success',
                    'bg-red-50 dark:bg-red-950/80 border-red-200 dark:border-red-800 text-red-800 dark:text-red-400': toast.type === 'error',
                    'bg-amber-50 dark:bg-amber-950/80 border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-400': toast.type === 'warning',
                    'bg-blue-50 dark:bg-blue-950/80 border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-400': toast.type === 'info',
                 }"
                 class="flex items-center gap-3 px-4 py-3 rounded-xl border shadow-lg backdrop-blur-sm transition-colors duration-300">
                <span x-text="toast.message" class="text-sm font-medium flex-1"></span>
                <button @click="removeToast(toast.id)" class="text-current/60 hover:text-current transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </template>
    </div>

    {{-- ─── CART DRAWER ─── --}}
    {{-- Cart drawer is rendered for guests too: it listens for add-to-cart and
         emits cart-updated, so without it a guest's "Tambah ke Keranjang" button
         has no listener and hangs on its loading state. Wishlist stays per-user. --}}
    @livewire('cart-drawer')
    @auth
        @livewire('wishlist-drawer')
    @endauth

    {{-- ─── GLOBAL SEARCH ─── --}}
    @livewire('global-search')

    {{-- ─── AI CHATBOT ─── --}}
    @livewire('chatbot')

    {{-- ─── MAIN CONTENT ─── --}}
    <main class="flex-1">
        {{-- Session Toasts Data --}}
        @if(session('success')) <div id="toast-success" data-message="{{ session('success') }}" class="hidden"></div> @endif
        @if(session('error')) <div id="toast-error" data-message="{{ session('error') }}" class="hidden"></div> @endif
        @if(session('warning')) <div id="toast-warning" data-message="{{ session('warning') }}" class="hidden"></div> @endif

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                ['success', 'error', 'warning'].forEach(type => {
                    const el = document.getElementById(`toast-${type}`);
                    if (el) {
                        window.dispatchEvent(new CustomEvent('toast', { 
                            detail: { type: type, message: el.dataset.message }
                        }));
                    }
                });
            });
        </script>
        @yield('content')
    </main>

    {{-- ─── FOOTER ─── --}}
    <footer class="bg-white dark:bg-slate-900 border-t border-slate-200/60 dark:border-slate-800/60 mt-auto transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-8">
            <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-12 mb-12">
                {{-- Brand & About --}}
                <div class="md:col-span-1 lg:col-span-2">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 group mb-4">
                        <img src="{{ asset('images/logo.png') }}" alt="Gegares Logo" width="48" height="48" class="w-12 h-12 object-contain dark:brightness-110 transition-all duration-300">
                        <span class="text-2xl font-extrabold tracking-tight text-primary-700 dark:text-primary-300">gegares</span>
                    </a>
                    <p class="mt-4 text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                        Gegares menghadirkan kelezatan jajanan pasar tradisional dengan kualitas premium. Dibuat segar setiap hari untuk memastikan rasa autentik yang Anda cintai.
                    </p>
                    @php
                        // Shared by the WhatsApp link and the payment logos below; the
                        // store settings are cached, so this costs no extra query.
                        $storeFooter = new \Illuminate\Support\Fluent(\Illuminate\Support\Facades\Cache::remember('store_settings', 86400, fn() => (\App\Models\StoreSetting::first() ?? new \App\Models\StoreSetting())->toArray()));

                        // The number was hardcoded here, so it ignored the store settings
                        // entirely and kept pointing at the placeholder.
                        $footerWa = preg_replace('/[^0-9]/', '', $storeFooter->contact_whatsapp ?? $storeFooter->contact_phone ?? '6281234567890');
                        if (str_starts_with($footerWa, '0')) {
                            $footerWa = '62' . substr($footerWa, 1);
                        }
                    @endphp
                    <div class="mt-6 flex items-center gap-4">
                        <a href="https://wa.me/{{ $footerWa }}" target="_blank" class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center hover:bg-emerald-600 dark:hover:bg-emerald-600 hover:text-white transition-all duration-300 shadow-sm" title="WhatsApp">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.72.937 3.659 1.432 5.628 1.433h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-xl bg-pink-50 dark:bg-pink-900/30 text-pink-600 dark:text-pink-400 flex items-center justify-center hover:bg-pink-600 dark:hover:bg-pink-600 hover:text-white transition-all duration-300 shadow-sm" title="Instagram">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                    </div>
                </div>

                {{-- Quick Links --}}
                <div>
                    <p class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest mb-6">Navigasi</p>
                    <ul class="space-y-4">
                        <li><a href="{{ route('home') }}" class="text-sm text-slate-500 dark:text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Beranda</a></li>
                        <li><a href="{{ route('products.index') }}" class="text-sm text-slate-500 dark:text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Produk Kami</a></li>
                        <li><a href="{{ route('about') }}" class="text-sm text-slate-500 dark:text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Tentang Gegares</a></li>
                        <li><a href="{{ route('contact') }}" class="text-sm text-slate-500 dark:text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Hubungi Kami</a></li>
                    </ul>
                </div>

                {{-- Shopping Features --}}
                <div>
                    <p class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest mb-6">Belanja</p>
                    <ul class="space-y-4">
                        <li><a href="{{ route('wishlist') }}" class="text-sm text-slate-500 dark:text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Wishlist Saya</a></li>
                        <li><a href="{{ auth()->check() ? route('orders.index') : route('login') }}" class="text-sm text-slate-500 dark:text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Status Pesanan</a></li>
                        <li><a href="{{ route('products.index', ['featured' => 1]) }}" class="text-sm text-slate-500 dark:text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Produk Unggulan</a></li>
                        <li><a href="{{ route('products.index') }}" class="text-sm text-slate-500 dark:text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Jajanan Terbaru</a></li>
                    </ul>
                </div>

                {{-- Support --}}
                <div>
                    <p class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest mb-6">Bantuan</p>
                    <ul class="space-y-4">
                        <li><a href="{{ route('home') }}#faq" class="text-sm text-slate-500 dark:text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">FAQ</a></li>
                        <li><a href="{{ route('shipping') }}" class="text-sm text-slate-500 dark:text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Info Pengiriman</a></li>
                        <li><a href="{{ route('privacy') }}" class="text-sm text-slate-500 dark:text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Kebijakan Privasi</a></li>
                        <li><a href="{{ route('terms') }}" class="text-sm text-slate-500 dark:text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Syarat & Ketentuan</a></li>
                    </ul>
                </div>
            </div>

            {{-- Bottom Footer --}}
            <div class="border-t border-slate-100 dark:border-slate-800 pt-8 mt-8 flex flex-col md:flex-row justify-between items-center gap-4 transition-colors">
                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400 transition-colors">&copy; {{ date('Y') }} Gegares. Semua hak cipta dilindungi.</p>
                @php
                    $paymentLogos = $storeFooter->payment_logos ?? [];
                @endphp
                <div class="flex items-center gap-6">
                    @forelse($paymentLogos as $logo)
                        <img src="{{ asset('storage/' . $logo) }}" alt="Metode Pembayaran" class="h-5 w-auto max-w-24 object-contain hover:scale-110 transition-all duration-300" onerror="this.style.display='none'">
                    @empty
                        {{-- Default logos when none configured in store settings --}}
                        <img src="{{ asset('images/bca.png') }}" alt="BCA" width="401" height="126" class="h-4 w-auto hover:scale-110 transition-all duration-300 dark:brightness-200 dark:contrast-150">
                        <img src="{{ asset('images/gopay.png') }}" alt="Gopay" width="1280" height="326" class="h-3 w-auto hover:scale-110 transition-all duration-300 dark:invert">
                        <img src="{{ asset('images/qris.png') }}" alt="QRIS" width="1280" height="486" class="h-4 w-auto hover:scale-110 transition-all duration-300 dark:invert">
                    @endforelse
                </div>
            </div>
        </div>
    </footer>

    @livewireScripts
    @stack('scripts')
    <script>
        function toastManager() {
            return {
                toasts: [],
                addToast(detail) {
                    const id = Date.now();
                    this.toasts.push({ id, ...detail, visible: true });
                    setTimeout(() => this.removeToast(id), 4000);
                },
                removeToast(id) {
                    const t = this.toasts.find(t => t.id === id);
                    if (t) t.visible = false;
                    setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 300);
                }
            };
        }
    </script>
    {{-- ─── HASH NAVIGATION ─── --}}
    <script>
        document.addEventListener('livewire:initialized', () => {
            if (window.location.hash === '#wishlist') {
                Livewire.dispatch('toggle-wishlist');
                // Clear the hash without reloading
                history.replaceState(null, null, ' ');
            }
        });
    </script>
    {{-- ─── SCROLL REVEAL OBSERVER ─── --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const observerOptions = {
                threshold: 0.15,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
                        // Optional: unobserve once revealed
                        // observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            const revealElements = document.querySelectorAll('.reveal');
            revealElements.forEach(el => observer.observe(el));
        });
    </script>
</body>
</html>
