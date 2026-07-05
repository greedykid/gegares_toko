@extends('layouts.app')
@section('title', 'Kontak')
@section('content')

@php
    $store = new \Illuminate\Support\Fluent(\Illuminate\Support\Facades\Cache::remember('store_settings', 86400, fn() => (\App\Models\StoreSetting::first() ?? new \App\Models\StoreSetting())->toArray()));
    $email = $store->contact_email ?? 'hello@gecares.com';
    $phone = $store->contact_phone ?? '+62 812-3456-7890';
    
    // Prioritize contact_whatsapp from database settings
    $rawWhatsapp = $store->contact_whatsapp ?? $phone;
    $waPhone = preg_replace('/[^0-9]/', '', $rawWhatsapp);
    if (str_starts_with($waPhone, '0')) {
        $waPhone = '62' . substr($waPhone, 1);
    }
    
    $addressHTML = 'Jl. Jajanan Pasar No. 12<br>Jakarta Selatan, Indonesia 12345';
    if ($store && $store->address_line) {
        $addressHTML = e($store->address_line) . '<br>' . e($store->city) . ', ' . e($store->province) . ' ' . e($store->postal_code);
    }
    
    $hoursHTML = "Setiap Hari: 06:00 - 17:00 WIB<br><span class=\"text-xs text-primary-500 dark:text-primary-400\">Pemesanan WhatsApp: 24 Jam</span>";
    if ($store && $store->contact_hours) {
        $hoursHTML = nl2br(e($store->contact_hours));
        $hoursHTML = str_replace(
            ['Pemesanan WhatsApp: 24 Jam', 'Pemesanan WhatsApp: 24 jam'],
            ['<span class="text-xs text-primary-500 dark:text-primary-400">Pemesanan WhatsApp: 24 Jam</span>', '<span class="text-xs text-primary-500 dark:text-primary-400">Pemesanan WhatsApp: 24 Jam</span>'],
            $hoursHTML
        );
    }
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20 space-y-16">
    
    {{-- Hero Section --}}
    <div class="text-center max-w-3xl mx-auto space-y-4">
        <span class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-primary-50 dark:bg-primary-950/40 text-[11px] font-black uppercase tracking-[0.2em] text-primary-600 dark:text-primary-400 border border-primary-100/40 dark:border-primary-900/30">
            Hubungi Kami
        </span>
        <h1 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-slate-100 tracking-tight uppercase">
            Ada <span class="text-primary-600 dark:text-primary-400">Pertanyaan?</span>
        </h1>
        <p class="text-base sm:text-lg text-slate-500 dark:text-slate-400 font-semibold leading-relaxed">
            Kami siap membantu Anda kapan saja. Silakan kirim pesan atau hubungi kontak kami.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
        {{-- ─── LEFT: WHATSAPP DIRECT FORM (7 cols) ─── --}}
        <div class="lg:col-span-7 bg-slate-50 dark:bg-slate-900/40 rounded-3xl border-2 border-slate-100 dark:border-slate-800/80 p-8 lg:p-12 transition-all duration-300"
             x-data="{ 
                name: '', 
                subject: 'Tanya Produk', 
                message: '',
                sendToWhatsapp() {
                    const phone = '{{ $waPhone }}';
                    const text = `Halo *Gegares*, saya *${this.name}*.\n\nSubjek: *${this.subject}*\n\nPesan:\n${this.message}`;
                    const url = `https://wa.me/${phone}?text=${encodeURIComponent(text)}`;
                    window.open(url, '_blank');
                }
             }">
            <div class="space-y-4 mb-8">
                <h2 class="text-2xl font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight">Kirim Pesan Langsung</h2>
                <div class="h-1.5 w-16 bg-primary-500 rounded-full"></div>
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400 leading-relaxed">
                    Tuliskan pesan Anda di bawah ini dan kami akan membalasnya langsung melalui chat WhatsApp resmi toko.
                </p>
            </div>
            
            <form @submit.prevent="sendToWhatsapp" class="space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-3 ml-1">Nama Lengkap</label>
                        <input type="text" x-model="name" required
                               class="w-full px-5 py-4 rounded-2xl border-2 border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all font-bold text-sm"
                               placeholder="Masukkan nama Anda">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-3 ml-1">Subjek Pembicaraan</label>
                        <div x-data="{ 
                                open: false,
                                get selectedLabel() {
                                    if (subject === 'Tanya Produk') return 'Pertanyaan Produk';
                                    if (subject === 'Pesanan Event') return 'Pesan untuk Acara / Katering';
                                    if (subject === 'Kendala Pesanan') return 'Kendala Pesanan';
                                    if (subject === 'Kerjasama Bisnis') return 'Kerjasama Bisnis';
                                    if (subject === 'Lainnya') return 'Lainnya...';
                                    return 'Pilih Subjek';
                                }
                             }" 
                             class="relative group">
                            <input type="hidden" x-model="subject" name="subject" required>
                            <button @click="open = !open" type="button" 
                                    class="w-full flex items-center justify-between px-5 py-4 rounded-2xl border-2 border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all font-bold text-sm cursor-pointer">
                                <span x-text="selectedLabel"></span>
                                <svg class="h-4 w-4 text-slate-400 dark:text-slate-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>
                            <div x-show="open" 
                                 @click.outside="open = false" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute z-55 w-full mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl py-1 overflow-hidden"
                                 style="display: none;">
                                <button type="button" @click="subject = 'Tanya Produk'; open = false"
                                        class="w-full text-left px-5 py-3 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                        :class="subject === 'Tanya Produk' ? 'bg-slate-50 dark:bg-slate-800/50 font-bold text-primary-600 dark:text-primary-400' : ''">Pertanyaan Produk</button>
                                <button type="button" @click="subject = 'Pesanan Event'; open = false"
                                        class="w-full text-left px-5 py-3 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                        :class="subject === 'Pesanan Event' ? 'bg-slate-50 dark:bg-slate-800/50 font-bold text-primary-600 dark:text-primary-400' : ''">Pesan untuk Acara / Katering</button>
                                <button type="button" @click="subject = 'Kendala Pesanan'; open = false"
                                        class="w-full text-left px-5 py-3 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                        :class="subject === 'Kendala Pesanan' ? 'bg-slate-50 dark:bg-slate-800/50 font-bold text-primary-600 dark:text-primary-400' : ''">Kendala Pesanan</button>
                                <button type="button" @click="subject = 'Kerjasama Bisnis'; open = false"
                                        class="w-full text-left px-5 py-3 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                        :class="subject === 'Kerjasama Bisnis' ? 'bg-slate-50 dark:bg-slate-800/50 font-bold text-primary-600 dark:text-primary-400' : ''">Kerjasama Bisnis</button>
                                <button type="button" @click="subject = 'Lainnya'; open = false"
                                        class="w-full text-left px-5 py-3 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                        :class="subject === 'Lainnya' ? 'bg-slate-50 dark:bg-slate-800/50 font-bold text-primary-600 dark:text-primary-400' : ''">Lainnya...</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-3 ml-1">Pesan Anda</label>
                    <textarea x-model="message" required rows="6"
                              class="w-full px-5 py-4 rounded-2xl border-2 border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all resize-none font-bold text-sm leading-relaxed"
                              placeholder="Tuliskan detail pertanyaan, keluhan, atau pesanan Anda di sini secara rinci..."></textarea>
                </div>
                <div class="pt-2">
                    <button type="submit" class="w-full py-4 bg-[#25D366] hover:bg-[#1EBE5D] text-white font-black rounded-2xl active:scale-[0.98] transition-all duration-300 flex items-center justify-center gap-3">
                        <svg class="w-6 h-6 flex-shrink-0" fill="currentColor" viewBox="0 0 448 512"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7 .9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"></path></svg>
                        <span>Mulai Obrolan WhatsApp</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- ─── RIGHT: CONTACT INFO CARDS (5 cols) ─── --}}
        <div class="lg:col-span-5 space-y-6">
            {{-- Email Card --}}
            <div class="group p-8 bg-white dark:bg-slate-900/20 border-2 border-slate-100 dark:border-slate-800/80 rounded-3xl transition-all duration-300 hover:border-primary-500 hover:-translate-y-1 flex items-start gap-6">
                <div class="w-14 h-14 shrink-0 bg-primary-50 dark:bg-primary-950/40 rounded-2xl flex items-center justify-center border border-primary-100/30 dark:border-primary-900/20 text-primary-600 dark:text-primary-400 group-hover:bg-primary-600 group-hover:text-white transition-colors duration-300">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                </div>
                <div class="space-y-1">
                    <h3 class="text-sm font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Email Resmi</h3>
                    <p class="text-base font-bold text-slate-800 dark:text-slate-100">{{ $email }}</p>
                </div>
            </div>
            
            {{-- Phone Card --}}
            <div class="group p-8 bg-white dark:bg-slate-900/20 border-2 border-slate-100 dark:border-slate-800/80 rounded-3xl transition-all duration-300 hover:border-primary-500 hover:-translate-y-1 flex items-start gap-6">
                <div class="w-14 h-14 shrink-0 bg-accent-50 dark:bg-accent-950/40 rounded-2xl flex items-center justify-center border border-accent-100/30 dark:border-accent-900/20 text-accent-600 dark:text-accent-400 group-hover:bg-accent-600 group-hover:text-white transition-colors duration-300">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                </div>
                <div class="space-y-1">
                    <h3 class="text-sm font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Nomor Telepon</h3>
                    <p class="text-base font-bold text-slate-800 dark:text-slate-100">{{ $phone }}</p>
                </div>
            </div>

            {{-- Address Card --}}
            <div class="group p-8 bg-white dark:bg-slate-900/20 border-2 border-slate-100 dark:border-slate-800/80 rounded-3xl transition-all duration-300 hover:border-primary-500 hover:-translate-y-1 flex items-start gap-6">
                <div class="w-14 h-14 shrink-0 bg-emerald-50 dark:bg-emerald-950/40 rounded-2xl flex items-center justify-center border border-emerald-100/30 dark:border-emerald-900/20 text-emerald-600 dark:text-emerald-400 group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-300">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                </div>
                <div class="space-y-1">
                    <h3 class="text-sm font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Alamat Toko</h3>
                    <p class="text-sm font-bold text-slate-800 dark:text-slate-100 leading-relaxed">{!! $addressHTML !!}</p>
                </div>
            </div>

            {{-- Hours Card --}}
            <div class="group p-8 bg-white dark:bg-slate-900/20 border-2 border-slate-100 dark:border-slate-800/80 rounded-3xl transition-all duration-300 hover:border-primary-500 hover:-translate-y-1 flex items-start gap-6">
                <div class="w-14 h-14 shrink-0 bg-amber-50 dark:bg-amber-950/40 rounded-2xl flex items-center justify-center border border-amber-100/30 dark:border-amber-900/20 text-amber-600 dark:text-amber-400 group-hover:bg-amber-600 group-hover:text-white transition-colors duration-300">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </div>
                <div class="space-y-1">
                    <h3 class="text-sm font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Jam Operasional</h3>
                    <p class="text-sm font-bold text-slate-800 dark:text-slate-100 leading-relaxed">
                        {!! $hoursHTML !!}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
