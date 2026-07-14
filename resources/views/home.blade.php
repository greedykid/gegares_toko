@extends('layouts.app')
@section('title', 'Beranda')
@section('content')
@php
    $settings = new \Illuminate\Support\Fluent(\Illuminate\Support\Facades\Cache::remember('store_settings', 86400, fn() => (\App\Models\StoreSetting::first() ?? new \App\Models\StoreSetting())->toArray()));
    
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
@php
    // Build a padded slide list for an infinite track: [cloneOfLast, ...real, cloneOfFirst].
    // The clones let a wrap (last→first) keep sliding in the same direction instead of
    // rewinding across every slide, and are snapped back invisibly on transitionend.
    $heroSlides = collect();
    $multi = $featuredProducts->count() > 1;
    if ($multi) {
        $heroSlides->push(['product' => $featuredProducts->last(), 'eager' => false]);
    }
    foreach ($featuredProducts as $i => $p) {
        $heroSlides->push(['product' => $p, 'eager' => $i === 0]);
    }
    if ($multi) {
        $heroSlides->push(['product' => $featuredProducts->first(), 'eager' => false]);
    }
@endphp
<section class="relative bg-white dark:bg-slate-950 transition-colors duration-500"
         x-data="heroCarousel({{ $featuredProducts->count() }})">

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-16 sm:pt-8 lg:pt-10 lg:pb-24">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-12 items-center">

            {{-- Left: Text Content --}}
            <div class="max-w-xl">
                <p class="text-xs sm:text-sm font-semibold text-primary-600 dark:text-primary-400 tracking-[0.15em] uppercase mb-5">{{ $heroBadge }}</p>

                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 dark:text-slate-100 leading-[1.15] tracking-tight transition-colors">
                    {!! $heroTitle !!}
                </h1>
                <p class="mt-6 text-base sm:text-lg text-slate-500 dark:text-slate-400 leading-relaxed max-w-lg transition-colors">
                    {{ $heroSubtitle }}
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 px-7 py-3.5 bg-primary-700 text-white font-semibold rounded-xl hover:bg-primary-800 transition-colors duration-200">
                        Jelajahi Menu
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </a>
                    <a href="{{ route('about') }}" class="inline-flex items-center gap-2 px-7 py-3.5 text-slate-600 dark:text-slate-300 font-semibold rounded-xl hover:text-primary-700 dark:hover:text-primary-400 transition-colors duration-200">
                        Tentang Kami
                    </a>
                </div>
            </div>

            {{-- Right: Slideshow --}}
            <div class="relative">
                <div class="group/hero relative aspect-square sm:aspect-video lg:aspect-square max-w-md mx-auto">
                    {{-- Carousel Frame: a fixed viewport that clips a wide sliding track --}}
                    <div x-ref="frame"
                         class="absolute inset-0 rounded-3xl overflow-hidden bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 touch-pan-y select-none"
                         @touchstart.passive="onStart($event)" @touchmove="onMove($event)" @touchend.passive="onEnd($event)">
                        {{-- Sliding track: one flex row, translated by whole slide widths --}}
                        <div x-ref="track"
                             class="flex h-full w-full will-change-transform"
                             :class="animate ? 'transition-transform duration-500 ease-out' : ''"
                             :style="trackStyle()"
                             @transitionend="onTransitionEnd($event)">
                            @foreach($heroSlides as $slide)
                                @php
                                    $product = $slide['product'];
                                    $imagePath = $product->image ? asset('storage/' . $product->image) : null;
                                    $productRoute = route('products.show', $product->slug);
                                @endphp
                                <div class="relative w-full h-full shrink-0 basis-full">
                                    {{-- Suppress the click that fires after a horizontal swipe --}}
                                    <a href="{{ $productRoute }}" class="group block w-full h-full relative"
                                       @click="moved && $event.preventDefault()" draggable="false">
                                        @if($imagePath)
                                            {{-- Every slide loads eagerly. The off-screen slides sit outside the
                                                 clipped frame, so with loading="lazy" the browser never even
                                                 requested them until they slid into view — swiping quickly (or the
                                                 5s autoplay) landed on a slide whose image had not started
                                                 downloading, and it showed up blank.

                                                 Only the first real slide keeps fetchpriority="high": it is the LCP
                                                 element. The rest are fetched at low priority so they queue behind
                                                 it instead of competing with it. Clones reuse the same src and are
                                                 served from cache. --}}
                                            <img src="{{ $imagePath }}" alt="{{ $product->name }}"
                                                 class="w-full h-full object-cover pointer-events-none transition-transform duration-700 group-hover:scale-110"
                                                 width="480" height="480" decoding="async" draggable="false"
                                                 loading="eager"
                                                 fetchpriority="{{ $slide['eager'] ? 'high' : 'low' }}">
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
                                        <div class="absolute bottom-6 left-6 right-6 p-4 bg-slate-900/75 backdrop-blur-md rounded-2xl border border-white/10 shadow-lg opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-4 group-hover:translate-y-0">
                                            <div class="flex items-center justify-between gap-4">
                                                <span class="text-sm font-bold text-white tracking-tight">{{ $product->name }}</span>
                                                <span class="text-[10px] font-black uppercase text-white tracking-widest bg-primary-600 px-2 py-1 rounded">Lihat</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Prev / Next Buttons --}}
                    <button x-show="count > 1" @click="prev(); resetAuto()" type="button"
                            class="absolute left-3 top-1/2 -translate-y-1/2 z-20 w-10 h-10 flex items-center justify-center rounded-full bg-white/90 dark:bg-slate-800/90 backdrop-blur border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 shadow-md hover:bg-white dark:hover:bg-slate-800 hover:scale-105 active:scale-95 opacity-100 lg:opacity-0 lg:group-hover/hero:opacity-100 transition-all duration-300"
                            aria-label="Slide sebelumnya">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                    </button>
                    <button x-show="count > 1" @click="next(); resetAuto()" type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 z-20 w-10 h-10 flex items-center justify-center rounded-full bg-white/90 dark:bg-slate-800/90 backdrop-blur border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 shadow-md hover:bg-white dark:hover:bg-slate-800 hover:scale-105 active:scale-95 opacity-100 lg:opacity-0 lg:group-hover/hero:opacity-100 transition-all duration-300"
                            aria-label="Slide berikutnya">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                    </button>

                    {{-- Navigation Dots --}}
                    <div x-show="count > 1" class="absolute -bottom-6 left-1/2 -translate-x-1/2 flex gap-1 z-20">
                        @foreach($featuredProducts as $index => $product)
                            <button @click="goto({{ $index }})" type="button"
                                    class="w-6 h-6 flex items-center justify-center rounded-full focus:outline-none"
                                    aria-label="Pilih slide {{ $index + 1 }}">
                                <span class="h-1.5 rounded-full transition-all duration-300"
                                      :class="real === {{ $index }} ? 'w-8 bg-primary-600' : 'w-2 bg-slate-300 dark:bg-slate-700 hover:bg-primary-300'"></span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ─── CATEGORY SECTION ─── --}}
<section class="bg-slate-50 dark:bg-slate-900 transition-colors duration-300">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
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
                        <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" width="80" height="80" loading="lazy" class="w-full h-full object-cover">
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
  </div>
</section>

{{-- ─── FEATURED PRODUCTS ─── --}}
<section class="bg-white dark:bg-slate-950 transition-colors duration-300 overflow-x-hidden">
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
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 lg:gap-6">
            @foreach($featuredProducts as $index => $product)
                <div class="reveal reveal-up delay-{{ ($index % 4 + 1) * 100 }}">
                    @include('components.product-card-grid', ['product' => $product])
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ─── FAQ SECTION ─── --}}
<section id="faq" class="bg-slate-50 dark:bg-slate-900 transition-colors duration-300 overflow-x-hidden">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
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
                <div class="border border-slate-100 dark:border-slate-700 rounded-2xl bg-white dark:bg-slate-800 overflow-hidden transition-all duration-300"
                     :class="{ 'border-primary-200 dark:border-primary-700 shadow-lg shadow-primary-50 dark:shadow-primary-900/10': activeFaq === {{ $index }} }">
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
                        <div class="px-6 pb-6 text-sm text-slate-500 dark:text-slate-400 leading-relaxed border-t border-slate-50 dark:border-slate-700 pt-4 transition-colors">
                            {{ $faq['a'] }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
  </div>
</section>

{{-- ─── CTA SECTION ─── --}}
<section class="bg-white dark:bg-slate-950 transition-colors duration-300">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20 reveal reveal-up">
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
  </div>
</section>

@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        // A real sliding carousel over a padded track: DOM order is
        // [cloneOfLast, slide0 … slideN-1, cloneOfFirst], so `index` runs 0..N+1
        // and the two clones let a wrap keep sliding one step in the same
        // direction, then snap back invisibly once the transition ends.
        Alpine.data('heroCarousel', (count) => ({
            count,
            index: count > 1 ? 1 : 0, // start on the first real slide, past the leading clone
            animate: true,
            timer: null,

            // drag state
            startX: 0,
            startY: 0,
            dragPx: 0,
            dragging: false,
            horizontal: false,
            moved: false,
            frameW: 1,

            get real() {
                if (this.count < 2) return 0;
                return ((this.index - 1) % this.count + this.count) % this.count;
            },

            init() {
                this.startAuto();
            },

            trackStyle() {
                return `transform: translateX(calc(${-this.index} * 100% + ${this.dragPx}px))`;
            },

            startAuto() {
                this.stopAuto();
                if (this.count > 1) this.timer = setInterval(() => this.next(), 5000);
            },
            stopAuto() {
                clearInterval(this.timer);
            },
            resetAuto() {
                this.startAuto();
            },

            next() {
                if (this.count < 2) return;
                this.animate = true;
                this.index++;
            },
            prev() {
                if (this.count < 2) return;
                this.animate = true;
                this.index--;
            },
            goto(realIndex) {
                this.animate = true;
                this.index = realIndex + 1;
                this.resetAuto();
            },

            // Fires when the track finishes moving. When it has landed on a clone,
            // jump to the matching real slide with animation off so it is invisible.
            onTransitionEnd(e) {
                // Ignore transitionend bubbling up from the image hover-zoom.
                if (e.target !== this.$refs.track) return;

                if (this.index === this.count + 1) {
                    this.animate = false;
                    this.index = 1;
                } else if (this.index === 0) {
                    this.animate = false;
                    this.index = this.count;
                }
            },

            onStart(e) {
                if (this.count < 2) return;
                this.dragging = true;
                this.horizontal = false;
                this.moved = false;
                this.startX = e.touches[0].clientX;
                this.startY = e.touches[0].clientY;
                this.dragPx = 0;
                this.frameW = this.$refs.frame.clientWidth || 1;
                this.stopAuto();
            },
            onMove(e) {
                if (!this.dragging) return;

                const dx = e.touches[0].clientX - this.startX;
                const dy = e.touches[0].clientY - this.startY;

                // Decide direction once: a mostly-vertical gesture is a page
                // scroll, so bow out and let the browser handle it.
                if (!this.horizontal) {
                    if (Math.abs(dx) < 8 && Math.abs(dy) < 8) return;
                    if (Math.abs(dy) > Math.abs(dx)) { this.dragging = false; return; }
                    this.horizontal = true;
                }

                e.preventDefault();
                this.moved = true;
                this.animate = false; // follow the finger with no easing lag
                this.dragPx = dx;
            },
            onEnd() {
                if (!this.dragging) return;
                this.dragging = false;

                const threshold = Math.max(40, this.frameW * 0.15);
                this.animate = true;

                if (this.dragPx <= -threshold) this.index++;
                else if (this.dragPx >= threshold) this.index--;

                this.dragPx = 0;
                this.startAuto();
            },
        }));
    });
</script>
@endpush
