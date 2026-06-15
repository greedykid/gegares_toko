<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Masuk') — Gegares</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300..700&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        const theme = localStorage.getItem('theme') || 'system';
        if (theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    @livewireStyles
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
    @stack('styles')
</head>

<body
    class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 transition-colors duration-300"
    x-data="{ 
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
      }" x-init="applyTheme()">

    <div class="min-h-screen grid grid-cols-1 lg:grid-cols-12 overflow-hidden relative">

        {{-- Floating Theme Toggle Button --}}
        <div class="hidden lg:block absolute top-6 right-6 z-50">
            <button @click="toggleTheme()"
                class="flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 hover:text-amber-500 dark:hover:text-amber-400 shadow-xs active:scale-95 transition-all duration-200"
                title="Ganti Tema">
                <svg x-show="theme === 'light'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0z" />
                </svg>
                <svg x-show="theme === 'dark'" class="w-5 h-5" x-cloak fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21.752 15.002A9.718 9.718 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998z" />
                </svg>
                <svg x-show="theme === 'system'" class="w-5 h-5" x-cloak fill="none" viewBox="0 0 24 24"
                    stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
                </svg>
            </button>
        </div>

        {{-- Left Column: Logo & Store info (40% width on large screens) --}}
        <div
            class="hidden lg:flex lg:col-span-5 bg-linear-to-br from-primary-600 via-primary-700 to-primary-850 p-12 flex-col justify-between relative overflow-hidden">
            {{-- Decorative Rings --}}
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -top-12 -left-12 w-64 h-64 border-4 border-white/10 rounded-full blur-xs"></div>
                <div class="absolute bottom-24 -right-12 w-96 h-96 border-8 border-white/5 rounded-full blur-xs"></div>
                <div class="absolute top-1/2 left-1/3 w-72 h-72 bg-white/5 rounded-full blur-3xl"></div>
            </div>

            {{-- Back Button --}}
            <div class="z-10">
                <a href="{{ route('home') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white/10 hover:bg-white/20 border border-white/10 text-white text-xs font-bold rounded-xl transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Kembali ke Toko
                </a>
            </div>

            {{-- Branding details --}}
            <div class="z-10 my-auto pt-20">
                <div class="w-72 h-72 rounded-3xl p-5 mb-2 flex items-center justify-center group">
                    <img src="{{ asset('images/logo.png') }}" alt="Gegares Logo"
                        class="w-full h-full object-contain group-hover:scale-105 group-hover:rotate-6 transition-transform duration-500">
                </div>
                <h2 class="text-4xl font-extrabold text-white tracking-tight leading-none mb-4">gegares</h2>
                <p class="text-white/80 text-sm max-w-sm leading-relaxed">
                    Jajanan Pasar Tradisional Premium & Kue Pilihan. Selalu segar, dibuat setiap hari dengan
                    cinta dan bahan alami berkualitas tinggi.
                </p>
            </div>

            {{-- Footer Text --}}
            <div class="z-10 text-white/50 text-[10px] uppercase font-bold tracking-widest">
                &copy; {{ date('Y') }} Gegares.
            </div>
        </div>

        {{-- Right Column: Forms Content (60% width on large screens) --}}
        <div
            class="col-span-1 lg:col-span-7 flex flex-col justify-center bg-slate-50 dark:bg-slate-950 transition-colors duration-300 min-h-screen relative overflow-y-auto">
            {{-- Mobile Logo & Brand (hidden on large screens) --}}
            <div
                class="lg:hidden p-6 absolute top-0 left-0 right-0 flex items-center justify-between z-40 bg-slate-50/80 dark:bg-slate-950/80 backdrop-blur-md border-b border-slate-100 dark:border-slate-900 transition-colors">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <div
                        class="w-16 h-16 rounded-xl bg-primary-50 dark:bg-primary-900/30 p-3 flex items-center justify-center shadow-sm">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-full h-full object-contain">
                    </div>
                    <span class="text-lg font-black text-slate-900 dark:text-white">gegares</span>
                </a>
                <div class="flex items-center gap-2">
                    {{-- Mobile Theme Toggle --}}
                    <button @click="toggleTheme()"
                        class="p-2.5 text-slate-400 hover:text-amber-500 dark:hover:text-amber-400 active:scale-95 transition-all duration-200"
                        title="Ganti Tema">
                        <svg x-show="theme === 'light'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0z" />
                        </svg>
                        <svg x-show="theme === 'dark'" class="w-5 h-5" x-cloak fill="none" viewBox="0 0 24 24"
                            stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21.752 15.002A9.718 9.718 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998z" />
                        </svg>
                        <svg x-show="theme === 'system'" class="w-5 h-5" x-cloak fill="none" viewBox="0 0 24 24"
                            stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
                        </svg>
                    </button>
                    {{-- Home Button --}}
                    <a href="{{ route('home') }}"
                        class="p-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                    </a>
                </div>
            </div>

            <div class="w-full @yield('form_width_class', 'max-w-md') mx-auto px-6 pt-36 pb-16 lg:py-24">
                @yield('content')
            </div>
        </div>

    </div>

    @livewireScripts
    @stack('scripts')
</body>

</html>