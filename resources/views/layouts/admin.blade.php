<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Gegares Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        const theme = localStorage.getItem('theme') || 'system';
        if (theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    @stack('styles')
    @livewireStyles
</head>

<body class="bg-slate-50 dark:bg-slate-950 font-sans antialiased transition-colors duration-300" x-data="{ 
        sidebarOpen: true, 
        sidebarMobile: false,
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
            window.dispatchEvent(new CustomEvent('theme-changed', { detail: newTheme }));
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
      }" x-init="applyTheme()" @theme-update.window="setTheme($event.detail)">
    @php
        $menuGroups = [
            'Utama' => [
                ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>'],
            ],
            'Katalog & Promosi' => [
                ['route' => 'admin.categories.index', 'label' => 'Kategori', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 7.125C2.25 6.504 2.754 6 3.375 6h6c.621 0 1.125.504 1.125 1.125v3.75c0 .621-.504 1.125-1.125 1.125h-6a1.125 1.125 0 0 1-1.125-1.125v-3.75ZM14.25 8.625c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v8.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 0 1-1.125-1.125v-8.25ZM3.75 16.125c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v2.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 0 1-1.125-1.125v-2.25Z"/>'],
                ['route' => 'admin.products.index', 'label' => 'Produk', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/>'],
                ['route' => 'admin.coupons.index', 'label' => 'Kode Kupon', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V4.245c0-.754-.726-1.294-1.453-1.096A60.07 60.07 0 0 1 2.25 5.25v13.5Zm15.93-8.25h.008v.008h-.008v-.008ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>'],
            ],
            'Penjualan & Pelanggan' => [
                ['route' => 'admin.orders.index', 'label' => 'Pesanan', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>'],
                ['route' => 'admin.reviews.index', 'label' => 'Ulasan', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/>'],
                ['route' => 'admin.users.index', 'label' => 'Pengguna', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v-.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>'],
            ],
            'Konfigurasi Toko' => [
                ['route' => 'admin.settings.store', 'label' => 'Pengaturan Toko', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />'],
            ],
        ];
    @endphp

    <div class="flex min-h-screen">
        {{-- Mobile Sidebar --}}
        <div x-show="sidebarMobile" class="fixed inset-0 z-40 lg:hidden" style="display:none;">
            {{-- Backdrop with improved transition --}}
            <div x-show="sidebarMobile" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 backdrop-blur-none"
                x-transition:enter-end="opacity-100 backdrop-blur-sm"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 backdrop-blur-sm"
                x-transition:leave-end="opacity-0 backdrop-blur-none" class="fixed inset-0 bg-slate-900/50"
                @click="sidebarMobile = false"></div>

            <aside x-show="sidebarMobile" x-transition:enter="transition ease-in-out duration-300 transform"
                x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in-out duration-300 transform"
                x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
                class="relative flex flex-col w-72 max-w-[80vw] h-full bg-white dark:bg-slate-900 shadow-2xl overflow-y-auto border-r dark:border-slate-800">
                {{-- Logo Mobile --}}
                <div class="flex items-center justify-between px-6 h-16 border-b border-slate-100 shrink-0">
                    <span class="text-xl font-extrabold text-primary-700 dark:text-primary-400">gegares</span>
                    <button @click="sidebarMobile = false"
                        class="p-2 -mr-2 rounded-lg text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Menu Mobile --}}
                <nav class="flex-1 py-4 px-3 space-y-6 overflow-y-auto">
                    @foreach($menuGroups as $groupLabel => $items)
                        <div class="space-y-1">
                            <h3
                                class="px-3 text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-2">
                                {{ $groupLabel }}</h3>
                            <div class="space-y-1">
                                @foreach($items as $item)
                                    <a href="{{ route($item['route']) }}"
                                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs($item['route'] . '*') ? 'bg-primary-50 dark:bg-primary-900/40 text-primary-700 dark:text-primary-400' : 'text-slate-600 dark:text-slate-400 hover:text-primary-700 dark:hover:text-primary-400 hover:bg-primary-50/50 dark:hover:bg-primary-900/20' }}">
                                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                            stroke="currentColor">{!! $item['icon'] !!}</svg>
                                        <span>{{ $item['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </nav>

                {{-- Bottom Mobile --}}
                <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                    <a href="{{ route('home') }}"
                        class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium text-slate-500 dark:text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50/50 dark:hover:bg-primary-900/20 transition-all">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                        </svg>
                        <span>Ke Website</span>
                    </a>
                </div>
            </aside>
        </div>

        {{-- Desktop Sidebar --}}
        <aside
            class="hidden lg:flex flex-col bg-white dark:bg-slate-900 border-r border-slate-200/60 dark:border-slate-800/60 sticky top-0 h-screen transition-all duration-300 ease-in-out shrink-0"
            :class="sidebarOpen ? 'w-64' : 'w-20'">
            {{-- Logo --}}
            <div class="flex items-center justify-between px-6 h-16 border-b border-slate-100 dark:border-slate-800">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                    <span class="text-xl font-extrabold text-primary-700 dark:text-primary-400" x-show="sidebarOpen"
                        x-transition>gegares</span>
                    <span class="text-xl font-extrabold text-primary-700 dark:text-primary-400" x-show="!sidebarOpen"
                        x-transition>G</span>
                </a>
                <button @click="sidebarOpen = !sidebarOpen"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
            </div>

            {{-- Menu --}}
            <nav
                class="flex-1 overflow-y-auto py-4 px-3 space-y-6 scrollbar-thin scrollbar-thumb-slate-200 dark:scrollbar-thumb-slate-800">
                @foreach($menuGroups as $groupLabel => $items)
                    <div class="space-y-1">
                        <h3 x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 -translate-x-2"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            class="px-3 text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-2 whitespace-nowrap">
                            {{ $groupLabel }}
                        </h3>
                        <div x-show="!sidebarOpen" class="h-px bg-slate-100 dark:bg-slate-800/50 mx-2 mb-2"></div>

                        <div class="space-y-1">
                            @foreach($items as $item)
                                <a href="{{ route($item['route']) }}"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs($item['route'] . '*') ? 'bg-primary-50 dark:bg-primary-900/40 text-primary-700 dark:text-primary-400' : 'text-slate-600 dark:text-slate-400 hover:text-primary-700 dark:hover:text-primary-400 hover:bg-primary-50/50 dark:hover:bg-primary-900/20' }}"
                                    :title="!sidebarOpen ? '{{ $item['label'] }}' : ''">
                                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor">{!! $item['icon'] !!}</svg>
                                    <span x-show="sidebarOpen" x-transition
                                        class="whitespace-nowrap">{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </nav>

            {{-- Bottom --}}
            <div class="p-3 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('home') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-500 dark:text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50/50 dark:hover:bg-primary-900/20 transition-all">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                    <span x-show="sidebarOpen" x-transition>Ke Website</span>
                </a>
            </div>
        </aside>

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Top Navbar --}}
            <header
                class="sticky top-0 z-20 bg-white/80 dark:bg-slate-900/80 backdrop-blur-lg border-b border-slate-200/60 dark:border-slate-800/60 h-16 flex items-center px-6 transition-colors duration-300">
                <button @click="sidebarMobile = !sidebarMobile"
                    class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100 mr-3">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
                <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">@yield('page_title', 'Dashboard')</h2>
                <div class="ml-auto flex items-center gap-3">
                    {{-- Theme Toggle --}}
                    <button @click="toggleTheme()"
                        class="p-2 rounded-lg text-slate-500 dark:text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all duration-200"
                        title="Ganti Tema">
                        {{-- Light --}}
                        <svg x-show="theme === 'light'" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0z" />
                        </svg>
                        {{-- Dark --}}
                        <svg x-show="theme === 'dark'" class="w-5 h-5" x-cloak fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21.752 15.002A9.718 9.718 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998z" />
                        </svg>
                        {{-- System --}}
                        <svg x-show="theme === 'system'" class="w-5 h-5" x-cloak fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
                        </svg>
                    </button>
                    {{-- Profile --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                            class="flex items-center gap-2 px-3 py-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors duration-200">
                            <div
                                class="w-8 h-8 rounded-lg bg-linear-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-sm font-semibold shadow-sm">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                            <span
                                class="text-sm font-medium text-slate-700 dark:text-slate-300 hidden sm:inline">{{ auth()->user()->name }}</span>
                        </button>
                        <div x-show="open" @click.away="open = false"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-900 rounded-xl shadow-lg ring-1 ring-slate-200/60 dark:ring-slate-800 py-1.5 z-50 overflow-hidden"
                            style="display:none;">
                            <a href="{{ route('home') }}"
                                class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 hover:text-primary-700 dark:hover:text-primary-400 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                </svg>
                                Ke Website
                            </a>
                            <div class="border-t border-slate-100 dark:border-slate-800 my-1"></div>
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit"
                                    class="flex items-center gap-2 w-full text-left px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/40 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                    </svg>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Page Content --}}
            <main class="flex-1 p-6">
                @yield('content')
                {{ $slot ?? '' }}
            </main>
        </div>
    </div>
    @livewireScripts

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
                        detail: { type: type === 'warning' ? 'info' : (type === 'error' ? 'error' : 'success'), message: el.dataset.message }
                    }));
                }
            });
        });
    </script>

    {{-- Toast Manager --}}
    <div x-data="toastManager()" @toast.window="addToast($event.detail)"
        class="fixed top-20 right-4 z-50 flex flex-col gap-3 w-80">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="toast.visible" x-transition :class="{ 
                    'bg-emerald-50 dark:bg-emerald-950/80 border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-400': toast.type==='success', 
                    'bg-red-50 dark:bg-red-950/80 border-red-200 dark:border-red-800 text-red-800 dark:text-red-400': toast.type==='error',
                    'bg-slate-900 dark:bg-slate-800 text-white border-slate-700': toast.type==='info'
                 }"
                class="flex items-center gap-3 px-4 py-3 rounded-xl border shadow-lg backdrop-blur-sm transition-colors duration-300">
                <span x-text="toast.message" class="text-sm font-medium flex-1"></span>
                <button @click="removeToast(toast.id)" class="text-current/60 hover:text-current transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </template>
    </div>
    <script>
        function toastManager() { return { toasts: [], addToast(d) { const id = Date.now(); this.toasts.push({ id, ...d, visible: true }); setTimeout(() => this.removeToast(id), 4000) }, removeToast(id) { const t = this.toasts.find(t => t.id === id); if (t) t.visible = false; setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id) }, 300) } } }
    </script>
    @stack('scripts')
</body>

</html>