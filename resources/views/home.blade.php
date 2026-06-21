@extends('layouts.app')
@section('title', 'Beranda')
@section('content')
@php
    $settings = \App\Models\StoreSetting::first() ?? new \App\Models\StoreSetting();
    
    $heroBadge = $settings->hero_badge ?? 'Jajanan Pasar Tradisional';
    $heroTitle = $settings->hero_title ?? 'Rasa <span class="text-primary-600 dark:text-primary-400">Autentik</span>,<br>Langsung ke <span class="text-accent-600 dark:text-accent-400">Rumah</span>';
    $heroSubtitle = $settings->hero_subtitle ?? 'Nikmati kelezatan jajanan pasar pilihan yang dibuat segar setiap hari selagi hangat. Dari klepon manis hingga risoles yang gurih renyah.';
    
    $ctaTitle = $settings->cta_title ?? 'Pesan Sekarang, Nikmati Hari Ini';
    $ctaSubtitle = $settings->cta_subtitle ?? 'Dibuat fresh, dikirim cepat. Nikmati jajanan pasar favorit Anda tanpa keluar rumah.';
    
    $whatsappPhone = preg_replace('/[^0-9]/', '', $settings->contact_whatsapp ?? '6281234567890');
    if (str_starts_with($whatsappPhone, '0')) {
        $whatsappPhone = '62' . substr($whatsappPhone, 1);
    }
    
    $faqItems = $settings->faq_items ?? [
        ['q' => 'Berapa lama waktu pengiriman?', 'a' => 'Kami menggunakan layanan Instan dan Sameday dari Biteship (Gojek/Grab) untuk memastikan jajanan pasar tetap segar saat sampai di tangan Anda. Estimasi sampai adalah 1-4 jam setelah kurir menjemput paket.'],
        ['q' => 'Apakah produk dibuat setiap hari?', 'a' => 'Tentu saja! Seluruh produk Gegares dibuat segar (freshly baked/made) setiap pagi hari sebelum pengiriman dimulai untuk menjamin kualitas dan rasa autentik.'],
        ['q' => 'Bagaimana cara melacak pesanan saya?', 'a' => 'Setelah pesanan Anda diproses oleh admin, Anda akan menerima nomor resi pelacakan. Anda dapat memantau posisi kurir secara real-time langsung melalui halaman "Pesanan Saya" di akun Anda.'],
        ['q' => 'Apakah bisa memesan untuk acara besar (katering)?', 'a' => 'Bisa! Kami melayani pemesanan untuk acara kantor, arisan, atau pesta. Untuk jumlah besar, kami menyarankan pemesanan minimal H-2 melalui WhatsApp agar kami dapat menyiapkan bahan baku terbaik.'],
        ['q' => 'Metode pembayaran apa saja yang tersedia?', 'a' => 'Kami mendukung berbagai metode pembayaran instan melalui Pakasir, termasuk QRIS, E-Wallet (GoPay, OVO, dll), dan Transfer Bank (Virtual Account).']
    ];
@endphp

{{-- ─── HERO SECTION ─── --}}
<section class="relative overflow-hidden bg-linear-to-br from-primary-50 via-white to-accent-50 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 transition-colors duration-500"
         x-data="{ 
            activeSlide: 0,
            slidesCount: {{ $featuredProducts->count() }},
            next() { this.activeSlide = (this.activeSlide + 1) % this.slidesCount }
         }"
         x-init="setInterval(() => next(), 5000)">
    
    {{-- Decorative Background --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-primary-200/20 dark:bg-primary-900/10 rounded-full blur-3xl transition-colors"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-accent-200/20 dark:bg-accent-900/10 rounded-full blur-3xl transition-colors"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-8 items-center">
            
            {{-- Left: Text Content --}}
            <div class="max-w-xl z-10">
                <p class="text-xs sm:text-sm font-black text-primary-600 dark:text-primary-400 tracking-[0.2em] uppercase mb-4">{{ $heroBadge }}</p>
                <h1 class="text-4xl sm:text-5xl lg:text-7xl font-extrabold text-slate-900 dark:text-slate-100 leading-[1.1] tracking-tight transition-colors">
                    {!! $heroTitle !!}
                </h1>
                <p class="mt-8 text-base sm:text-lg text-slate-500 dark:text-slate-400 leading-relaxed max-w-lg transition-colors">
                    {{ $heroSubtitle }}
                </p>
                <div class="mt-10 flex flex-wrap gap-4">
                    <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-primary-700 text-white font-bold rounded-2xl hover:bg-primary-800 hover:shadow-2xl hover:shadow-primary-200 dark:hover:shadow-primary-900/20 hover:-translate-y-1 transition-all duration-300 active:scale-95">
                        Jelajahi Menu
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </a>
                    <a href="{{ route('about') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-bold rounded-2xl border border-slate-200 dark:border-slate-700 hover:border-primary-300 dark:hover:border-primary-500 hover:text-primary-700 dark:hover:text-primary-400 transition-all duration-300">
                        Tentang Kami
                    </a>
                </div>
            </div>

            {{-- Right: Slideshow --}}
            <div class="relative order-first lg:order-last">
                <div class="relative aspect-square sm:aspect-video lg:aspect-square max-w-lg mx-auto">
                    {{-- Decorative Ring --}}
                    <div class="absolute inset-0 rounded-full border-2 border-dashed border-primary-100 dark:border-primary-900/30 animate-spin-slow"></div>
                    
                    {{-- Carousel Frame --}}
                    <div class="absolute inset-4 rounded-[2.5rem] overflow-hidden shadow-2xl shadow-primary-200/50 dark:shadow-black/50 border-4 border-white dark:border-slate-800">
                        {{-- Slides --}}
                        @foreach($featuredProducts as $index => $product)
                            @php
                                $imagePath = $product->image ? asset('storage/' . $product->image) : null;
                                $productRoute = route('products.show', $product->slug);
                            @endphp
                            <div x-show="activeSlide === {{ $index }}"
                                 x-transition:enter="transition transform ease-out duration-1000"
                                 x-transition:enter-start="-translate-x-full opacity-0"
                                 x-transition:enter-end="translate-x-0 opacity-100"
                                 x-transition:leave="transition transform ease-in duration-1000"
                                 x-transition:leave-start="translate-x-0 opacity-100"
                                 x-transition:leave-end="translate-x-full opacity-0"
                                 class="absolute inset-0 w-full h-full"
                                 @if($index > 0) x-cloak style="display: none;" @endif>
                                
                                <a href="{{ $productRoute }}" class="group block w-full h-full relative">
                                    @if($imagePath)
                                        <img src="{{ $imagePath }}" alt="{{ $product->name }}" 
                                             class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                                             width="480" height="480">
                                    @else
                                        {{-- Image Fallback --}}
                                        <div class="w-full h-full flex flex-col items-center justify-center bg-primary-50 dark:bg-slate-900 transition-colors duration-300">
                                            <svg class="w-12 h-12 text-primary-400/80 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 18.75V5.25A2.25 2.25 0 0 1 5.25 3h13.5A2.25 2.25 0 0 1 21 5.25v13.5A2.25 2.25 0 0 1 18.75 21H5.25A2.25 2.25 0 0 1 3 18.75Z" />
                                            </svg>
                                            <span class="mt-2 text-xs text-primary-700 dark:text-slate-400 font-bold">{{ $product->name }}</span>
                                        </div>
                                    @endif
                                    
                                    {{-- Slide Caption --}}
                                    <div class="absolute bottom-6 left-6 right-6 p-4 bg-white/10 backdrop-blur-md rounded-2xl border border-white/20 opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-4 group-hover:translate-y-0">
                                        <div class="flex items-center justify-between gap-4">
                                            <span class="text-sm font-bold text-white tracking-tight">{{ $product->name }}</span>
                                            <span class="text-[10px] font-black uppercase text-white/80 tracking-widest bg-primary-600 px-2 py-1 rounded">Lihat</span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>

                    {{-- Navigation Dots --}}
                    <div class="absolute -bottom-6 left-1/2 -translate-x-1/2 flex gap-1 z-20">
                        @foreach($featuredProducts as $index => $product)
                            <button @click="activeSlide = {{ $index }}" 
                                    class="w-6 h-6 flex items-center justify-center rounded-full focus:outline-none"
                                    aria-label="Pilih slide {{ $index + 1 }}">
                                <span class="h-1.5 rounded-full transition-all duration-300"
                                      :class="activeSlide === {{ $index }} ? 'w-8 bg-primary-600' : 'w-2 bg-slate-300 dark:bg-slate-700 hover:bg-primary-300'"></span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ─── CATEGORY SECTION ─── --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
    <div class="text-center mb-12 reveal reveal-up">
        <p class="text-sm font-semibold text-primary-600 dark:text-primary-400 tracking-wide uppercase">Kategori</p>
        <h2 class="mt-2 text-3xl font-bold text-slate-900 dark:text-slate-100 transition-colors">Pilih Jajanan Favorit</h2>
    </div>
    <div class="flex overflow-x-auto overflow-y-hidden scrollbar-none pt-2 pb-4 gap-4 px-4 -mx-4 md:overflow-visible md:grid md:grid-cols-6 md:gap-4 lg:gap-8 md:px-0 md:mx-0">
        @foreach($categories as $index => $category)
            <a href="{{ route('products.index', ['category' => $category->slug]) }}"
               class="group relative flex flex-col items-center p-1 sm:p-2 md:p-4 hover:-translate-y-1 transition-all duration-300 reveal reveal-up delay-{{ ($index % 6 + 1) * 100 }} w-28 sm:w-32 md:w-auto shrink-0">
                <div class="w-20 h-20 md:w-24 md:h-24 rounded-full overflow-hidden mb-2 sm:mb-4 shadow-sm group-hover:shadow-lg group-hover:scale-105 transition-all duration-300 shrink-0 border border-slate-100 dark:border-slate-800">
                    @if($category->image)
                        <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" width="80" height="80" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                            <svg class="w-10 h-10 md:w-12 md:h-12 text-slate-400 dark:text-slate-550" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 18.75V5.25A2.25 2.25 0 0 1 5.25 3h13.5A2.25 2.25 0 0 1 21 5.25v13.5A2.25 2.25 0 0 1 18.75 21H5.25A2.25 2.25 0 0 1 3 18.75Z" />
                            </svg>
                        </div>
                    @endif
                </div>
                <h3 class="text-[10px] sm:text-xs md:text-sm font-bold text-slate-800 dark:text-slate-200 text-center transition-colors group-hover:text-primary-600 dark:group-hover:text-primary-400 leading-tight">{{ $category->name }}</h3>
                <span class="mt-0.5 text-[8px] sm:text-[10px] md:text-xs text-slate-400 dark:text-slate-500 font-medium">{{ $category->products_count }} produk</span>
            </a>
        @endforeach
    </div>
</section>

{{-- ─── FEATURED PRODUCTS ─── --}}
<section class="bg-white dark:bg-slate-950 border-y border-slate-100 dark:border-slate-900 transition-colors duration-300 overflow-x-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
        <div class="flex items-end justify-between mb-12 reveal reveal-right">
            <div>
                <p class="text-sm font-semibold text-accent-600 dark:text-accent-400 tracking-wide uppercase">Produk Unggulan</p>
                <h2 class="mt-2 text-3xl font-bold text-slate-900 dark:text-slate-100 transition-colors">Yang Paling Disukai</h2>
            </div>
            <a href="{{ route('products.index') }}" class="hidden sm:inline-flex items-center gap-1.5 text-sm font-semibold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors">
                Lihat Semua
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
            </a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 lg:gap-6">
            @foreach($featuredProducts as $index => $product)
                <div class="reveal reveal-up delay-{{ ($index % 4 + 1) * 100 }}">
                    @include('components.product-card', ['product' => $product])
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ─── FAQ SECTION ─── --}}
<section id="faq" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24 overflow-x-hidden">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
        <div class="lg:col-span-1 reveal reveal-right">
            <p class="text-sm font-semibold text-primary-600 dark:text-primary-400 tracking-wide uppercase">Bantuan</p>
            <h2 class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-slate-100 leading-tight transition-colors">Pertanyaan yang Sering Diajukan</h2>
            <p class="mt-4 text-slate-500 dark:text-slate-400 text-sm leading-relaxed text-balance transition-colors">Punya pertanyaan lain? Jangan ragu untuk menghubungi tim dukungan kami melalui WhatsApp.</p>
            <a href="https://wa.me/{{ $whatsappPhone }}" target="_blank" class="mt-6 inline-flex items-center gap-2 text-sm font-bold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors">
                Tanya via WhatsApp
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
            </a>
        </div>
        
        <div class="lg:col-span-2 space-y-4 reveal reveal-left" x-data="{ activeFaq: null }">
            @php
                $faqs = $faqItems;
            @endphp

            @foreach($faqs as $index => $faq)
                <div class="border border-slate-100 dark:border-slate-800 rounded-2xl bg-white dark:bg-slate-900 overflow-hidden transition-all duration-300"
                     :class="{ 'border-primary-200 dark:border-primary-800 shadow-lg shadow-primary-50 dark:shadow-primary-900/10': activeFaq === {{ $index }} }">
                    <button @click="activeFaq = (activeFaq === {{ $index }} ? null : {{ $index }})" 
                            class="w-full px-6 py-5 flex items-center justify-between text-left transition-colors">
                        <span class="text-sm font-bold text-slate-900 dark:text-slate-100 leading-tight transition-colors" 
                              :class="{ 'text-primary-600 dark:text-primary-400': activeFaq === {{ $index }} }">{{ $faq['q'] }}</span>
                        <div class="ml-4 shrink-0">
                            <svg class="w-5 h-5 text-slate-400 transform transition-transform duration-300" 
                                 :class="{ 'rotate-180 text-primary-600 dark:text-primary-400': activeFaq === {{ $index }} }"
                                 fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </div>
                    </button>
                    <div x-show="activeFaq === {{ $index }}" 
                         x-collapse
                         x-cloak>
                        <div class="px-6 pb-6 text-sm text-slate-500 dark:text-slate-400 leading-relaxed border-t border-slate-50 dark:border-slate-800 pt-4 transition-colors">
                            {{ $faq['a'] }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ─── CTA SECTION ─── --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20 reveal reveal-up">
    <div class="relative overflow-hidden bg-linear-to-r from-primary-600 to-primary-700 rounded-3xl p-8 lg:p-14">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/3 blur-2xl"></div>
        <div class="relative max-w-lg">
            <h2 class="text-2xl lg:text-3xl font-bold text-white">{{ $ctaTitle }}</h2>
            <p class="mt-3 text-primary-100 text-sm lg:text-base leading-relaxed">{{ $ctaSubtitle }}</p>
            <a href="{{ route('products.index') }}" class="mt-6 inline-flex items-center gap-2 px-6 py-3 bg-white text-primary-700 font-semibold rounded-xl hover:bg-primary-50 transition-all duration-200">
                Mulai Belanja
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
            </a>
        </div>
    </div>
</section>

@endsection
