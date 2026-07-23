@extends('layouts.app')
@section('title', 'Syarat & Ketentuan')
@section('content')

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20 space-y-14">

    {{-- Hero --}}
    <div class="text-center max-w-3xl mx-auto space-y-4">
        <span class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-primary-50 dark:bg-primary-950/40 text-[11px] font-black uppercase tracking-[0.2em] text-primary-600 dark:text-primary-400 border border-primary-100/40 dark:border-primary-900/30">
            Syarat &amp; Ketentuan
        </span>
        <h1 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-slate-100 tracking-tight uppercase">
            Ketentuan <span class="text-primary-600 dark:text-primary-400">Layanan</span>
        </h1>
        <p class="text-base sm:text-lg text-slate-500 dark:text-slate-400 font-semibold leading-relaxed">
            Dengan membuat akun atau melakukan pemesanan di Gegares, Anda dianggap telah membaca, memahami,
            dan menyetujui seluruh ketentuan berikut.
        </p>
        <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Terakhir diperbarui: 23 Juli 2026</p>
    </div>

    <div class="space-y-8">

        {{-- 1. Akun --}}
        <section class="bg-slate-50 dark:bg-slate-900/40 rounded-3xl border-2 border-slate-100 dark:border-slate-800/80 p-8 lg:p-10 space-y-5">
            <h2 class="text-lg sm:text-xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
                <span class="text-primary-500">01.</span> Akun Pengguna
            </h2>
            <ul class="space-y-3 text-sm text-slate-600 dark:text-slate-350 leading-relaxed font-medium">
                <li class="flex gap-3"><span class="text-primary-500 font-black">•</span><span>Anda bertanggung jawab menjaga kerahasiaan kata sandi serta seluruh aktivitas yang terjadi pada akun Anda.</span></li>
                <li class="flex gap-3"><span class="text-primary-500 font-black">•</span><span>Data yang Anda berikan (termasuk nama, nomor telepon, dan alamat pengiriman) wajib benar dan akurat agar pesanan dapat dikirim dengan tepat.</span></li>
            </ul>
        </section>

        {{-- 2. Pemesanan & Pembayaran --}}
        <section class="bg-slate-50 dark:bg-slate-900/40 rounded-3xl border-2 border-slate-100 dark:border-slate-800/80 p-8 lg:p-10 space-y-5">
            <h2 class="text-lg sm:text-xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
                <span class="text-primary-500">02.</span> Pemesanan &amp; Pembayaran
            </h2>
            <ul class="space-y-3 text-sm text-slate-600 dark:text-slate-350 leading-relaxed font-medium">
                <li class="flex gap-3"><span class="text-primary-500 font-black">•</span><span>Semua pembayaran diproses secara aman melalui payment gateway resmi <strong class="text-slate-900 dark:text-white">Pakasir</strong>. Pesanan baru diproses setelah pembayaran terkonfirmasi.</span></li>
                <li class="flex gap-3"><span class="text-primary-500 font-black">•</span><span>Pesanan yang <strong class="text-slate-900 dark:text-white">belum dibayar dalam 24 jam</strong> akan otomatis dibatalkan, dan stok yang dipesan akan dilepas kembali.</span></li>
                <li class="flex gap-3"><span class="text-primary-500 font-black">•</span><span>Harga produk dan ongkos kirim yang berlaku adalah yang tertera pada saat Anda menyelesaikan pembayaran.</span></li>
            </ul>
        </section>

        {{-- 3. Kesegaran Produk --}}
        <section class="bg-slate-50 dark:bg-slate-900/40 rounded-3xl border-2 border-slate-100 dark:border-slate-800/80 p-8 lg:p-10 space-y-5">
            <h2 class="text-lg sm:text-xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
                <span class="text-primary-500">03.</span> Kesegaran Produk
            </h2>
            <ul class="space-y-3 text-sm text-slate-600 dark:text-slate-350 leading-relaxed font-medium">
                <li class="flex gap-3"><span class="text-primary-500 font-black">•</span><span>Jajanan kami dibuat fresh secara terjadwal dan <strong class="text-slate-900 dark:text-white">bebas pengawet</strong>. Kami menyarankan konsumsi di hari yang sama.</span></li>
                <li class="flex gap-3"><span class="text-primary-500 font-black">•</span><span>Tips penyimpanan untuk tiap produk dapat Anda tanyakan langsung melalui chatbot kami.</span></li>
            </ul>
        </section>

        {{-- 4. Pembatalan & Refund --}}
        <section class="bg-slate-50 dark:bg-slate-900/40 rounded-3xl border-2 border-slate-100 dark:border-slate-800/80 p-8 lg:p-10 space-y-5">
            <h2 class="text-lg sm:text-xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
                <span class="text-primary-500">04.</span> Pembatalan &amp; Pengembalian Dana
            </h2>
            <ul class="space-y-3 text-sm text-slate-600 dark:text-slate-350 leading-relaxed font-medium">
                <li class="flex gap-3"><span class="text-primary-500 font-black">•</span><span>Karena jajanan dibuat fresh, pesanan yang <strong class="text-slate-900 dark:text-white">sudah diproses oleh dapur</strong> tidak dapat dibatalkan atau diubah.</span></li>
                <li class="flex gap-3"><span class="text-primary-500 font-black">•</span><span>Apabila sebuah pesanan yang sudah dibayar dibatalkan (mis. stok habis atau kendala operasional), dana Anda akan dikembalikan sepenuhnya, dan kami akan menghubungi Anda mengenai prosesnya.</span></li>
            </ul>
        </section>

        {{-- 5. Pengiriman --}}
        <section class="bg-slate-50 dark:bg-slate-900/40 rounded-3xl border-2 border-slate-100 dark:border-slate-800/80 p-8 lg:p-10 space-y-5">
            <h2 class="text-lg sm:text-xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
                <span class="text-primary-500">05.</span> Pengiriman
            </h2>
            <p class="text-sm text-slate-600 dark:text-slate-350 leading-relaxed font-medium">
                Pengiriman dilakukan melalui kurir rekanan (Biteship) dan tunduk pada jam operasional toko serta
                batas jam penjemputan kurir. Detail lengkapnya dapat Anda baca pada halaman
                <a href="{{ route('shipping') }}" class="font-black text-primary-600 dark:text-primary-400 underline">Info Pengiriman</a>.
                Estimasi waktu tiba bersifat perkiraan dan dapat dipengaruhi kondisi di luar kendali kami seperti cuaca dan lalu lintas.
            </p>
        </section>

        {{-- 6. Konten & Ulasan --}}
        <section class="bg-slate-50 dark:bg-slate-900/40 rounded-3xl border-2 border-slate-100 dark:border-slate-800/80 p-8 lg:p-10 space-y-5">
            <h2 class="text-lg sm:text-xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
                <span class="text-primary-500">06.</span> Ulasan &amp; Konten Pengguna
            </h2>
            <p class="text-sm text-slate-600 dark:text-slate-350 leading-relaxed font-medium">
                Ulasan yang Anda kirimkan harus jujur dan sopan. Kami berhak menyunting atau menghapus ulasan yang
                mengandung kata-kata kasar, SARA, spam, atau konten yang melanggar hukum.
            </p>
        </section>

        {{-- Kontak --}}
        <div class="bg-primary-50/60 dark:bg-primary-950/20 border border-primary-100 dark:border-primary-900/40 rounded-3xl p-6 sm:p-8">
            <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed font-semibold">
                <span class="font-black text-primary-700 dark:text-primary-400">Butuh penjelasan lebih lanjut?</span>
                Ketentuan ini dapat diperbarui sewaktu-waktu. Untuk pertanyaan, silakan kunjungi
                <a href="{{ route('contact') }}" class="font-black text-primary-600 dark:text-primary-400 underline">halaman kontak</a>
                atau baca <a href="{{ route('privacy') }}" class="font-black text-primary-600 dark:text-primary-400 underline">Kebijakan Privasi</a> kami.
            </p>
        </div>

    </div>
</div>
@endsection
