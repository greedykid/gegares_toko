<div>
    <div class="flex items-center gap-4 mb-6">
        <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-500 shrink-0">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 7c0-1.1-.9-2-2-2h-3v2h3v2.65L13.52 14H10V9H6c-2.21 0-4 1.79-4 4v3h2c0 1.66 1.34 3 3 3s3-1.34 3-3h4.48L19 10.35zM7 17c-.55 0-1-.45-1-1h2c0 .55-.45 1-1 1"/>
                <path d="M5 6h5v2H5z"/>
                <path d="M19 13c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3m0 4c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1"/>
            </svg>
        </div>
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Metode Pengiriman</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Pilih layanan ekspedisi pengiriman yang Anda inginkan.</p>
        </div>
    </div>
    
    @if(!$hasAddress)
        <div class="p-8 text-center border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-2xl bg-slate-50/50 dark:bg-slate-950/20">
            <svg class="w-10 h-10 text-slate-350 dark:text-slate-650 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
            </svg>
            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Pilih alamat pengiriman terlebih dahulu untuk melihat opsi kurir.</p>
        </div>
    @else
        <div class="space-y-3 relative">
            {{-- Loading State Overlay --}}
            <div wire:loading wire:target="handleAddressSelected, handleGuestAddressUpdated, fetchRates" class="absolute inset-0 bg-white/70 dark:bg-slate-950/70 backdrop-blur-[2px] z-10 flex flex-col items-center justify-center rounded-2xl border border-slate-100/50 dark:border-slate-800/50">
                <div class="flex items-center gap-3 bg-white dark:bg-slate-900 px-5 py-3 rounded-2xl shadow-lg border border-slate-150 dark:border-slate-800">
                    <svg class="animate-spin h-5 w-5 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-sm font-bold text-slate-700 dark:text-slate-300 animate-pulse">Menghitung ongkir...</p>
                </div>
            </div>

            @if(!$hasValidArea)
                <div wire:loading.remove wire:target="handleAddressSelected, handleGuestAddressUpdated, fetchRates" class="p-6 border border-red-200 dark:border-red-900/50 rounded-2xl bg-red-50 dark:bg-red-900/10">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                        <div>
                            <p class="text-sm text-red-700 dark:text-red-400 font-bold">Alamat Menggunakan Format Lama</p>
                            <p class="text-xs text-red-600 dark:text-red-500/80 mt-1">Silakan klik tombol Edit (<svg class="w-3.5 h-3.5 inline pb-0.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>) pada alamat pilihan Anda di atas dan pilih ulang Area / Kecamatan Anda agar terhubung ke peta kurir.</p>
                        </div>
                    </div>
                </div>
            @elseif(count($rates) === 0)
                <div wire:loading.remove wire:target="handleAddressSelected, handleGuestAddressUpdated, fetchRates" class="p-6 text-center border border-slate-150 dark:border-slate-800 rounded-2xl bg-slate-50 dark:bg-slate-950/20">
                    <svg class="w-8 h-8 text-slate-400 dark:text-slate-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0 1 18 0Z"/></svg>
                    <p class="text-sm text-slate-600 dark:text-slate-400 font-bold">Tidak ada kurir pengiriman yang tersedia untuk lokasi ini.</p>
                </div>
            @else
                <div class="grid grid-cols-1 gap-2.5">
                    @foreach($rates as $rate)
                        @php
                            $rateValue = $rate['courier_code'] . '|' . $rate['courier_service_code'] . '|' . $rate['price'];
                            $isSelected = $selectedRate === $rateValue;
                        @endphp
                        <label wire:key="{{ $rateValue }}" 
                               class="relative flex items-center py-2.5 px-4 sm:py-3.5 sm:px-5 rounded-2xl border transition-all duration-300 cursor-pointer group hover:shadow-xs {{ $isSelected ? 'border-primary-500 bg-primary-50/50 dark:bg-primary-900/20 border-2' : 'border-slate-100 dark:border-slate-800/80 hover:border-slate-200 dark:hover:border-slate-700 bg-white dark:bg-slate-950/50' }}">
                            <input type="radio" 
                                   name="shipping_service_selection" 
                                   value="{{ $rateValue }}" 
                                   wire:model.live="selectedRate"
                                   x-on:change="shippingCost = {{ $rate['price'] }}; shippingInfo = '{{ $rate['courier_name'] }} {{ $rate['courier_service_name'] }}'"
                                   class="hidden">
                            
                            <div class="flex items-center gap-3.5 w-full">
                                <!-- Custom Radio Indicator -->
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 transition-all duration-300 {{ $isSelected ? 'border-primary-500 bg-primary-500' : 'border-slate-300 dark:border-slate-600 group-hover:border-slate-400' }}">
                                    @if($isSelected)
                                        <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                        </svg>
                                    @endif
                                </div>
                                
                                <!-- Courier Details (single horizontal row) -->
                                <div class="flex-1 min-w-0 flex items-center justify-between gap-4">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                            <span class="text-sm font-extrabold text-slate-900 dark:text-slate-100 uppercase tracking-wider">
                                                {{ $rate['courier_name'] }}
                                            </span>
                                            <span class="px-1.5 py-0.5 text-[9px] font-extrabold rounded-md uppercase tracking-wider bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                                {{ $rate['courier_service_name'] }}
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-450 dark:text-slate-500 mt-1 flex items-center gap-1.5 font-medium">
                                            <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-550 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                            </svg>
                                            Estimasi: <span class="font-bold text-slate-500 dark:text-slate-400">{{ $rate['shipment_duration_range'] ?? '?' }} {{ $rate['shipment_duration_unit'] ?? 'jam' }}</span>
                                        </p>

                                        {{-- A parcel needs the shop staffed AND the courier running.
                                             Whichever is shut, saying so here — before the choice is
                                             made — beats letting the customer find out after paying. --}}
                                        @php
                                            $opensAt = \App\Support\CourierSchedule::nextOpening(
                                                $rate['courier_code'] ?? null,
                                                $rate['courier_service_code'] ?? null
                                            );
                                            $storeShut = ! \App\Support\StoreSchedule::isOpenNow();
                                            // The shop is open but this service still can't be collected:
                                            // that only happens for a Same Day courier whose daily cutoff
                                            // has passed, so it earns its own, clearer message.
                                            $sameDayCutoff = ! $storeShut && \App\Support\CourierSchedule::hasPickupWindow(
                                                $rate['courier_code'] ?? null,
                                                $rate['courier_service_code'] ?? null
                                            );
                                            $notice = $storeShut
                                                ? 'Toko sedang tutup.'
                                                : ($sameDayCutoff ? 'Batas jemput layanan Same Day hari ini sudah lewat.' : 'Di luar jam jemput kurir.');
                                        @endphp
                                        @if($opensAt)
                                            <p class="text-[11px] text-amber-600 dark:text-amber-400 mt-1 flex items-start gap-1.5 font-semibold">
                                                <svg class="w-3.5 h-3.5 shrink-0 mt-px" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                                                </svg>
                                                <span>
                                                    {{ $notice }}
                                                    Pesanan tetap kami terima, dijemput {{ $opensAt->translatedFormat('l, d M') }} mulai {{ $opensAt->format('H:i') }} WIB.@if($sameDayCutoff) Untuk pengiriman hari ini, pilih layanan <span class="font-bold">Instant</span>.@endif
                                                </span>
                                            </p>
                                        @endif
                                    </div>
                                    <div class="shrink-0">
                                        <span class="text-sm sm:text-base font-black text-slate-900 dark:text-white bg-slate-50 dark:bg-slate-950/40 px-3 py-1 sm:px-3.5 sm:py-1.5 rounded-xl border border-slate-100 dark:border-slate-800/80 group-hover:border-primary-500/20 group-hover:bg-primary-50/10 dark:group-hover:bg-primary-950/10 transition-all duration-300 shadow-2xs">
                                            Rp {{ number_format($rate['price'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
            @endif
        </div>
        
        {{-- Hidden fields for form submission --}}
        @if($selectedRate)
            @php
                $parts = explode('|', $selectedRate);
                $courier = $parts[0] ?? '';
                $service = $parts[1] ?? '';
                $price = $parts[2] ?? 0;
            @endphp
            <input type="hidden" name="shipping_courier" value="{{ $courier }}">
            <input type="hidden" name="shipping_service" value="{{ $service }}">
            <input type="hidden" name="shipping_cost" value="{{ $price }}">
        @endif
        
        {{-- Initialize Alpine state when rendering Rates --}}
        @if($selectedRate && count($rates) > 0)
            @php
                $firstParts = explode('|', $selectedRate);
                $initialPrice = reset($rates)['price'] ?? 0;
            @endphp
            <div x-init="shippingCost = {{ $initialPrice }}; shippingInfo = '{{ reset($rates)['courier_name'] ?? '' }} {{ reset($rates)['courier_service_name'] ?? '' }}'"></div>
        @endif
    @endif
</div>
