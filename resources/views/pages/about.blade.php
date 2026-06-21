@extends('layouts.app')

@section('title', 'Tentang Kami')

@section('content')
@php
    $settings = \App\Models\StoreSetting::first() ?? new \App\Models\StoreSetting();
    
    $aboutTitle = $settings->about_title ?? 'Tentang <span class="text-primary-600 dark:text-primary-400">Gegares</span>';
    $aboutSubtitle = $settings->about_subtitle ?? 'Menghadirkan kelezatan jajanan pasar tradisional dengan kualitas premium dan resep autentik rumahan.';
    $aboutStoryTitle = $settings->about_story_title ?? 'Cita Rasa Warisan';
    $aboutStoryContent = $settings->about_story_content ?? "Gegares adalah usaha kuliner rumahan yang didedikasikan untuk melestarikan dan menyajikan jajanan pasar tradisional khas Indonesia dengan standar kualitas terbaik.\n\nKami memproduksi aneka kue basah dan gorengan legendaris seperti pastel renyah, onde-onde wijen gurih, soes mini lembut, molen pisang manis, risol ayam padat, hingga dadar gulung wangi pandan. Seluruh produk kami dibuat secara mandiri setiap dini hari untuk memastikan kesegaran maksimal saat dikirimkan ke para pedagang mitra di pasar tradisional.\n\nSelain menyuplai pedagang lokal melalui sistem titip jual (consignment), kami juga melayani pemesanan skala besar untuk berbagai kebutuhan acara seperti rapat kantor, arisan, pengajian, ulang tahun, hingga paket snack box eksklusif yang dapat dipesan secara mudah dan cepat.";
    $aboutVision = $settings->about_vision ?? 'Menjadi produsen jajanan tradisional pilihan utama keluarga yang mampu melestarikan cita rasa Nusantara dengan kualitas premium, higienis, dan dapat diakses dengan mudah oleh semua kalangan.';
    $aboutMission = $settings->about_mission ?? [
        'Menggunakan bahan baku segar berkualitas tinggi tanpa pengawet buatan.',
        'Menjaga konsistensi resep tradisional warisan keluarga.',
        'Mendukung ekosistem ekonomi pedagang pasar kecil lokal melalui skema titip jual yang adil.'
    ];
    $aboutGallery = $settings->about_gallery ?? [];
@endphp
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20 space-y-16">

        {{-- Hero Section --}}
        <div class="text-center max-w-3xl mx-auto space-y-4">
            <span class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-primary-50 dark:bg-primary-950/40 text-[11px] font-black uppercase tracking-[0.2em] text-primary-600 dark:text-primary-400 border border-primary-100/40 dark:border-primary-900/30">
                Tentang Kami
            </span>
            <h1 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-slate-100 tracking-tight uppercase">
                {!! $aboutTitle !!}
            </h1>
            <p class="text-base sm:text-lg text-slate-500 dark:text-slate-400 font-semibold leading-relaxed">
                {{ $aboutSubtitle }}
            </p>
        </div>

        {{-- Story Section --}}
        <div class="max-w-4xl mx-auto bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800/60 rounded-3xl p-8 sm:p-10 space-y-6">
            <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100 uppercase tracking-tight">{{ $aboutStoryTitle }}</h2>
            <div class="h-1 w-12 bg-primary-500 rounded-full"></div>
            <div class="text-sm sm:text-base text-slate-600 dark:text-slate-400 space-y-4 leading-relaxed font-semibold whitespace-pre-line">
                {!! nl2br(e($aboutStoryContent)) !!}
            </div>
        </div>

        {{-- Vision & Mission --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
            <div class="bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800/60 rounded-3xl p-8 space-y-4">
                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 uppercase tracking-tight">Visi Kami</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed font-semibold">
                    {{ $aboutVision }}
                </p>
            </div>

            <div class="bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800/60 rounded-3xl p-8 space-y-4">
                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 uppercase tracking-tight">Misi Kami</h3>
                <ul class="text-sm text-slate-500 dark:text-slate-400 space-y-2 list-disc pl-5 leading-relaxed font-semibold">
                    @foreach($aboutMission as $missionItem)
                        <li>{{ $missionItem }}</li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Core Pillars / Kelebihan Gegares --}}
        <div class="space-y-10 max-w-5xl mx-auto pt-6">
            <div class="text-center max-w-xl mx-auto space-y-2">
                <h2 class="text-2xl font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight">Kelebihan Gegares</h2>
                <p class="text-sm font-semibold text-slate-400 dark:text-slate-500">Mengapa produk kami dicintai oleh pelanggan dan mitra pedagang?</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="group bg-white dark:bg-slate-900/20 border-2 border-slate-100 dark:border-slate-800/80 rounded-3xl p-8 transition-all duration-300 hover:border-primary-500 hover:-translate-y-1">
                    <div class="w-12 h-12 rounded-2xl bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 flex items-center justify-center mb-6 border border-primary-100/30 dark:border-primary-900/20 group-hover:bg-primary-600 group-hover:text-white transition-colors duration-300">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 uppercase tracking-tight">Dibuat Dengan Cinta</h3>
                    <p class="mt-3 text-sm font-medium text-slate-500 dark:text-slate-400 leading-relaxed font-semibold">
                        Setiap resep dikerjakan secara cermat dan tradisional oleh pembuat kue berpengalaman untuk menghasilkan cita rasa autentik.
                    </p>
                </div>

                <div class="group bg-white dark:bg-slate-900/20 border-2 border-slate-100 dark:border-slate-800/80 rounded-3xl p-8 transition-all duration-300 hover:border-primary-500 hover:-translate-y-1">
                    <div class="w-12 h-12 rounded-2xl bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 flex items-center justify-center mb-6 border border-primary-100/30 dark:border-primary-900/20 group-hover:bg-primary-600 group-hover:text-white transition-colors duration-300">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.036 2.036 0 0 0-1.737-.833H14.25M16.5 18.75h-2.25m0-11.177v11.177M3.517 5.035a18.683 18.683 0 0 0-1.285 8.365a1.5 1.5 0 0 0 1.5 1.5h7.5A1.5 1.5 0 0 0 12 13.5V6a1.5 1.5 0 0 0-1.5-1.5H5.036a1.5 1.5 0 0 0-1.519 1.535Z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 uppercase tracking-tight">Pengiriman Cepat</h3>
                    <p class="mt-3 text-sm font-medium text-slate-500 dark:text-slate-400 leading-relaxed font-semibold">
                        Pesanan dikirim sesaat setelah matang dengan penanganan higienis agar sampai ke tangan Anda dalam kondisi segar optimal.
                    </p>
                </div>

                <div class="group bg-white dark:bg-slate-900/20 border-2 border-slate-100 dark:border-slate-800/80 rounded-3xl p-8 transition-all duration-300 hover:border-primary-500 hover:-translate-y-1">
                    <div class="w-12 h-12 rounded-2xl bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 flex items-center justify-center mb-6 border border-primary-100/30 dark:border-primary-900/20 group-hover:bg-primary-600 group-hover:text-white transition-colors duration-300">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 uppercase tracking-tight">Terjamin Kualitas</h3>
                    <p class="mt-3 text-sm font-medium text-slate-500 dark:text-slate-400 leading-relaxed font-semibold">
                        Hanya menggunakan bahan-bahan alami premium tanpa bahan pengawet sintetik untuk menjamin cita rasa lezat yang sehat.
                    </p>
                </div>
            </div>
        </div>

        {{-- Gallery Section for Process Images --}}
        @if(!empty($aboutGallery))
            <div class="space-y-10 max-w-5xl mx-auto pt-10 border-t border-slate-100 dark:border-slate-850">
                <div class="text-center max-w-xl mx-auto space-y-2">
                    <span class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-accent-50 dark:bg-accent-950/40 text-[10px] font-black uppercase tracking-[0.2em] text-accent-600 dark:text-accent-400 border border-accent-100/40 dark:border-accent-900/30">
                        Galeri Kegiatan
                    </span>
                    <h2 class="text-2xl font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight">Proses Produksi Kami</h2>
                    <p class="text-sm font-semibold text-slate-400 dark:text-slate-500">Melihat langsung bagaimana jajanan pasar legendaris kami dibuat secara higienis setiap dini hari.</p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    @foreach($aboutGallery as $index => $path)
                        <div class="group relative aspect-square rounded-3xl overflow-hidden border border-slate-100 dark:border-slate-800/80 shadow-xs hover:shadow-xl transition-all duration-300 hover:-translate-y-1 bg-slate-50 dark:bg-slate-950">
                            <img src="{{ asset('storage/' . $path) }}" alt="Proses Produksi {{ $index + 1 }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute inset-0 bg-linear-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                                <span class="text-xs font-bold text-white tracking-wide">Proses Produksi {{ $index + 1 }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection