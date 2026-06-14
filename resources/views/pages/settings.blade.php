@extends('layouts.app')

@section('title', 'Pengaturan Akun')

@section('content')
<div class="bg-slate-50 dark:bg-slate-950 min-h-screen py-10 lg:py-16 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-10 lg:mb-14 text-center lg:text-left">
            <h1 class="text-3xl lg:text-4xl font-black text-slate-900 dark:text-white tracking-tight">Pengaturan Akun</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm lg:text-base mt-2 font-medium">Kelola informasi profil, referensi, dan keamanan Anda dalam satu tempat.</p>
        </div>

        <div class="flex flex-col lg:flex-row gap-8 lg:gap-12" x-data="{ activeTab: window.location.hash || '#profile' }" @hashchange.window="activeTab = window.location.hash || '#profile'">
            {{-- Navigation --}}
            <div class="w-full lg:w-72 shrink-0">
                <div class="sticky top-28 bg-white/80 dark:bg-slate-900/60 backdrop-blur-xl p-3 sm:p-4 rounded-4xl ring-1 ring-slate-200/50 dark:ring-slate-800/80 shadow-sm">
                    <nav class="flex flex-row lg:flex-col gap-2 overflow-x-auto hide-scrollbar">
                        <a href="#profile" @click="activeTab = '#profile'" 
                           :class="activeTab == '#profile' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-md' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white'" 
                           class="flex shrink-0 items-center justify-center lg:justify-start gap-3 px-5 py-3.5 text-sm font-bold rounded-2xl transition-all duration-300">
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                            <span class="hidden sm:block">Profil</span>
                        </a>
                        <a href="#notifications" @click="activeTab = '#notifications'" 
                           :class="activeTab == '#notifications' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-md' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white'" 
                           class="flex shrink-0 items-center justify-center lg:justify-start gap-3 px-5 py-3.5 text-sm font-bold rounded-2xl transition-all duration-300">
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
                            <span class="hidden sm:block">Notifikasi</span>
                        </a>
                        <a href="#security" @click="activeTab = '#security'" 
                           :class="activeTab == '#security' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-md' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white'" 
                           class="flex shrink-0 items-center justify-center lg:justify-start gap-3 px-5 py-3.5 text-sm font-bold rounded-2xl transition-all duration-300">
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751A11.956 11.956 0 0 1 12 2.714Z" /></svg>
                            <span class="hidden sm:block">Keamanan</span>
                        </a>
                        <a href="#addresses" @click="activeTab = '#addresses'" 
                           :class="activeTab == '#addresses' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-md' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white'" 
                           class="flex shrink-0 items-center justify-center lg:justify-start gap-3 px-5 py-3.5 text-sm font-bold rounded-2xl transition-all duration-300">
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                            <span class="hidden sm:block">Alamat</span>
                        </a>
                    </nav>
                </div>
            </div>

            {{-- Content Areas --}}
            <div class="flex-1 min-w-0 w-full text-left">
                
                {{-- Profile Section --}}
                <div id="profile-section" x-show="activeTab === '#profile'" x-cloak class="block w-full bg-white dark:bg-slate-900/60 rounded-4xl shadow-sm ring-1 ring-slate-200/50 dark:ring-slate-800/80 backdrop-blur-xl">
                    <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-10 space-y-8 sm:space-y-10">
                        @csrf
                        @method('PUT')
                        
                        <div class="border-b border-slate-100 dark:border-slate-800 pb-6">
                            <h2 class="text-xl font-extrabold text-slate-900 dark:text-white">Informasi Profil</h2>
                            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1 font-medium">Perbarui foto dan rincian pribadi Anda.</p>
                        </div>
                        
                        {{-- Avatar Upload --}}
                        <div class="flex flex-col sm:flex-row sm:items-center gap-6 sm:gap-8" x-data="{ photoPreview: null }">
                            <div class="relative shrink-0 w-32 h-32 group mx-auto sm:mx-0">
                                <template x-if="!photoPreview">
                                    @if($user->avatar)
                                        <img src="{{ asset('storage/' . $user->avatar) }}" class="w-full h-full rounded-4xl object-cover shadow-md border-4 border-white dark:border-slate-900 ring-1 ring-slate-200/50 dark:ring-slate-800">
                                    @else
                                        <div class="w-full h-full rounded-4xl bg-linear-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-5xl font-black shadow-md border-4 border-white dark:border-slate-900 ring-1 ring-slate-200/50 dark:ring-slate-800">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </template>
                                <template x-if="photoPreview">
                                    <img :src="photoPreview" class="w-full h-full rounded-4xl object-cover shadow-md border-4 border-white dark:border-slate-900 ring-1 ring-slate-200/50 dark:ring-slate-800">
                                </template>
                                
                                <label for="avatar" class="absolute -bottom-2 -right-2 w-11 h-11 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-2xl shadow-lg flex items-center justify-center cursor-pointer hover:scale-110 active:scale-95 transition-all z-10">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15a2.25 2.25 0 0 0 2.25-2.25V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" /></svg>
                                    <input type="file" name="avatar" id="avatar" class="hidden" @change="const file = $event.target.files[0]; if (file) { const reader = new FileReader(); reader.onload = (e) => { photoPreview = e.target.result; }; reader.readAsDataURL(file); }">
                                </label>
                            </div>
                            <div class="text-center sm:text-left">
                                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Foto Profil</h3>
                                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1 font-medium leading-relaxed max-w-xs">Format mendatar (JPG/PNG). Dianjurkan resolusi tinggi. Maksimal 2MB.</p>
                                @if($user->avatar)
                                    <button type="button" onclick="if(confirm('Yakin ingin menghapus foto profil?')){fetch('{{ route('settings.delete-avatar') }}',{method:'DELETE',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}}).then(()=>window.location.reload())}" class="mt-3 text-sm font-bold text-red-500 hover:text-red-700 transition-colors">Hapus Foto</button>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Nama Lengkap</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full px-5 py-4 rounded-xl border-2 border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-white focus:bg-white dark:focus:bg-slate-900 focus:ring-0 focus:border-primary-500 text-sm font-bold transition-all shadow-inner shadow-slate-100/50 dark:shadow-none">
                                @error('name') <p class="mt-2 text-xs font-bold text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Alamat Email</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full px-5 py-4 rounded-xl border-2 border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-white focus:bg-white dark:focus:bg-slate-900 focus:ring-0 focus:border-primary-500 text-sm font-bold transition-all shadow-inner shadow-slate-100/50 dark:shadow-none">
                                @error('email') <p class="mt-2 text-xs font-bold text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div class="md:col-span-2 lg:col-span-1">
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Nomor WhatsApp</label>
                                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="Contoh: 628123456789" class="w-full px-5 py-4 rounded-xl border-2 border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-white focus:bg-white dark:focus:bg-slate-900 focus:ring-0 focus:border-primary-500 text-sm font-bold transition-all shadow-inner shadow-slate-100/50 dark:shadow-none">
                                @error('phone') <p class="mt-2 text-xs font-bold text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="pt-6">
                            <button type="submit" class="relative overflow-hidden group w-full sm:w-auto px-8 py-4 bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-sm font-bold rounded-xl shadow-md active:scale-95 transition-all">
                                <span class="relative z-10 flex items-center justify-center gap-2">
                                    Simpan Perubahan
                                </span>
                                <div class="absolute inset-0 h-full w-full bg-linear-to-r from-transparent via-white/20 dark:via-slate-900/10 to-transparent -translate-x-full group-hover:animate-[shimmer_1.5s_infinite]"></div>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Appearance Section --}}
                <div id="appearance" x-show="activeTab === '#profile'" x-cloak class="mt-8 block w-full bg-white dark:bg-slate-900/60 rounded-4xl shadow-sm ring-1 ring-slate-200/50 dark:ring-slate-800/80 backdrop-blur-xl">
                    <div class="p-6 sm:p-10 border-b border-slate-100 dark:border-slate-800">
                        <h2 class="text-xl font-extrabold text-slate-900 dark:text-white">Tampilan & Tema</h2>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mt-1 font-medium">Pilih gaya favorit untuk menjelajahi Gegares.</p>
                    </div>
                    <div class="p-6 sm:p-10 py-8">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                            {{-- Light Theme --}}
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="theme_select" value="light" class="sr-only peer" @change="$dispatch('theme-update', 'light')" :checked="theme === 'light'">
                                <div class="p-5 rounded-3xl border-2 border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 peer-checked:border-primary-500 peer-checked:bg-white dark:peer-checked:bg-slate-800 transition-all hover:border-slate-300 dark:hover:border-slate-700 shadow-sm peer-checked:shadow-primary-500/10">
                                    <div class="w-full aspect-video rounded-xl bg-white shadow-sm mb-4 flex items-center justify-center border border-slate-200 ring-4 ring-slate-100/50 group-hover:scale-105 transition-transform">
                                        <svg class="w-8 h-8 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" /></svg>
                                    </div>
                                    <span class="block text-sm font-extrabold text-slate-800 dark:text-slate-100 text-center">Terang</span>
                                </div>
                                <div class="absolute top-4 right-4 opacity-0 peer-checked:opacity-100 transition-all scale-50 peer-checked:scale-100">
                                    <div class="w-6 h-6 bg-primary-600 rounded-full flex items-center justify-center text-white shadow-md">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                    </div>
                                </div>
                            </label>

                            {{-- Dark Theme --}}
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="theme_select" value="dark" class="sr-only peer" @change="$dispatch('theme-update', 'dark')" :checked="theme === 'dark'">
                                <div class="p-5 rounded-3xl border-2 border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 peer-checked:border-primary-500 peer-checked:bg-white dark:peer-checked:bg-slate-800 transition-all hover:border-slate-300 dark:hover:border-slate-700 shadow-sm peer-checked:shadow-primary-500/10">
                                    <div class="w-full aspect-video rounded-xl bg-slate-900 shadow-sm mb-4 flex items-center justify-center border border-slate-800 ring-4 ring-slate-800/50 group-hover:scale-105 transition-transform">
                                        <svg class="w-8 h-8 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998z" /></svg>
                                    </div>
                                    <span class="block text-sm font-extrabold text-slate-800 dark:text-slate-100 text-center">Gelap</span>
                                </div>
                                <div class="absolute top-4 right-4 opacity-0 peer-checked:opacity-100 transition-all scale-50 peer-checked:scale-100">
                                    <div class="w-6 h-6 bg-primary-600 rounded-full flex items-center justify-center text-white shadow-md">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                    </div>
                                </div>
                            </label>

                            {{-- System Theme --}}
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="theme_select" value="system" class="sr-only peer" @change="$dispatch('theme-update', 'system')" :checked="theme === 'system'">
                                <div class="p-5 rounded-3xl border-2 border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 peer-checked:border-primary-500 peer-checked:bg-white dark:peer-checked:bg-slate-800 transition-all hover:border-slate-300 dark:hover:border-slate-700 shadow-sm peer-checked:shadow-primary-500/10">
                                    <div class="w-full aspect-video rounded-xl bg-linear-to-br from-slate-200 to-slate-800 shadow-sm mb-4 flex items-center justify-center overflow-hidden border border-slate-300 dark:border-slate-700 ring-4 ring-slate-200/50 dark:ring-slate-700/50 group-hover:scale-105 transition-transform">
                                        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25"/></svg>
                                    </div>
                                    <span class="block text-sm font-extrabold text-slate-800 dark:text-slate-100 text-center">Sistem</span>
                                </div>
                                <div class="absolute top-4 right-4 opacity-0 peer-checked:opacity-100 transition-all scale-50 peer-checked:scale-100">
                                    <div class="w-6 h-6 bg-primary-600 rounded-full flex items-center justify-center text-white shadow-md">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Notifications Section --}}
                <div id="notifications-section" x-show="activeTab === '#notifications'" x-cloak class="block w-full bg-white dark:bg-slate-900/60 rounded-4xl shadow-sm ring-1 ring-slate-200/50 dark:ring-slate-800/80 backdrop-blur-xl">
                    <form action="{{ route('settings.notifications') }}" method="POST" class="p-6 sm:p-10 space-y-8">
                        @csrf
                        @method('PUT')
                        
                        <div class="border-b border-slate-100 dark:border-slate-800 pb-6">
                            <h2 class="text-xl font-extrabold text-slate-900 dark:text-white">Preferensi Notifikasi</h2>
                            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1 font-medium">Atur informasi apa saja yang ingin Anda terima.</p>
                        </div>
                        
                        <div class="space-y-6">
                            @php
                                $settings = $user->notification_settings ?? ['order_updates' => true, 'promos' => true, 'newsletter' => false];
                            @endphp
                            
                            <div class="flex items-center justify-between py-2 group">
                                <div class="pr-6">
                                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">Pembaruan Pesanan</h3>
                                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 font-medium">Dapatkan notifikasi real-time tentang status pengiriman paket.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                    <input type="checkbox" name="order_updates" value="1" {{ ($settings['order_updates'] ?? false) ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-14 h-7 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-primary-600 shadow-inner"></div>
                                </label>
                            </div>

                            <hr class="border-slate-100 dark:border-slate-800">

                            <div class="flex items-center justify-between py-2 group">
                                <div class="pr-6">
                                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">Promo & Penawaran</h3>
                                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 font-medium">Jangan lewatkan diskon menarik dan penawaran spesial artisan.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                    <input type="checkbox" name="promos" value="1" {{ ($settings['promos'] ?? false) ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-14 h-7 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-primary-600 shadow-inner"></div>
                                </label>
                            </div>

                            <hr class="border-slate-100 dark:border-slate-800">

                            <div class="flex items-center justify-between py-2 group">
                                <div class="pr-6">
                                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">Buletin (Newsletter)</h3>
                                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 font-medium">Info jajanan terbaru dan tips kuliner mingguan eksklusif.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                    <input type="checkbox" name="newsletter" value="1" {{ ($settings['newsletter'] ?? false) ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-14 h-7 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-primary-600 shadow-inner"></div>
                                </label>
                            </div>
                        </div>

                        <div class="pt-6">
                            <button type="submit" class="relative overflow-hidden group w-full sm:w-auto px-8 py-4 bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-sm font-bold rounded-xl shadow-md active:scale-95 transition-all">
                                <span class="relative z-10 flex items-center justify-center gap-2">
                                    Simpan Preferensi
                                </span>
                                <div class="absolute inset-0 h-full w-full bg-linear-to-r from-transparent via-white/20 dark:via-slate-900/10 to-transparent -translate-x-full group-hover:animate-[shimmer_1.5s_infinite]"></div>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Security Section --}}
                <div id="security-section" x-show="activeTab === '#security'" x-cloak class="block w-full bg-white dark:bg-slate-900/60 rounded-4xl shadow-sm ring-1 ring-slate-200/50 dark:ring-slate-800/80 backdrop-blur-xl">
                    <form action="{{ route('settings.password') }}" method="POST" class="p-6 sm:p-10 space-y-8">
                        @csrf
                        @method('PUT')
                        
                        <div class="border-b border-slate-100 dark:border-slate-800 pb-6">
                            <h2 class="text-xl font-extrabold text-slate-900 dark:text-white">Keamanan Akun</h2>
                            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1 font-medium">Jaga akun Anda tetap aman dengan kata sandi yang kuat.</p>
                        </div>
                        
                        <div class="space-y-6 max-w-lg">
                            @if($user->google_id)
                                <div class="p-5 rounded-3xl border-2 border-slate-150 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/40 flex items-center justify-between shadow-xs">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 flex items-center justify-center shadow-xs">
                                            <svg class="w-6 h-6 shrink-0" viewBox="0 0 24 24">
                                                <path fill="#ea4335" d="M5.2662 9.76451C6.1982 6.95542 8.8542 4.90909 12 4.90909C13.6909 4.90909 15.2182 5.50909 16.4182 6.49091L19.9091 3C17.7818 1.14545 15.0545 0 12 0C7.27273 0 3.19091 2.69091 1.24545 6.65455L5.2662 9.76451Z" />
                                                <path fill="#34a853" d="M16.0409 18.0136C14.8705 18.7159 13.4841 19.0909 12 19.0909C8.8542 19.0909 6.1982 17.0455 5.2662 14.2364L1.24545 17.3455C3.19091 21.3091 7.27273 24 12 24C15.0545 24 17.7818 23.0182 19.9318 21.3273L16.0409 18.0136Z" />
                                                <path fill="#4285f4" d="M23.64 12.2182C23.64 11.3364 23.5545 10.5182 23.3909 9.70909H12V14.2473H18.5182C18.2364 15.7455 17.3909 17.0273 16.1261 17.8909L20.0171 21.2045C22.28 19.1182 23.64 16.0091 23.64 12.2182Z" />
                                                <path fill="#fbbc05" d="M5.2662 14.2364C5.0182 13.5273 4.8818 12.7727 4.8818 12C4.8818 11.2273 5.0182 10.4727 5.2662 9.76364L1.24545 6.65455C0.445455 8.27273 0 10.0818 0 12C0 13.9182 0.445455 15.7273 1.24545 17.3455L5.2662 14.2364Z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-extrabold text-slate-800 dark:text-white">Google</h4>
                                            <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 mt-0.5">Akun masuk terhubung</p>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-extrabold bg-green-500/10 text-green-600 dark:bg-green-500/15 dark:text-green-400 border border-green-500/20 select-none">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                        Tersambung
                                    </span>
                                </div>
                            @endif
                            <div x-data="{ show: false }">
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Kata Sandi Saat Ini</label>
                                <div class="relative">
                                    <input :type="show ? 'text' : 'password'" name="current_password" 
                                           class="w-full px-5 py-4 rounded-xl border-2 border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-white focus:bg-white dark:focus:bg-slate-900 focus:ring-0 focus:border-primary-500 text-sm font-bold transition-all shadow-inner shadow-slate-100/50 dark:shadow-none pr-12">
                                    <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">
                                        <svg x-show="!show" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                        <svg x-show="show" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                                    </button>
                                </div>
                                @error('current_password') <p class="mt-2 text-xs font-bold text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div x-data="{ show: false }">
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Kata Sandi Baru</label>
                                <div class="relative">
                                    <input :type="show ? 'text' : 'password'" name="password" 
                                           class="w-full px-5 py-4 rounded-xl border-2 border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-white focus:bg-white dark:focus:bg-slate-900 focus:ring-0 focus:border-primary-500 text-sm font-bold transition-all shadow-inner shadow-slate-100/50 dark:shadow-none pr-12">
                                    <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">
                                        <svg x-show="!show" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                        <svg x-show="show" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                                    </button>
                                </div>
                                @error('password') <p class="mt-2 text-xs font-bold text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div x-data="{ show: false }">
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Konfirmasi Kata Sandi Baru</label>
                                <div class="relative">
                                    <input :type="show ? 'text' : 'password'" name="password_confirmation" 
                                           class="w-full px-5 py-4 rounded-xl border-2 border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-white focus:bg-white dark:focus:bg-slate-900 focus:ring-0 focus:border-primary-500 text-sm font-bold transition-all shadow-inner shadow-slate-100/50 dark:shadow-none pr-12">
                                    <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">
                                        <svg x-show="!show" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                        <svg x-show="show" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-transparent">
                            <button type="submit" class="relative overflow-hidden group w-full sm:w-auto px-8 py-4 bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-sm font-bold rounded-xl shadow-md active:scale-95 transition-all">
                                <span class="relative z-10 flex items-center justify-center gap-2">
                                    Perbarui Keamanan
                                </span>
                                <div class="absolute inset-0 h-full w-full bg-linear-to-r from-transparent via-white/20 dark:via-slate-900/10 to-transparent -translate-x-full group-hover:animate-[shimmer_1.5s_infinite]"></div>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Addresses Section --}}
                <div id="addresses-section" x-show="activeTab === '#addresses'" x-cloak class="block w-full bg-white dark:bg-slate-900/60 rounded-4xl shadow-sm ring-1 ring-slate-200/50 dark:ring-slate-800/80 backdrop-blur-xl">
                    <div class="p-6 sm:p-10 border-b border-slate-100 dark:border-slate-800">
                        <h2 class="text-xl font-extrabold text-slate-900 dark:text-white">Alamat Pengiriman</h2>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mt-1 font-medium">Kelola titik lokasi untuk pengantaran pesanan Anda.</p>
                    </div>
                    <div class="p-6 sm:p-10">
                        @livewire('manage-addresses')
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    [x-cloak] { display: none !important; }
    /* Override Livewire Address Component to match the radius if needed */
    #addresses-section .card { border-radius: 1.5rem !important; }
    #addresses-section input, #addresses-section select { border-radius: 1rem !important; font-weight: 700; border-width: 2px !important; }
    #addresses-section button, #addresses-section a.btn { border-radius: 1rem !important; font-weight: 700; }
</style>
@endpush
