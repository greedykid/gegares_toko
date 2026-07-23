@extends('layouts.app')
@section('title', 'Info Pengiriman')
@section('content')

@php
    $store = new \Illuminate\Support\Fluent(\Illuminate\Support\Facades\Cache::remember('store_settings', 86400, fn() => (\App\Models\StoreSetting::first() ?? new \App\Models\StoreSetting())->toArray()));
    $waRaw = $store->contact_whatsapp ?? $store->contact_phone ?? '';
    $waPhone = preg_replace('/[^0-9]/', '', (string) $waRaw);
    if (str_starts_with($waPhone, '0')) {
        $waPhone = '62' . substr($waPhone, 1);
    }
@endphp

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20 space-y-14">

    {{-- Hero --}}
    <div class="text-center max-w-3xl mx-auto space-y-4">
        <span class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-primary-50 dark:bg-primary-950/40 text-[11px] font-black uppercase tracking-[0.2em] text-primary-600 dark:text-primary-400 border border-primary-100/40 dark:border-primary-900/30">
            Info Pengiriman
        </span>
        <h1 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-slate-100 tracking-tight uppercase">
            Sampai <span class="text-primary-600 dark:text-primary-400">Segar</span> di Tangan Anda
        </h1>
        <p class="text-base sm:text-lg text-slate-500 dark:text-slate-400 font-semibold leading-relaxed">
            Semua jajanan pasar Gegares dibuat fresh setiap hari dan dikirim melalui kurir rekanan
            terintegrasi (Biteship) agar tiba dalam kondisi terbaik.
        </p>
        <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Terakhir diperbarui: 23 Juli 2026</p>
    </div>

    <div class="space-y-8">

        {{-- Metode Pengiriman --}}
        <section class="bg-slate-50 dark:bg-slate-900/40 rounded-3xl border-2 border-slate-100 dark:border-slate-800/80 p-8 lg:p-10 space-y-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 shrink-0 bg-primary-50 dark:bg-primary-950/40 rounded-2xl flex items-center justify-center text-primary-600 dark:text-primary-400 border border-primary-100/30 dark:border-primary-900/20">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                </div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight">Metode Pengiriman</h2>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-350 leading-relaxed font-medium">
                Saat checkout, Anda dapat memilih layanan kurir yang tersedia untuk alamat Anda. Ongkos kirim
                dihitung otomatis berdasarkan jarak dan berat pesanan.
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-white dark:bg-slate-900/50 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 space-y-2">
                    <p class="text-sm font-black text-slate-900 dark:text-white">Instan</p>
                    <p class="text-xs font-bold text-primary-600 dark:text-primary-400">~1 – 2 jam</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Paling direkomendasikan untuk kue basah &amp; gorengan hangat. Kurir menjemput dan mengantar langsung.</p>
                </div>
                <div class="bg-white dark:bg-slate-900/50 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 space-y-2">
                    <p class="text-sm font-black text-slate-900 dark:text-white">Same Day</p>
                    <p class="text-xs font-bold text-primary-600 dark:text-primary-400">Tiba hari yang sama</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Pilihan ekonomis untuk getuk, serabi, dan lontong. Ada batas jam penjemputan (lihat bagian di bawah).</p>
                </div>
            </div>
        </section>

        {{-- Waktu Proses & Penjemputan --}}
        <section class="bg-slate-50 dark:bg-slate-900/40 rounded-3xl border-2 border-slate-100 dark:border-slate-800/80 p-8 lg:p-10 space-y-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 shrink-0 bg-amber-50 dark:bg-amber-950/40 rounded-2xl flex items-center justify-center text-amber-600 dark:text-amber-400 border border-amber-100/30 dark:border-amber-900/20">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight">Waktu Proses &amp; Penjemputan</h2>
            </div>
            <ul class="space-y-3 text-sm text-slate-600 dark:text-slate-350 leading-relaxed font-medium">
                <li class="flex gap-3"><span class="text-primary-500 font-black">•</span><span>Toko beroperasi setiap hari pukul <strong class="text-slate-900 dark:text-white">06:00 – 17:00 WIB</strong>. Semua jajanan dibuat fresh di pagi hari.</span></li>
                <li class="flex gap-3"><span class="text-primary-500 font-black">•</span><span>Kurir hanya bisa menjemput pesanan saat toko buka dan ada staf yang menyerahkan paket. Pesanan yang dibayar di luar jam buka akan disiapkan dan <strong class="text-slate-900 dark:text-white">dijemput pada saat toko buka kembali</strong>.</span></li>
                <li class="flex gap-3"><span class="text-primary-500 font-black">•</span><span>Layanan <strong class="text-slate-900 dark:text-white">Same Day</strong> memiliki batas jam penjemputan kurir. Jika pembayaran melewati batas tersebut, penjemputan otomatis dijadwalkan ke jam operasional berikutnya.</span></li>
                <li class="flex gap-3"><span class="text-primary-500 font-black">•</span><span>Estimasi waktu tiba yang tampil di halaman detail pesanan bersifat dinamis. Jika penjemputan belum bisa dilakukan, sistem akan menampilkan perkiraan waktu paket benar-benar dijemput.</span></li>
            </ul>
        </section>

        {{-- Ongkir & Pelacakan --}}
        <section class="bg-slate-50 dark:bg-slate-900/40 rounded-3xl border-2 border-slate-100 dark:border-slate-800/80 p-8 lg:p-10 space-y-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 shrink-0 bg-emerald-50 dark:bg-emerald-950/40 rounded-2xl flex items-center justify-center text-emerald-600 dark:text-emerald-400 border border-emerald-100/30 dark:border-emerald-900/20">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                </div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight">Ongkos Kirim &amp; Pelacakan</h2>
            </div>
            <ul class="space-y-3 text-sm text-slate-600 dark:text-slate-350 leading-relaxed font-medium">
                <li class="flex gap-3"><span class="text-primary-500 font-black">•</span><span><strong class="text-slate-900 dark:text-white">Ongkos kirim</strong> dihitung real-time saat Anda memasukkan alamat pengiriman di halaman pemesanan. Biaya final akan terlihat sebelum Anda membayar.</span></li>
                <li class="flex gap-3"><span class="text-primary-500 font-black">•</span><span><strong class="text-slate-900 dark:text-white">Nomor resi</strong> otomatis muncul setelah pesanan dijemput kurir. Anda dapat memantau posisi paket kapan saja melalui halaman <em>Detail Pesanan</em>.</span></li>
                <li class="flex gap-3"><span class="text-primary-500 font-black">•</span><span>Setiap paket dikemas rapi dengan wadah <em>food grade</em> untuk menjaga kebersihan dan bentuk jajanan selama perjalanan.</span></li>
            </ul>
        </section>

        {{-- Catatan --}}
        <div class="bg-primary-50/60 dark:bg-primary-950/20 border border-primary-100 dark:border-primary-900/40 rounded-3xl p-6 sm:p-8">
            <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed font-semibold">
                <span class="font-black text-primary-700 dark:text-primary-400">Catatan kesegaran:</span>
                Karena jajanan kami dibuat tanpa pengawet, kami menyarankan memilih layanan tercepat untuk kue basah
                dan mengonsumsinya di hari yang sama. Ada pertanyaan seputar pengiriman?
                @if($waPhone)
                    Hubungi kami via <a href="https://wa.me/{{ $waPhone }}" target="_blank" class="font-black text-primary-600 dark:text-primary-400 underline">WhatsApp resmi</a>.
                @else
                    Hubungi kami melalui <a href="{{ route('contact') }}" class="font-black text-primary-600 dark:text-primary-400 underline">halaman kontak</a>.
                @endif
            </p>
        </div>

    </div>
</div>
@endsection
