@extends('layouts.app')
@section('title', 'Detail Pesanan')
@section('content')
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
                 if (!'{{ $order->tracking_number }}') return;
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
                                   'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800': liveStatus === 'completed',
                                   'bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-400 border-teal-200 dark:border-teal-800': liveStatus === 'paid',
                                   'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800': liveStatus === 'cancelled' || liveStatus === 'expired',
                                   'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-800': liveStatus !== 'completed' && liveStatus !== 'paid' && liveStatus !== 'cancelled' && liveStatus !== 'expired'
                               }"
                               x-text="liveStatusLabel">
                             {{ $order->status_label }}
                         </span>
                     </div>
                 </div>
             </div>

             {{-- Visual Stepper Progress Bar Card --}}
             @if($order->status !== 'cancelled' && $order->status !== 'expired')
             <div class="bg-white dark:bg-slate-900/60 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm p-6 sm:p-8 ring-1 ring-slate-200/50 dark:ring-slate-800/80">
                 <h3 class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-6 flex items-center gap-2">
                     <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                     Progres Pengiriman
                 </h3>

                 {{-- Stepper Container --}}
                 <div class="relative flex flex-col md:flex-row justify-between items-start md:items-center gap-6 md:gap-4">
                     {{-- Connecting Line (Desktop) --}}
                     <div class="absolute top-5 left-8 right-8 h-1 bg-slate-100 dark:bg-slate-800 hidden md:block rounded-full overflow-hidden">
                         <div class="h-full bg-primary-500 transition-all duration-500" 
                              :style="{ width: liveStatus === 'completed' ? '100%' : (liveStatus === 'shipped' ? '66%' : (['paid', 'processing'].includes(liveStatus) ? '33%' : '0%')) }">
                         </div>
                     </div>

                     {{-- Step 1: Dipesan --}}
                     <div class="flex flex-row md:flex-col items-center gap-4 md:gap-2 relative z-10 w-full md:w-auto md:flex-1">
                         <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 font-bold transition-all duration-300"
                              :class="['pending', 'awaiting_payment', 'paid', 'processing', 'shipped', 'completed'].includes(liveStatus) 
                                  ? 'bg-primary-500 border-primary-500 text-white shadow-lg shadow-primary-500/20' 
                                  : 'bg-white dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-400'">
                             <span x-show="!['paid', 'processing', 'shipped', 'completed'].includes(liveStatus)">1</span>
                             <svg x-show="['paid', 'processing', 'shipped', 'completed'].includes(liveStatus)" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                         </div>
                         <div class="text-left md:text-center">
                             <p class="text-sm font-extrabold text-slate-900 dark:text-slate-100">Pesanan Dibuat</p>
                             <p class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase mt-0.5 tracking-wider">Selesai</p>
                         </div>
                     </div>

                     {{-- Vertical Connector Line (Mobile) --}}
                     <div class="w-0.5 h-6 ml-5 -my-4 md:hidden block transition-colors duration-300"
                          :class="['paid', 'processing', 'shipped', 'completed'].includes(liveStatus) ? 'bg-primary-500' : 'bg-slate-200 dark:bg-slate-800'"></div>

                     {{-- Step 2: Dibayar / Dikemas --}}
                     <div class="flex flex-row md:flex-col items-center gap-4 md:gap-2 relative z-10 w-full md:w-auto md:flex-1">
                         <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 font-bold transition-all duration-300"
                              :class="['paid', 'processing', 'shipped', 'completed'].includes(liveStatus) 
                                  ? 'bg-primary-500 border-primary-500 text-white shadow-lg shadow-primary-500/20' 
                                  : 'bg-white dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-400'">
                             <span x-show="!['shipped', 'completed'].includes(liveStatus)">2</span>
                             <svg x-show="['shipped', 'completed'].includes(liveStatus)" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                         </div>
                         <div class="text-left md:text-center">
                             <p class="text-sm font-extrabold text-slate-900 dark:text-slate-100">Sedang Dikemas</p>
                             <p class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase mt-0.5 tracking-wider"
                                x-text="['paid', 'processing', 'shipped', 'completed'].includes(liveStatus) ? 'Selesai' : 'Menunggu'">Menunggu</p>
                         </div>
                     </div>

                     {{-- Vertical Connector Line (Mobile) --}}
                     <div class="w-0.5 h-6 ml-5 -my-4 md:hidden block transition-colors duration-300"
                          :class="['shipped', 'completed'].includes(liveStatus) ? 'bg-primary-500' : 'bg-slate-200 dark:bg-slate-800'"></div>

                     {{-- Step 3: Dikirim --}}
                     <div class="flex flex-row md:flex-col items-center gap-4 md:gap-2 relative z-10 w-full md:w-auto md:flex-1">
                         <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 font-bold transition-all duration-300"
                              :class="['shipped', 'completed'].includes(liveStatus) 
                                  ? 'bg-primary-500 border-primary-500 text-white shadow-lg shadow-primary-500/20' 
                                  : 'bg-white dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-400'">
                             <span x-show="!['completed'].includes(liveStatus)">3</span>
                             <svg x-show="['completed'].includes(liveStatus)" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                         </div>
                         <div class="text-left md:text-center">
                             <p class="text-sm font-extrabold text-slate-900 dark:text-slate-100">Dalam Perjalanan</p>
                             <p class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase mt-0.5 tracking-wider"
                                x-text="['shipped', 'completed'].includes(liveStatus) ? 'Selesai' : 'Belum'">Belum</p>
                         </div>
                     </div>

                     {{-- Vertical Connector Line (Mobile) --}}
                     <div class="w-0.5 h-6 ml-5 -my-4 md:hidden block transition-colors duration-300"
                          :class="['completed'].includes(liveStatus) ? 'bg-primary-500' : 'bg-slate-200 dark:bg-slate-800'"></div>

                     {{-- Step 4: Selesai --}}
                     <div class="flex flex-row md:flex-col items-center gap-4 md:gap-2 relative z-10 w-full md:w-auto md:flex-1">
                         <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 font-bold transition-all duration-300"
                              :class="['completed'].includes(liveStatus) 
                                  ? 'bg-emerald-500 border-emerald-500 text-white shadow-lg shadow-emerald-500/20' 
                                  : 'bg-white dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-400'">
                             <span>4</span>
                         </div>
                         <div class="text-left md:text-center">
                             <p class="text-sm font-extrabold text-slate-900 dark:text-slate-100">Pesanan Tiba</p>
                             <p class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase mt-0.5 tracking-wider"
                                x-text="liveStatus === 'completed' ? 'Selesai' : 'Belum'">Belum</p>
                         </div>
                     </div>
                 </div>
             </div>
             @endif

             {{-- ETA Banner --}}
             @if($order->status !== 'cancelled' && $order->status !== 'expired' && $order->status !== 'completed')
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
             @endphp
             <div class="bg-amber-50/70 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/50 rounded-3xl p-5 flex items-center gap-4">
                 <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                     <svg class="w-5 h-5 animate-pulse" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                 </div>
                 <div>
                     <p class="text-xs font-black text-amber-800 dark:text-amber-400 uppercase tracking-wider mb-0.5">Estimasi Waktu Pengiriman</p>
                     <p class="text-sm font-bold text-slate-800 dark:text-slate-200">
                         {{ $etaText }}
                     </p>
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
                                 <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><path stroke-linecap="round" stroke-linejoin="round" d="M7 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM17 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/></svg>
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
                                      <img :src="trackingData.courier.photo" class="w-full h-full object-cover" alt="Foto Kurir">
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
                    <h3 class="text-xl font-black text-slate-900 dark:text-white mb-6">Daftar Produk</h3>
                    <div class="space-y-4">
                        @foreach($order->items as $item)
                            <div class="p-4 sm:p-5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/30 transition-all hover:bg-slate-50 dark:hover:bg-slate-900/50 group">
                                <div class="flex items-start sm:items-center gap-5">
                                    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-900 shrink-0 shadow-sm relative border border-slate-200/50 dark:border-slate-800">
                                        @if($item->product && $item->product->image)
                                            <img src="{{ asset('storage/' . $item->product->image) }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-slate-300 dark:text-slate-700">
                                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                                            </div>
                                        @endif
                                        <div class="absolute bottom-1.5 right-1.5 px-2 py-0.5 bg-slate-900/80 backdrop-blur-sm text-white text-[10px] font-bold rounded-lg border border-white/10 shadow-sm">
                                            {{ $item->quantity }}x
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-base font-bold text-slate-900 dark:text-slate-100 truncate group-hover:text-primary-600 transition-colors">
                                            @if($item->product)
                                                <a href="{{ route('products.show', $item->product) }}">{{ $item->product_name }}</a>
                                            @else
                                                {{ $item->product_name }}
                                            @endif
                                        </h4>
                                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mt-1">Rp {{ number_format($item->product_price, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="text-base font-black text-slate-900 dark:text-white shrink-0 self-start sm:self-center">
                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                    </div>
                                </div>

                                {{-- Review Block --}}
                                @if($order->status === 'completed' && $item->product_id)
                                    <div class="mt-4 pt-4 border-t border-slate-200/60 dark:border-slate-800">
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
                </div>
            </div>
        </div>

        {{-- Right Column: Sidebar --}}
        <div class="lg:col-span-5 xl:col-span-4 space-y-6">
            
            {{-- Address Summary --}}
            @if($order->address)
            <div class="bg-white dark:bg-slate-900/60 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm p-6 sm:p-8 ring-1 ring-slate-200/50 dark:ring-slate-800/80">
                <h3 class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-5 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                    Dikirim Kepada
                </h3>
                <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-950/50 border border-slate-100 dark:border-slate-800">
                    <p class="text-base font-extrabold text-slate-900 dark:text-slate-100">{{ $order->address->recipient_name }}</p>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">{{ $order->address->full_address }}</p>
                    
                    <div class="mt-4 pt-4 border-t border-slate-200/60 dark:border-slate-700" x-data="{ userPhone: '{{ $order->user->phone ?? $order->address->phone }}' }">
                        <p class="text-xs font-bold text-slate-500 dark:text-slate-400 select-all" x-text="userPhone"></p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Ringkasan Biaya --}}
            <div class="bg-white dark:bg-slate-900/60 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm p-6 sm:p-8 ring-1 ring-slate-200/50 dark:ring-slate-800/80 sticky top-28 xl:top-32">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-6">Ringkasan Biaya</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center text-sm">
                        <span class="font-medium text-slate-500 dark:text-slate-400">Total Harga Produk</span>
                        <span class="font-bold text-slate-700 dark:text-slate-300">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="font-medium text-slate-500 dark:text-slate-400">Ongkos Kirim</span>
                        <span class="font-bold text-slate-700 dark:text-slate-300">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center border-t border-dashed border-slate-200 dark:border-slate-700 pt-5 mt-2">
                        <span class="text-base font-bold text-slate-900 dark:text-white">Total Belanja</span>
                        <span class="text-2xl font-black text-primary-600 dark:text-primary-400 tracking-tight">{{ $order->formatted_total }}</span>
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
