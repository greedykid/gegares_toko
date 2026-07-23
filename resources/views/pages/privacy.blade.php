@extends('layouts.app')
@section('title', 'Kebijakan Privasi')
@section('content')

@php
    $store = new \Illuminate\Support\Fluent(\Illuminate\Support\Facades\Cache::remember('store_settings', 86400, fn() => (\App\Models\StoreSetting::first() ?? new \App\Models\StoreSetting())->toArray()));
    $email = $store->contact_email ?? 'hello@gegares.shop';
@endphp

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20 space-y-14">

    {{-- Hero --}}
    <div class="text-center max-w-3xl mx-auto space-y-4">
        <span class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-primary-50 dark:bg-primary-950/40 text-[11px] font-black uppercase tracking-[0.2em] text-primary-600 dark:text-primary-400 border border-primary-100/40 dark:border-primary-900/30">
            Kebijakan Privasi
        </span>
        <h1 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-slate-100 tracking-tight uppercase">
            Data Anda <span class="text-primary-600 dark:text-primary-400">Terlindungi</span>
        </h1>
        <p class="text-base sm:text-lg text-slate-500 dark:text-slate-400 font-semibold leading-relaxed">
            Gegares berkomitmen penuh menjaga kerahasiaan dan keamanan informasi pribadi setiap pelanggan.
            Berikut cara kami mengumpulkan, menggunakan, dan melindungi data Anda.
        </p>
        <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Terakhir diperbarui: 23 Juli 2026</p>
    </div>

    <div class="space-y-8">

        {{-- 1. Data yang dikumpulkan --}}
        <section class="bg-slate-50 dark:bg-slate-900/40 rounded-3xl border-2 border-slate-100 dark:border-slate-800/80 p-8 lg:p-10 space-y-5">
            <h2 class="text-lg sm:text-xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
                <span class="text-primary-500">01.</span> Informasi yang Kami Kumpulkan
            </h2>
            <ul class="space-y-3 text-sm text-slate-600 dark:text-slate-350 leading-relaxed font-medium">
                <li class="flex gap-3"><span class="text-primary-500 font-black">•</span><span><strong class="text-slate-900 dark:text-white">Data profil:</strong> nama, alamat email, nomor telepon, dan alamat pengiriman yang Anda masukkan untuk keperluan pembuatan akun dan pengiriman pesanan.</span></li>
                <li class="flex gap-3"><span class="text-primary-500 font-black">•</span><span><strong class="text-slate-900 dark:text-white">Data transaksi:</strong> riwayat pesanan, produk yang dibeli, serta status pembayaran dan pengiriman.</span></li>
                <li class="flex gap-3"><span class="text-primary-500 font-black">•</span><span><strong class="text-slate-900 dark:text-white">Data akun Google:</strong> jika Anda memilih masuk dengan Google, kami hanya menerima nama, email, dan foto profil publik Anda.</span></li>
            </ul>
        </section>

        {{-- 2. Penggunaan data --}}
        <section class="bg-slate-50 dark:bg-slate-900/40 rounded-3xl border-2 border-slate-100 dark:border-slate-800/80 p-8 lg:p-10 space-y-5">
            <h2 class="text-lg sm:text-xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
                <span class="text-primary-500">02.</span> Bagaimana Data Digunakan
            </h2>
            <ul class="space-y-3 text-sm text-slate-600 dark:text-slate-350 leading-relaxed font-medium">
                <li class="flex gap-3"><span class="text-primary-500 font-black">•</span><span>Memproses dan mengirimkan pesanan Anda hingga sampai tujuan.</span></li>
                <li class="flex gap-3"><span class="text-primary-500 font-black">•</span><span>Menghubungi Anda terkait status pesanan, pembayaran, atau kendala pengiriman.</span></li>
                <li class="flex gap-3"><span class="text-primary-500 font-black">•</span><span>Meningkatkan kualitas layanan, produk, dan pengalaman berbelanja di situs kami.</span></li>
            </ul>
        </section>

        {{-- 3. Keamanan --}}
        <section class="bg-slate-50 dark:bg-slate-900/40 rounded-3xl border-2 border-slate-100 dark:border-slate-800/80 p-8 lg:p-10 space-y-5">
            <h2 class="text-lg sm:text-xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
                <span class="text-primary-500">03.</span> Keamanan Data
            </h2>
            <ul class="space-y-3 text-sm text-slate-600 dark:text-slate-350 leading-relaxed font-medium">
                <li class="flex gap-3"><span class="text-primary-500 font-black">•</span><span>Kata sandi Anda disimpan dalam bentuk <em>hash</em> terenkripsi, tidak pernah disimpan sebagai teks biasa, dan tidak dapat kami lihat.</span></li>
                <li class="flex gap-3"><span class="text-primary-500 font-black">•</span><span>Informasi sensitif ditampilkan dengan <em>masking</em> otomatis untuk mencegah kebocoran data di layar.</span></li>
                <li class="flex gap-3"><span class="text-primary-500 font-black">•</span><span>Transaksi pembayaran diproses langsung melalui payment gateway resmi, sehingga detail pembayaran Anda tidak disimpan di server kami.</span></li>
            </ul>
        </section>

        {{-- 4. Pihak ketiga --}}
        <section class="bg-slate-50 dark:bg-slate-900/40 rounded-3xl border-2 border-slate-100 dark:border-slate-800/80 p-8 lg:p-10 space-y-5">
            <h2 class="text-lg sm:text-xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
                <span class="text-primary-500">04.</span> Berbagi dengan Pihak Ketiga
            </h2>
            <p class="text-sm text-slate-600 dark:text-slate-350 leading-relaxed font-medium">
                Kami <strong class="text-slate-900 dark:text-white">tidak pernah menjual</strong> data pribadi Anda. Data hanya dibagikan
                seperlunya kepada mitra tepercaya berikut demi kelancaran layanan:
            </p>
            <ul class="space-y-3 text-sm text-slate-600 dark:text-slate-350 leading-relaxed font-medium">
                <li class="flex gap-3"><span class="text-primary-500 font-black">•</span><span><strong class="text-slate-900 dark:text-white">Biteship</strong>, mitra logistik untuk penjemputan dan pengiriman paket.</span></li>
                <li class="flex gap-3"><span class="text-primary-500 font-black">•</span><span><strong class="text-slate-900 dark:text-white">Pakasir</strong>, penyedia payment gateway untuk memproses pembayaran secara aman.</span></li>
                <li class="flex gap-3"><span class="text-primary-500 font-black">•</span><span><strong class="text-slate-900 dark:text-white">Google</strong>, hanya jika Anda memilih untuk masuk menggunakan akun Google.</span></li>
            </ul>
        </section>

        {{-- 5. Hak pengguna --}}
        <section class="bg-slate-50 dark:bg-slate-900/40 rounded-3xl border-2 border-slate-100 dark:border-slate-800/80 p-8 lg:p-10 space-y-5">
            <h2 class="text-lg sm:text-xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
                <span class="text-primary-500">05.</span> Hak Anda atas Data
            </h2>
            <ul class="space-y-3 text-sm text-slate-600 dark:text-slate-350 leading-relaxed font-medium">
                <li class="flex gap-3"><span class="text-primary-500 font-black">•</span><span>Anda dapat mengakses dan memperbarui data profil Anda kapan saja melalui halaman <em>Pengaturan</em>.</span></li>
                <li class="flex gap-3"><span class="text-primary-500 font-black">•</span><span>Anda berhak meminta penghapusan akun beserta data pribadi Anda dengan menghubungi kami.</span></li>
            </ul>
        </section>

        {{-- Kontak --}}
        <div class="bg-primary-50/60 dark:bg-primary-950/20 border border-primary-100 dark:border-primary-900/40 rounded-3xl p-6 sm:p-8">
            <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed font-semibold">
                <span class="font-black text-primary-700 dark:text-primary-400">Ada pertanyaan soal privasi?</span>
                Hubungi kami di <a href="mailto:{{ $email }}" class="font-black text-primary-600 dark:text-primary-400 underline">{{ $email }}</a>
                atau melalui <a href="{{ route('contact') }}" class="font-black text-primary-600 dark:text-primary-400 underline">halaman kontak</a>.
                Kebijakan ini dapat diperbarui sewaktu-waktu; perubahan penting akan kami tampilkan di halaman ini.
            </p>
        </div>

    </div>
</div>
@endsection
