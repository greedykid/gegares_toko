@extends('layouts.app')

@section('title', 'Tentang Kami')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20 space-y-20">

        {{-- Hero Section --}}
        <div class="text-center max-w-3xl mx-auto space-y-4">
            <span
                class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-primary-50 dark:bg-primary-950/40 text-[11px] font-black uppercase tracking-[0.2em] text-primary-600 dark:text-primary-400 border border-primary-100/40 dark:border-primary-900/30">
                Tentang Kami
            </span>
            <h1 class="text-4xl sm:text-5xl font-black text-slate-900 dark:text-slate-100 tracking-tight uppercase">
                Tentang <span class="text-primary-600">Gegares</span>
            </h1>
            <p class="text-lg text-slate-500 dark:text-slate-400 font-medium">
                Menghadirkan kelezatan jajanan pasar tradisional dengan kualitas premium dan resep autentik rumahan.
            </p>
        </div>

        {{-- Main Story & Vision/Mission Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
            {{-- Left: Deep Story --}}
            <div class="lg:col-span-7 space-y-6">
                <h2 class="text-2xl font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight">Cita Rasa
                    Warisan</h2>
                <div class="h-1.5 w-16 bg-primary-500 rounded-full"></div>
                <div
                    class="prose prose-slate dark:prose-invert max-w-none text-slate-600 dark:text-slate-400 space-y-4 leading-relaxed font-medium font-semibold">
                    <p
                        class="text-lg text-slate-800 dark:text-slate-200 border-l-4 border-primary-500 pl-6 italic font-medium">
                        Gegares adalah usaha kuliner rumahan yang didedikasikan untuk melestarikan dan menyajikan jajanan
                        pasar tradisional khas Indonesia dengan standar kualitas terbaik.
                    </p>
                    <p>
                        Kami memproduksi aneka kue basah dan gorengan legendaris seperti pastel renyah, onde-onde wijen
                        gurih, soes mini lembut, molen pisang manis, risol ayam padat, hingga dadar gulung wangi pandan.
                        Seluruh produk kami dibuat secara mandiri setiap dini hari untuk memastikan kesegaran maksimal saat
                        dikirimkan ke para pedagang mitra di pasar tradisional.
                    </p>
                    <p>
                        Selain menyuplai pedagang lokal melalui sistem titip jual (<i>consignment</i>), kami juga melayani
                        pemesanan skala besar untuk berbagai kebutuhan acara seperti rapat kantor, arisan, pengajian, ulang
                        tahun, hingga paket <i>snack box</i> eksklusif yang dapat dipesan secara mudah dan cepat.
                    </p>
                </div>
            </div>

            {{-- Right: Vision & Mission Cards --}}
            <div class="lg:col-span-5 space-y-6">
                <div
                    class="bg-slate-50 dark:bg-slate-900 border-2 border-slate-100 dark:border-slate-800/80 rounded-3xl p-8 space-y-4 transition-all duration-300 hover:border-primary-500/30">
                    <h3 class="text-lg font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight">Visi Kami
                    </h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed font-medium font-semibold">
                        Menjadi produsen jajanan tradisional pilihan utama keluarga yang mampu melestarikan cita rasa
                        Nusantara dengan kualitas premium, higienis, dan dapat diakses dengan mudah oleh semua kalangan.
                    </p>
                </div>

                <div
                    class="bg-slate-50 dark:bg-slate-900 border-2 border-slate-100 dark:border-slate-800/80 rounded-3xl p-8 space-y-4 transition-all duration-300 hover:border-primary-500/30">
                    <h3 class="text-lg font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight">Misi Kami
                    </h3>
                    <ul
                        class="text-sm text-slate-500 dark:text-slate-400 space-y-2 list-disc pl-5 leading-relaxed font-medium font-semibold">
                        <li>Menggunakan bahan baku segar berkualitas tinggi tanpa pengawet buatan.</li>
                        <li>Menjaga konsistensi resep tradisional warisan keluarga.</li>
                        <li>Mendukung ekosistem ekonomi pedagang pasar kecil lokal melalui skema titip jual yang adil.</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Carousel Galeri Foto (Split Layout - No Gradient & No Glassmorphism) --}}
        <div x-data="{ 
            activeSlide: 0,
            slides: [
                { image: '{{ asset('images/gallery/klepon.png') }}', title: 'Klepon Ketan Tradisional', desc: 'Kue bulat kenyal berbalut kelapa parut gurih dengan ledakan gula merah cair premium di dalamnya.' },
                { image: '{{ asset('images/gallery/risol_pastel.png') }}', title: 'Pastel & Risol Goreng Renyah', desc: 'Gorengan premium dengan isian wortel, kentang, dan daging ayam gurih dibungkus kulit renyah khas Gegares.' },
                { image: '{{ asset('images/gallery/jajanan_pasar_mix.png') }}', title: 'Aneka Jajanan Pasar', desc: 'Pilihan snack box mewah dan cantik yang siap melengkapi berbagai acara spesial dan formal Anda.' }
            ],
            next() {
                this.activeSlide = (this.activeSlide + 1) % this.slides.length;
            },
            prev() {
                this.activeSlide = (this.activeSlide - 1 + this.slides.length) % this.slides.length;
            },
            init() {
                setInterval(() => this.next(), 6000);
            }
        }"
            class="w-full max-w-5xl mx-auto rounded-[2rem] border-2 border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xl shadow-slate-100/50 dark:shadow-none overflow-hidden transition-all duration-300">

            <div class="grid grid-cols-1 lg:grid-cols-12 h-full min-h-[420px]">
                {{-- Left: Carousel Images (7 cols) --}}
                <div
                    class="lg:col-span-7 relative bg-slate-100 dark:bg-slate-950 aspect-[4/3] lg:aspect-auto overflow-hidden">
                    <template x-for="(slide, index) in slides" :key="index">
                        <div x-show="activeSlide === index" x-transition:enter="transition ease-out duration-700 transform"
                            x-transition:enter-start="opacity-0 scale-105" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-500 transform"
                            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="absolute inset-0 w-full h-full">
                            <img :src="slide.image" :alt="slide.title" class="w-full h-full object-cover select-none">
                        </div>
                    </template>
                </div>

                {{-- Right: Text Description Panel (5 cols) --}}
                <div
                    class="lg:col-span-5 bg-slate-50 dark:bg-slate-950/40 p-8 sm:p-12 flex flex-col justify-between border-t lg:border-t-0 lg:border-l-2 border-slate-100 dark:border-slate-800">
                    <div class="space-y-6">
                        <span
                            class="inline-flex items-center px-3 py-1 text-[10px] font-black tracking-widest text-primary-600 dark:text-primary-400 bg-primary-100/60 dark:bg-primary-950/60 border border-primary-200/50 dark:border-primary-900/40 uppercase rounded-full">
                            Galeri Kuliner
                        </span>
                        <div class="relative min-h-[140px]">
                            <template x-for="(slide, index) in slides" :key="index">
                                <div x-show="activeSlide === index"
                                    x-transition:enter="transition ease-out duration-500 delay-100"
                                    x-transition:enter-start="opacity-0 translate-y-2"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-300 absolute"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 -translate-y-2" class="w-full">
                                    <h3 x-text="slide.title"
                                        class="text-2xl font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight text-wrap">
                                    </h3>
                                    <p x-text="slide.desc"
                                        class="mt-4 text-sm font-medium text-slate-500 dark:text-slate-400 leading-relaxed">
                                    </p>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Interactive controls (Dots & Arrows) --}}
                    <div
                        class="mt-8 pt-8 border-t border-slate-200/50 dark:border-slate-800/80 flex items-center justify-between">
                        {{-- Dots Indicator --}}
                        <div class="flex gap-2">
                            <template x-for="(slide, index) in slides" :key="index">
                                <button @click="activeSlide = index" aria-label="Slide"
                                    :class="activeSlide === index ? 'w-8 bg-primary-600 border-primary-600' : 'w-2.5 bg-slate-200 dark:bg-slate-700 border-transparent hover:bg-slate-300 dark:hover:bg-slate-600'"
                                    class="h-2.5 rounded-full border transition-all duration-300"></button>
                            </template>
                        </div>

                        {{-- Navigation Arrows --}}
                        <div class="flex gap-2">
                            <button @click="prev()" aria-label="Slide Sebelumnya"
                                class="w-10 h-10 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/80 text-slate-600 dark:text-slate-300 hover:bg-primary-600 hover:text-white hover:border-primary-600 flex items-center justify-center transition-all active:scale-95 shadow-sm">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                                </svg>
                            </button>
                            <button @click="next()" aria-label="Slide Selanjutnya"
                                class="w-10 h-10 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/80 text-slate-600 dark:text-slate-300 hover:bg-primary-600 hover:text-white hover:border-primary-600 flex items-center justify-center transition-all active:scale-95 shadow-sm">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Core Pillars/Highlights Section --}}
        <div class="space-y-10">
            <div class="text-center max-w-xl mx-auto space-y-2">
                <h2 class="text-2xl font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight">Kelebihan
                    Gegares</h2>
                <p class="text-sm font-semibold text-slate-400 dark:text-slate-500">Mengapa produk kami dicintai oleh
                    pelanggan dan mitra pedagang?</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div
                    class="group bg-white dark:bg-slate-900 border-2 border-slate-100 dark:border-slate-800 rounded-3xl p-8 transition-all duration-300 hover:border-primary-500 hover:-translate-y-1">
                    <div
                        class="w-12 h-12 rounded-2xl bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 flex items-center justify-center mb-6 border border-primary-100/30 dark:border-primary-900/20 group-hover:bg-primary-600 group-hover:text-white transition-colors duration-300">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight">Dibuat Dengan
                        Cinta</h3>
                    <p class="mt-3 text-sm font-medium text-slate-500 dark:text-slate-400 leading-relaxed font-semibold">
                        Setiap resep dikerjakan secara cermat dan tradisional oleh pembuat kue berpengalaman untuk
                        menghasilkan cita rasa autentik.
                    </p>
                </div>

                <div
                    class="group bg-white dark:bg-slate-900 border-2 border-slate-100 dark:border-slate-800 rounded-3xl p-8 transition-all duration-300 hover:border-accent-500 hover:-translate-y-1">
                    <div
                        class="w-12 h-12 rounded-2xl bg-accent-50 dark:bg-accent-950/40 text-accent-600 dark:text-accent-400 flex items-center justify-center mb-6 border border-accent-100/30 dark:border-accent-900/20 group-hover:bg-accent-600 group-hover:text-white transition-colors duration-300">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.036 2.036 0 0 0-1.737-.833H14.25M16.5 18.75h-2.25m0-11.177v11.177M3.517 5.035a18.683 18.683 0 0 0-1.285 8.365a1.5 1.5 0 0 0 1.5 1.5h7.5A1.5 1.5 0 0 0 12 13.5V6a1.5 1.5 0 0 0-1.5-1.5H5.036a1.5 1.5 0 0 0-1.519 1.535Z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight">Pengiriman
                        Cepat</h3>
                    <p class="mt-3 text-sm font-medium text-slate-500 dark:text-slate-400 leading-relaxed font-semibold">
                        Pesanan dikirim sesaat setelah matang dengan penanganan higienis agar sampai ke tangan Anda dalam
                        kondisi segar optimal.
                    </p>
                </div>

                <div
                    class="group bg-white dark:bg-slate-900 border-2 border-slate-100 dark:border-slate-800 rounded-3xl p-8 transition-all duration-300 hover:border-emerald-500 hover:-translate-y-1">
                    <div
                        class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-6 border border-emerald-100/30 dark:border-emerald-900/20 group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-300">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight">Terjamin
                        Kualitas</h3>
                    <p class="mt-3 text-sm font-medium text-slate-500 dark:text-slate-400 leading-relaxed font-semibold">
                        Hanya menggunakan bahan-bahan alami premium tanpa bahan pengawet sintetik untuk menjamin cita rasa
                        lezat yang sehat.
                    </p>
                </div>
            </div>
        </div>

    </div>
@endsection