@extends('layouts.app')
@section('title', 'Detail Pesanan')
@section('content')
@php
    // Store WhatsApp for the refund enquiry below (same normalisation the
    // contact page uses: strip non-digits, 0xxx → 62xxx).
    $store = new \Illuminate\Support\Fluent(\Illuminate\Support\Facades\Cache::remember('store_settings', 86400, fn () => (\App\Models\StoreSetting::first() ?? new \App\Models\StoreSetting)->toArray()));
    $refundWa = preg_replace('/[^0-9]/', '', $store->contact_whatsapp ?? $store->contact_phone ?? '6281234567890');
    if (str_starts_with($refundWa, '0')) {
        $refundWa = '62'.substr($refundWa, 1);
    }
    $refundText = "Halo Gegares, saya ingin menanyakan pengembalian dana untuk pesanan *{$order->order_number}* senilai *{$order->formatted_total}* yang dibatalkan. Terima kasih.";
    $refundUrl = 'https://wa.me/'.$refundWa.'?text='.rawurlencode($refundText);
@endphp
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
    {{-- Header / Breadcrumb --}}
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('orders.index') }}" class="flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-slate-900 shadow-sm ring-1 ring-slate-200 dark:ring-slate-800 text-slate-500 hover:text-primary-600 dark:text-slate-400 dark:hover:text-primary-400 transition-all hover:-translate-x-1">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        </a>
        <div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Detail Pesanan</h1>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mt-1 select-all">{{ $order->order_number }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 xl:gap-10" 
          x-data="{ 
             loadingTracking: false, 
             trackingData: null,
             liveStatus: '{{ $order->status }}',
             liveStatusLabel: '{{ $order->status_label }}',
             fetchTracking() {
                 // Normally there is nothing to ask for without a waybill. On a
                 // demo deployment the endpoint answers with a stand-in courier,
                 // so the call is still worth making.
                 if (!'{{ $order->tracking_number }}' && !{{ \App\Support\DemoCourier::appliesTo($order) ? 'true' : 'false' }}) return;
                 this.loadingTracking = true;
                 fetch('{{ route('orders.tracking', $order) }}')
                     .then(res => res.json())
                     .then(data => {
                         if (data.success) {
                             this.trackingData = data;
                             // Synchronize live status with the courier progress
                             if (data.status === 'delivered') {
                                 this.liveStatus = 'completed';
                                 this.liveStatusLabel = 'Selesai';
                             } else if (['allocated', 'picking_up', 'picked_up', 'dropping_off', 'on_the_way', 'in_transit'].includes(data.status)) {
                                 this.liveStatus = 'shipped';
                                 this.liveStatusLabel = 'Dikirim';
                             }
                         }
                     })
                     .finally(() => this.loadingTracking = false);
             }
          }"
          x-init="fetchTracking()">
          
         {{-- Left Column: Main Contents --}}
         <div class="lg:col-span-7 xl:col-span-8 space-y-6">
             
             {{-- Status Card --}}
             <div class="bg-white dark:bg-slate-900/60 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm p-6 sm:p-8 ring-1 ring-slate-200/50 dark:ring-slate-800/80 relative overflow-hidden">
                 <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
                     <div>
                         <p class="text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-1">Tanggal Pesanan</p>
                         <p class="text-base font-bold text-slate-900 dark:text-slate-100">{{ \Carbon\Carbon::parse($order->created_at)->translatedFormat('d F Y, H:i') }}</p>
                     </div>
                     <div>
                         <span class="inline-flex px-4 py-2 text-sm font-bold rounded-xl border shadow-sm transition-all duration-300"
                               :class="{
                                   {{-- liveStatus holds an order status, never a payment status: 'paid' and 'expired' never reach here. --}}
                                   'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800': liveStatus === 'completed',
                                   'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800': liveStatus === 'cancelled',
                                   'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-800': liveStatus !== 'completed' && liveStatus !== 'cancelled'
                               }"
                               x-text="liveStatusLabel">
                             {{ $order->status_label }}
                         </span>
                     </div>
                 </div>
             </div>

             {{-- Cancelled but already paid: tell the customer their money is coming
                  back and give them a direct line to ask about it. --}}
             @if($order->needsRefund())
                 <div class="bg-amber-50 dark:bg-amber-950/20 rounded-3xl border border-amber-200 dark:border-amber-900/50 shadow-sm p-6 sm:p-8">
                     <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                         <div class="w-11 h-11 rounded-2xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                             <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/></svg>
                         </div>
                         <div class="flex-1 min-w-0">
                             <h3 class="text-base font-extrabold text-amber-900 dark:text-amber-300">Pengembalian Dana</h3>
                             <p class="mt-1.5 text-sm text-amber-800/90 dark:text-amber-400/90 leading-relaxed">
                                 Pesanan ini dibatalkan, sedangkan pembayaran sebesar <span class="font-bold">{{ $order->formatted_total }}</span> sudah kami terima.
                                 Silakan hubungi admin kami untuk memproses pengembalian dananya.
                             </p>
                             <a href="{{ $refundUrl }}" target="_blank" rel="noopener"
                                class="mt-4 inline-flex items-center justify-center gap-2.5 px-5 py-3 bg-emerald-600 text-white text-sm font-bold rounded-xl hover:bg-emerald-700 transition-all active:scale-[0.98] shadow-lg shadow-emerald-600/20">
                                 <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.372-.025-.521-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.71.306 1.263.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                 Chat Admin untuk Refund
                             </a>
                         </div>
                     </div>
                 </div>
             @elseif($order->status === 'cancelled' && $order->refunded_at)
                 {{-- Closes the loop: the shop has recorded the money going back. --}}
                 <div class="bg-emerald-50 dark:bg-emerald-950/20 rounded-3xl border border-emerald-200 dark:border-emerald-900/50 shadow-sm p-6 sm:p-8">
                     <div class="flex items-start gap-4">
                         <div class="w-11 h-11 rounded-2xl bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                             <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                         </div>
                         <div class="flex-1 min-w-0">
                             <h3 class="text-base font-extrabold text-emerald-900 dark:text-emerald-300">Dana Telah Dikembalikan</h3>
                             <p class="mt-1.5 text-sm text-emerald-800/90 dark:text-emerald-400/90 leading-relaxed">
                                 Pengembalian dana sebesar <span class="font-bold">{{ $order->formatted_total }}</span> telah kami proses pada
                                 {{ $order->refunded_at->translatedFormat('d F Y, H:i') }} WIB.
                             </p>
                         </div>
                     </div>
                 </div>
             @endif

             {{-- Visual Stepper Progress Bar Card --}}
             @if($order->status !== 'cancelled')
             <div class="bg-white dark:bg-slate-900/60 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm p-6 sm:p-8 ring-1 ring-slate-200/50 dark:ring-slate-800/80">
                 <h3 class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-6 flex items-center gap-2">
                     <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                     Progres Pengiriman
                 </h3>

                 {{-- Stepper Container --}}
                 <div class="relative flex flex-col md:flex-row justify-between items-start md:items-center gap-6 md:gap-4">
                     {{-- Connecting Line (Desktop) --}}
                     <div class="absolute top-5 left-8 right-8 h-1 bg-slate-100 dark:bg-slate-800 hidden md:block rounded-full overflow-hidden">
                         <div class="h-full bg-emerald-500 transition-all duration-500" 
                              :style="{ width: liveStatus === 'completed' ? '100%' : (liveStatus === 'shipped' ? '66%' : (liveStatus === 'processing' ? '33%' : '0%')) }">
                         </div>
                     </div>

                     {{-- Step 1: Dipesan --}}
                     <div class="flex flex-row md:flex-col items-center gap-4 md:gap-2 relative z-10 w-full md:w-auto md:flex-1">
                         <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 font-bold transition-all duration-300"
                              :class="['processing', 'shipped', 'completed'].includes(liveStatus) 
                                  ? 'bg-emerald-500 border-emerald-500 text-white shadow-lg shadow-emerald-500/20' 
                                  : 'bg-primary-500 border-primary-500 text-white ring-4 ring-primary-500/30 shadow-lg shadow-primary-500/20 animate-pulse-subtle'">
                             <span x-show="!['processing', 'shipped', 'completed'].includes(liveStatus)">1</span>
                             <svg x-show="['processing', 'shipped', 'completed'].includes(liveStatus)" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                         </div>
                         <div class="text-left md:text-center">
                             <p class="text-sm font-extrabold text-slate-900 dark:text-slate-100">Pesanan Dibuat</p>
                             <p class="text-[11px] uppercase mt-0.5 tracking-wider"
                                :class="['processing', 'shipped', 'completed'].includes(liveStatus) 
                                    ? 'text-slate-400 dark:text-slate-500 font-bold' 
                                    : 'text-primary-600 dark:text-primary-400 font-black'"
                                x-text="['processing', 'shipped', 'completed'].includes(liveStatus) ? 'Selesai' : 'Belum Bayar'">Selesai</p>
                         </div>
                     </div>

                     {{-- Vertical Connector Line (Mobile) --}}
                     <div class="w-0.5 h-6 ml-5 -my-4 md:hidden block transition-colors duration-300"
                          :class="['processing', 'shipped', 'completed'].includes(liveStatus) ? 'bg-emerald-500' : 'bg-slate-200 dark:bg-slate-800'"></div>

                     {{-- Step 2: Dibayar / Dikemas --}}
                     <div class="flex flex-row md:flex-col items-center gap-4 md:gap-2 relative z-10 w-full md:w-auto md:flex-1">
                         <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 font-bold transition-all duration-300"
                              :class="['shipped', 'completed'].includes(liveStatus) 
                                  ? 'bg-emerald-500 border-emerald-500 text-white shadow-lg shadow-emerald-500/20' 
                                  : (liveStatus === 'processing'
                                      ? 'bg-primary-500 border-primary-500 text-white ring-4 ring-primary-500/30 shadow-lg shadow-primary-500/20 animate-pulse-subtle'
                                      : 'bg-white dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-400')">
                             <span x-show="!['shipped', 'completed'].includes(liveStatus)">2</span>
                             <svg x-show="['shipped', 'completed'].includes(liveStatus)" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                         </div>
                         <div class="text-left md:text-center">
                             <p class="text-sm font-extrabold text-slate-900 dark:text-slate-100">Sedang Dikemas</p>
                             <p class="text-[11px] uppercase mt-0.5 tracking-wider"
                                :class="['shipped', 'completed'].includes(liveStatus) 
                                    ? 'text-slate-400 dark:text-slate-500 font-bold' 
                                    : (liveStatus === 'processing'
                                        ? 'text-primary-600 dark:text-primary-400 font-black'
                                        : 'text-slate-400 dark:text-slate-600')"
                                x-text="['shipped', 'completed'].includes(liveStatus) ? 'Selesai' : (liveStatus === 'processing' ? 'Diproses' : 'Belum')">Belum</p>
                         </div>
                     </div>

                     {{-- Vertical Connector Line (Mobile) --}}
                     <div class="w-0.5 h-6 ml-5 -my-4 md:hidden block transition-colors duration-300"
                          :class="['shipped', 'completed'].includes(liveStatus) ? 'bg-emerald-500' : 'bg-slate-200 dark:bg-slate-800'"></div>

                     {{-- Step 3: Dikirim --}}
                     <div class="flex flex-row md:flex-col items-center gap-4 md:gap-2 relative z-10 w-full md:w-auto md:flex-1">
                         <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 font-bold transition-all duration-300"
                              :class="liveStatus === 'completed' 
                                  ? 'bg-emerald-500 border-emerald-500 text-white shadow-lg shadow-emerald-500/20' 
                                  : (liveStatus === 'shipped'
                                      ? 'bg-primary-500 border-primary-500 text-white ring-4 ring-primary-500/30 shadow-lg shadow-primary-500/20 animate-pulse-subtle'
                                      : 'bg-white dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-400')">
                             <span x-show="liveStatus !== 'completed'">3</span>
                             <svg x-show="liveStatus === 'completed'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                         </div>
                         <div class="text-left md:text-center">
                             <p class="text-sm font-extrabold text-slate-900 dark:text-slate-100">Dalam Perjalanan</p>
                             <p class="text-[11px] uppercase mt-0.5 tracking-wider"
                                :class="liveStatus === 'completed' 
                                    ? 'text-slate-400 dark:text-slate-500 font-bold' 
                                    : (liveStatus === 'shipped'
                                        ? 'text-primary-600 dark:text-primary-400 font-black'
                                        : 'text-slate-400 dark:text-slate-600')"
                                x-text="liveStatus === 'completed' ? 'Selesai' : (liveStatus === 'shipped' ? 'DikirimKurir' : 'Belum')">Belum</p>
                         </div>
                     </div>

                     {{-- Vertical Connector Line (Mobile) --}}
                     <div class="w-0.5 h-6 ml-5 -my-4 md:hidden block transition-colors duration-300"
                          :class="liveStatus === 'completed' ? 'bg-emerald-500' : 'bg-slate-200 dark:bg-slate-800'"></div>

                     {{-- Step 4: Selesai --}}
                     <div class="flex flex-row md:flex-col items-center gap-4 md:gap-2 relative z-10 w-full md:w-auto md:flex-1">
                         <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 font-bold transition-all duration-300"
                              :class="liveStatus === 'completed' 
                                  ? 'bg-emerald-500 border-emerald-500 text-white shadow-lg shadow-emerald-500/20' 
                                  : 'bg-white dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-400'">
                             <span x-show="liveStatus !== 'completed'">4</span>
                             <svg x-show="liveStatus === 'completed'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                         </div>
                         <div class="text-left md:text-center">
                             <p class="text-sm font-extrabold text-slate-900 dark:text-slate-100">Pesanan Tiba</p>
                             <p class="text-[11px] uppercase mt-0.5 tracking-wider"
                                :class="liveStatus === 'completed' 
                                    ? 'text-emerald-600 dark:text-emerald-400 font-black' 
                                    : 'text-slate-400 dark:text-slate-600'"
                                x-text="liveStatus === 'completed' ? 'Selesai' : 'Belum'">Belum</p>
                         </div>
                     </div>
                 </div>
             </div>
             @endif

             {{-- ETA Banner — dynamic: before the parcel is handed over, if a pickup
                  is not possible right now (the courier is past its cutoff, or the
                  shop is currently shut) it says when it will actually be collected;
                  otherwise it shows the normal delivery estimate. --}}
             @if($order->status !== 'cancelled' && $order->status !== 'completed')
             @php
                 $etaText = '';
                 $courier = strtolower($order->shipping_courier);
                 $service = strtolower($order->shipping_service);

                 if (in_array($courier, ['grab', 'gojek'])) {
                     if (in_array($service, ['instant', 'express'])) {
                         $etaText = 'Estimasi Tiba: ~1 - 2 Jam (Pengiriman Instan)';
                     } elseif (in_array($service, ['same_day', 'sameday'])) {
                         $etaText = 'Estimasi Tiba: Hari ini sebelum pukul 17:00 WIB (Same Day)';
                     } else {
                         $etaText = 'Estimasi Tiba: Hari ini (Layanan Khusus)';
                     }
                 } else {
                     $etaText = 'Estimasi Tiba: 1 - 3 Hari Kerja (Layanan Reguler)';
                 }

                 // Only meaningful before pickup. nextOpening() combines the shop's
                 // hours and the courier's window (e.g. a Same Day cutoff), so it is
                 // null once a pickup is actually possible. Reckon from now(), not
                 // created_at: a pickup can only happen from the present moment on,
                 // and this is exactly what BookBiteshipOrder does when it defers a
                 // booking — so an order placed while a pickup was still possible but
                 // paid after the cutoff (or after close) shows the real collection
                 // time, not the stale one it had at checkout.
                 $pickupAt = in_array($order->status, ['pending', 'processing'])
                     ? \App\Support\CourierSchedule::nextOpening($order->shipping_courier, $order->shipping_service)
                     : null;
             @endphp
             <div class="bg-amber-50/70 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/50 rounded-3xl p-5 flex items-center gap-4">
                 <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                     <svg class="w-5 h-5 {{ $pickupAt ? '' : 'animate-pulse' }}" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                 </div>
                 <div>
                     @if($pickupAt)
                         @php $pastCutoff = \App\Support\CourierSchedule::hasPickupWindow($order->shipping_courier, $order->shipping_service) && ! \App\Support\CourierSchedule::isOpenNow($order->shipping_courier, $order->shipping_service) && \App\Support\StoreSchedule::isOpenNow(); @endphp
                         <p class="text-xs font-black text-amber-800 dark:text-amber-400 uppercase tracking-wider mb-0.5">Menunggu Penjemputan Kurir</p>
                         <p class="text-sm font-bold text-slate-800 dark:text-slate-200 leading-relaxed">
                             @if($pastCutoff)
                                 Sudah melewati batas jemput {{ strtoupper($order->shipping_courier) }} {{ strtoupper(str_replace('_', ' ', $order->shipping_service)) }}.
                             @else
                                 Toko sedang tutup, kurir belum bisa menjemput.
                             @endif
                             Pesanan tetap disiapkan dan dijemput
                             <span class="text-amber-700 dark:text-amber-300">{{ $pickupAt->translatedFormat('l, d M') }} pukul {{ $pickupAt->format('H:i') }} WIB</span>.
                         </p>
                     @else
                         <p class="text-xs font-black text-amber-800 dark:text-amber-400 uppercase tracking-wider mb-0.5">Estimasi Waktu Pengiriman</p>
                         <p class="text-sm font-bold text-slate-800 dark:text-slate-200">
                             {{ $etaText }}
                         </p>
                     @endif
                 </div>
             </div>
             @endif

             {{-- Tracking Number Bar --}}
             @if($order->tracking_number)
                 <div class="rounded-3xl p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-white overflow-hidden relative group shadow-lg"
                      style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                     <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10 mix-blend-overlay"></div>
                     <div class="relative z-10">
                         <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 flex items-center gap-1.5">
                             <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" /></svg>
                             Nomor Resi
                         </p>
                         <h2 class="text-2xl font-mono font-black tracking-wider text-white select-all">{{ $order->tracking_number }}</h2>
                     </div>
                     <div class="relative z-10 flex flex-row sm:flex-col items-center sm:items-end gap-2">
                         <span class="px-3 py-1.5 rounded-lg bg-white/10 backdrop-blur-md border border-white/10 text-xs font-bold uppercase tracking-wider">{{ strtoupper($order->shipping_courier) }}</span>
                         <span class="text-xs font-bold text-slate-400/80 uppercase tracking-widest">{{ strtoupper($order->shipping_service) }}</span>
                     </div>
                 </div>
             @endif

             {{-- Real-time Courier Tracking Section --}}
             <template x-if="trackingData">
                 <div class="bg-white dark:bg-slate-900/60 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden ring-1 ring-slate-200/50 dark:ring-slate-800/80">
                     <div class="p-6 sm:p-8 bg-slate-50/50 dark:bg-slate-950/30 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                         <h3 class="text-xl font-black text-slate-900 dark:text-white flex items-center gap-3">
                             <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                 <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 7c0-1.1-.9-2-2-2h-3v2h3v2.65L13.52 14H10V9H6c-2.21 0-4 1.79-4 4v3h2c0 1.66 1.34 3 3 3s3-1.34 3-3h4.48L19 10.35zM7 17c-.55 0-1-.45-1-1h2c0 .55-.45 1-1 1"/><path d="M5 6h5v2H5z"/><path d="M19 13c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3m0 4c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1"/></svg>
                             </div>
                             Status Pengiriman
                         </h3>
                         <button type="button" @click="fetchTracking()" 
                                 class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800 text-xs font-bold text-slate-500 hover:text-emerald-600 dark:text-slate-400 shadow-sm border border-slate-200 dark:border-slate-700 transition-colors"
                                 :class="loadingTracking ? 'opacity-50 pointer-events-none' : ''">
                             <svg class="w-3.5 h-3.5" :class="{ 'animate-spin': loadingTracking }" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                             <span class="hidden sm:inline">Refresh</span>
                         </button>
                     </div>
                     
                     <div class="p-6 sm:p-8 space-y-8" role="status" aria-live="polite">
                          {{-- Driver Card --}}
                          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 rounded-2xl bg-white dark:bg-slate-900 shadow-xl shadow-slate-200/20 dark:shadow-none border border-slate-100 dark:border-slate-800">
                              {{-- Left: Driver Identity --}}
                              <div class="flex items-center gap-4 flex-1 min-w-0">
                                  <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full border-4 border-slate-50 dark:border-slate-800 shadow-md overflow-hidden bg-slate-100 dark:bg-slate-800 shrink-0 relative">
                                      {{-- Biteship does not always send a photo. Initials, never a stock
                                           portrait: a face from an avatar service is a real person's
                                           likeness presented as this order's driver. --}}
                                      <img x-show="trackingData.courier.photo" :src="trackingData.courier.photo" class="w-full h-full object-cover" alt="Foto Kurir">
                                      <div x-show="!trackingData.courier.photo"
                                           class="w-full h-full flex items-center justify-center bg-linear-to-br from-primary-400 to-primary-600 text-white text-lg sm:text-xl font-extrabold"
                                           x-text="(trackingData.courier.name || '?').trim().charAt(0).toUpperCase()"></div>
                                      <div class="absolute inset-0 rounded-full ring-1 ring-inset ring-slate-900/10"></div>
                                  </div>
                                  <div class="flex-1 min-w-0">
                                      <div class="flex flex-wrap items-center gap-2 mb-1">
                                          <p class="text-base font-extrabold text-slate-900 dark:text-slate-100 truncate" x-text="trackingData.courier.name"></p>
                                          <span class="px-2.5 py-0.5 rounded-md bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 text-xs font-bold uppercase tracking-widest border border-emerald-200 dark:border-emerald-800/50 shadow-sm" x-text="trackingData.courier.plate_number"></span>
                                      </div>
                                      <div class="flex items-center gap-1.5 mt-1.5">
                                          <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                          <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest" x-text="trackingData.status_label"></p>
                                      </div>
                                  </div>
                              </div>
                              
                              {{-- Right: Live Map Button --}}
                              <div class="w-full sm:w-auto pt-3 sm:pt-0 border-t sm:border-t-0 sm:border-l border-slate-100 dark:border-slate-800/80 sm:pl-4 flex shrink-0">
                                  <a :href="trackingData.link" target="_blank" 
                                     class="relative group overflow-hidden w-full sm:w-auto flex sm:flex-col items-center justify-center gap-2 sm:gap-1 p-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 shadow-sm border border-emerald-100 dark:border-emerald-800/50 hover:bg-emerald-600 hover:text-white dark:hover:bg-emerald-600 transition-colors"
                                     title="Lacak Live">
                                      <svg class="w-6 h-6 sm:w-7 sm:h-7 relative z-10 transition-transform group-hover:scale-110" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                          <path d="M7 10V21C7 23.2091 8.79086 25 11 25H22" stroke="#8b31ff" stroke-width="5" stroke-linecap="round"/>
                                          <path d="M10 7H21C23.2091 7 25 8.79086 25 11V22" stroke="#00c0a5" stroke-width="5" stroke-linecap="round"/>
                                      </svg>
                                      <span class="text-xs font-bold uppercase relative z-10 tracking-widest">Lacak di Biteship</span>
                                  </a>
                              </div>
                          </div>
 
                         {{-- Timeline --}}
                         <div class="relative border-l-2 border-slate-200 dark:border-slate-700 ml-4 sm:ml-8 pl-5 sm:pl-8 space-y-8 py-2">
                             <template x-for="(event, index) in trackingData.history" :key="index + '-' + event.status">
                                 <div class="relative group">
                                     {{-- Dot --}}
                                     <div class="absolute -left-[31px] sm:-left-[41px] top-0.5 w-5 h-5 rounded-full border-4 border-white dark:border-slate-900 bg-emerald-500 shadow-sm z-10 transition-transform group-hover:scale-125"></div>
                                     
                                     <div class="flex flex-col bg-white dark:bg-slate-900 p-4 rounded-xl shadow-sm border border-slate-100 dark:border-slate-800 -translate-y-3">
                                         <span class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wide leading-relaxed mb-1" x-text="event.note"></span>
                                         <div class="flex items-center gap-1.5 text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                                             <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                             <span x-text="event.time"></span>
                                         </div>
                                     </div>
                                 </div>
                             </template>
                         </div>
                     </div>
                 </div>
             </template>

             {{-- Products List --}}
             <div class="bg-white dark:bg-slate-900/60 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm ring-1 ring-slate-200/50 dark:ring-slate-800/80 overflow-hidden">
                 <div class="p-6 sm:p-8">
                     <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100 dark:border-slate-800/60">
                         <h3 class="text-lg lg:text-xl font-black text-slate-900 dark:text-white flex items-center gap-2.5">
                             <div class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 flex items-center justify-center">
                                 <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                             </div>
                             Daftar Produk
                         </h3>
                         <span class="inline-flex items-center px-3 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-bold text-slate-600 dark:text-slate-400 shadow-2xs border border-slate-200/40 dark:border-slate-700/50">
                             {{ count($order->items) }} Items
                         </span>
                     </div>
                     
                     <div class="space-y-4">
                         @foreach($order->items as $item)
                             <div class="p-4 sm:p-5 rounded-2xl border border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-950/20 transition-all duration-300 hover:bg-slate-50 dark:hover:bg-slate-900/40 hover:shadow-md hover:shadow-slate-100/10 dark:hover:shadow-none hover:-translate-y-0.5 group">
                                 <div class="flex items-start gap-4 sm:gap-5">
                                     {{-- Product Image Container --}}
                                     <div class="w-16 h-16 sm:w-24 sm:h-24 rounded-2xl overflow-hidden bg-white dark:bg-slate-900 shrink-0 shadow-xs relative border border-slate-200/60 dark:border-slate-800/60 group-hover:border-primary-500/30 transition-colors">
                                         @if($item->product && $item->product->image)
                                             <img src="{{ asset('storage/' . $item->product->image) }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" alt="{{ $item->product_name }}">
                                         @else
                                             <div class="w-full h-full flex items-center justify-center text-slate-400 bg-slate-100 dark:bg-slate-900">
                                                 <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                                             </div>
                                         @endif
                                         {{-- Quantity overlay badge with elegant glass style --}}
                                         <div class="absolute bottom-1 right-1 px-1.5 py-0.5 bg-slate-900/75 dark:bg-slate-950/80 backdrop-blur-xs text-white text-[9px] sm:text-[10px] font-black rounded-lg border border-white/15 dark:border-slate-800 shadow-sm transition-transform group-hover:scale-105">
                                             x{{ $item->quantity }}
                                         </div>
                                     </div>
                                     
                                     {{-- Product Info & Subtotal (responsive wrap) --}}
                                     <div class="flex-1 min-w-0 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                         <div>
                                             <h4 class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-slate-100 truncate group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                                 @if($item->product)
                                                     <a href="{{ route('products.show', $item->product) }}" class="hover:underline decoration-2 underline-offset-4">{{ $item->product_name }}</a>
                                                 @else
                                                     {{ $item->product_name }}
                                                 @endif
                                             </h4>
                                             <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 mt-1 sm:mt-1.5 tracking-wide flex items-center gap-1.5">
                                                 <span>Rp {{ number_format($item->product_price, 0, ',', '.') }}</span>
                                                 <span class="text-slate-300 dark:text-slate-700 select-none">|</span>
                                                 <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[10px] font-bold text-slate-500 dark:text-slate-400">Qty: {{ $item->quantity }}</span>
                                             </p>
                                         </div>
                                         
                                         {{-- Subtotal Price --}}
                                         <div class="text-xs sm:text-base font-black text-slate-900 dark:text-white shrink-0 self-start sm:self-center bg-white dark:bg-slate-900/60 px-3 py-1 sm:px-3.5 sm:py-1.5 rounded-xl border border-slate-100 dark:border-slate-800/80 group-hover:border-primary-500/20 group-hover:bg-primary-50/10 dark:group-hover:bg-primary-950/10 transition-all duration-300 shadow-2xs">
                                             Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                         </div>
                                     </div>
                                 </div>
 
                                 {{-- Review Block --}}
                                 @if($order->status === 'completed' && $item->product_id)
                                     <div class="mt-4 pt-4 border-t border-slate-200/60 dark:border-slate-800/80">
                                         @livewire('submit-review', ['orderId' => $order->id, 'productId' => $item->product_id], 'review-'.$item->id)
                                     </div>
                                 @endif
                             </div>
                         @endforeach
                     </div>

                    @if($order->notes)
                        <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-800">
                            <h3 class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                Catatan Pesanan
                            </h3>
                            <div class="p-5 rounded-2xl bg-amber-50/50 dark:bg-amber-900/10 border border-amber-100/50 dark:border-amber-800/20 text-slate-700 dark:text-slate-300">
                                <p class="text-sm italic tracking-wide">"{{ $order->notes }}"</p>
                            </div>
                        </div>
                    @endif

                    {{-- The customer's half of the admin modal's "Catatan Sistem": the same
                         events, restated so they carry no courier codes or internal to-dos. --}}
                    @php($systemNotes = \App\Support\CustomerOrderNote::for($order))
                    @if(! empty($systemNotes))
                        <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-800">
                            <h3 class="text-xs font-black text-amber-500 dark:text-amber-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                                Informasi Pesanan
                            </h3>
                            <div class="p-5 rounded-2xl bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-900/40 space-y-2.5">
                                @foreach($systemNotes as $note)
                                    <div class="flex items-start gap-2.5">
                                        <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-amber-400 dark:bg-amber-500 shrink-0"></span>
                                        <p class="text-sm font-medium text-amber-800 dark:text-amber-300 leading-relaxed">{{ $note }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right Column: Sidebar --}}
        <div class="lg:col-span-5 xl:col-span-4 space-y-6">
            
            {{-- Address Summary --}}
            @if($order->address)
            <div class="bg-white dark:bg-slate-900/60 rounded-3xl border border-slate-100 dark:border-slate-800/80 shadow-sm p-6 sm:p-8 ring-1 ring-slate-200/50 dark:ring-slate-800/80">
                <h3 class="text-xs font-black text-slate-500 dark:text-slate-500 uppercase tracking-widest mb-5 flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                    Alamat Pengiriman
                </h3>
                <div class="p-5 rounded-2xl bg-slate-50/50 dark:bg-slate-950/30 border border-slate-100 dark:border-slate-800/60 relative overflow-hidden">
                    <div class="absolute top-4 right-4 px-2 py-0.5 rounded bg-primary-100 dark:bg-primary-950/50 text-primary-700 dark:text-primary-400 text-[10px] font-bold uppercase tracking-wider border border-primary-200/30 dark:border-primary-900/50 shadow-2xs">
                        {{ $order->address->label }}
                    </div>

                    <p class="text-base font-extrabold text-slate-900 dark:text-slate-100">{{ $order->address->recipient_name }}</p>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mt-2 leading-relaxed select-all">{{ $order->address->full_address }}</p>
                    
                    <div class="mt-4 pt-4 border-t border-slate-200/60 dark:border-slate-800 flex items-center justify-between"
                         x-data="{ copied: false, phone: '{{ $order->user->phone ?? $order->address->phone }}' }">
                        <a :href="'tel:' + phone" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-300 border border-slate-200/60 dark:border-slate-800 transition-colors shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-2.824-1.443-5.15-3.768-6.593-6.593l1.293-.97c.362-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>
                            <span x-text="copied ? 'Tersalin!' : phone" :class="copied ? 'text-emerald-600 dark:text-emerald-400' : ''"></span>
                        </a>
                        <button @click="navigator.clipboard.writeText(phone); copied = true; setTimeout(() => copied = false, 2000)" 
                                class="text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-all active:scale-95" 
                                title="Salin nomor">
                            <svg x-show="!copied" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                            </svg>
                            <svg x-show="copied" class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            @endif

            {{-- Which courier/service the customer picked at checkout. Shown from
                 the moment the order exists, not only once a tracking number
                 arrives (the resi bar above only appears after pickup). --}}
            @if($order->shipping_courier)
            <div class="bg-white dark:bg-slate-900/60 rounded-3xl border border-slate-100 dark:border-slate-800/80 shadow-sm p-6 sm:p-8 ring-1 ring-slate-200/50 dark:ring-slate-800/80">
                <h3 class="text-xs font-black text-slate-500 dark:text-slate-500 uppercase tracking-widest mb-5 flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary-500" fill="currentColor" viewBox="0 0 24 24"><path d="M19 7c0-1.1-.9-2-2-2h-3v2h3v2.65L13.52 14H10V9H6c-2.21 0-4 1.79-4 4v3h2c0 1.66 1.34 3 3 3s3-1.34 3-3h4.48L19 10.35zM7 17c-.55 0-1-.45-1-1h2c0 .55-.45 1-1 1"/><path d="M5 6h5v2H5z"/><path d="M19 13c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3m0 4c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1"/></svg>
                    Metode Pengiriman
                </h3>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-primary-50 dark:bg-primary-950/40 text-primary-700 dark:text-primary-300 text-xs font-black uppercase tracking-wider border border-primary-200/40 dark:border-primary-900/50 shadow-2xs">
                        {{ strtoupper($order->shipping_courier) }}
                    </span>
                    @if($order->shipping_service)
                    <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 border border-slate-200/50 dark:border-slate-700/50 shadow-2xs">
                        {{ strtoupper(str_replace('_', ' ', $order->shipping_service)) }}
                    </span>
                    @endif
                </div>
            </div>
            @endif

            {{-- Ringkasan Biaya --}}
            <div class="bg-white dark:bg-slate-900/60 rounded-3xl border border-slate-100 dark:border-slate-800/80 shadow-sm p-6 sm:p-8 ring-1 ring-slate-200/50 dark:ring-slate-800/80 sticky top-28 xl:top-32">
                <h3 class="text-xs font-black text-slate-500 dark:text-slate-500 uppercase tracking-widest mb-5 flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                    Ringkasan Biaya
                </h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center text-sm">
                        <span class="font-medium text-slate-500 dark:text-slate-400">Total Harga Produk</span>
                        <span class="font-bold text-slate-700 dark:text-slate-200">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="font-medium text-slate-500 dark:text-slate-400">Ongkos Kirim</span>
                        <span class="font-bold text-slate-700 dark:text-slate-200">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                    </div>
                    @if($order->discount_amount > 0)
                        <div class="flex justify-between items-center text-sm text-emerald-600 dark:text-emerald-400">
                            <span class="font-medium">Potongan Diskon</span>
                            <span class="font-bold">-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between items-center border-t border-dashed border-slate-200 dark:border-slate-800 pt-5 mt-2">
                        <span class="text-sm font-extrabold text-slate-900 dark:text-slate-200">Total Belanja</span>
                        <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">{{ $order->formatted_total }}</span>
                    </div>
                </div>

                @if(in_array($order->payment_status, ['unpaid', 'pending']))
                    <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-800">
                        <p class="text-xs text-amber-600 dark:text-amber-500 font-bold mb-3 text-center">Menunggu Pembayaran</p>
                        <a href="{{ route('orders.payment', $order) }}" class="group relative w-full flex items-center justify-center gap-2 py-4 bg-primary-600 text-white font-bold rounded-2xl shadow-xl shadow-primary-500/30 dark:shadow-none hover:bg-primary-700 transition-all transform hover:-translate-y-0.5 active:scale-95 overflow-hidden">
                            <span class="relative z-10">Bayar Tagihan Sekarang</span>
                            <div class="absolute inset-0 h-full w-full bg-linear-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:animate-[shimmer_1.5s_infinite]"></div>
                        </a>
                    </div>
                @endif

                @if($order->status === 'shipped')
                    <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-800" x-data="{ confirming: false }">
                        <template x-if="!confirming">
                            <button @click="confirming = true" 
                                    class="relative group w-full flex items-center justify-center gap-2 py-4 bg-emerald-600 text-white font-bold rounded-2xl hover:bg-emerald-700 transition-all shadow-xl shadow-emerald-500/30 dark:shadow-none animate-pulse-subtle overflow-hidden">
                                <span class="relative z-10">Pesanan Diterima</span>
                                <svg class="w-5 h-5 relative z-10" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                <div class="absolute inset-0 h-full w-full bg-linear-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:animate-[shimmer_2s_infinite]"></div>
                            </button>
                        </template>
                        <div x-show="confirming" x-cloak class="p-5 rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 shadow-inner">
                            <p class="text-xs font-extrabold text-amber-700 dark:text-amber-500 text-center uppercase tracking-wider mb-4 leading-relaxed">Konfirmasi Paket<br><span class="text-[10px] font-bold text-amber-600/80 dark:text-amber-400/80 normal-case tracking-normal">Pastikan barang diterima dengan kondisi baik.</span></p>
                            <div class="flex gap-3">
                                <button @click="confirming = false" class="flex-1 py-3 text-xs font-bold text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-800 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 transition-all active:scale-95 shadow-sm">Batal</button>
                                <form action="{{ route('orders.complete', $order) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full py-3 text-xs font-bold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 border border-emerald-700 transition-all active:scale-95 shadow-sm shadow-emerald-600/30">Ya, Selesai!</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            
        </div>
    </div>
</div>

{{-- Success Lottie Modal --}}
@push('scripts')
@if(session('success'))
<script src="{{ asset('js/lottie-player.js') }}" defer></script>
<div x-data="{ showSuccess: true }"
     x-show="showSuccess"
     class="fixed inset-0 z-100 flex items-center justify-center p-4"
     x-init="setTimeout(() => showSuccess = false, 5000)">
    <div x-show="showSuccess"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-500"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"
         style="display:none;"
         @click="showSuccess = false"></div>
    
    <div x-show="showSuccess"
         x-transition:enter="transition cubic-bezier(0.34, 1.56, 0.64, 1) duration-500"
         x-transition:enter-start="opacity-0 scale-75 translate-y-8"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="relative bg-white dark:bg-slate-900 rounded-[2.5rem] p-10 shadow-3xl max-w-sm w-full text-center border border-slate-100 dark:border-slate-800" style="display:none;">
        
        <div class="mb-6 h-40 flex items-center justify-center">
            <lottie-player 
                src="https://lottie.host/80703f8a-c21e-451e-9133-31766629910e/h3C1V6bLh7.json" 
                background="transparent" 
                speed="1" 
                style="width: 200px; height: 200px" 
                autoplay>
            </lottie-player>
        </div>
        
        <h2 class="text-2xl font-black text-slate-900 dark:text-slate-100 mb-2">Berhasil!</h2>
        <p class="text-slate-500 dark:text-slate-400 text-sm font-medium leading-relaxed mb-8">
            {{ session('success') }}
        </p>
        
        <button @click="showSuccess = false" 
                class="w-full py-4 bg-primary-600 text-white font-bold rounded-2xl hover:bg-primary-700 shadow-xl shadow-primary-500/30 dark:shadow-none transition-all transform active:scale-95">
            Lanjutkan
        </button>
    </div>
</div>
@endif
@endpush
@endsection
