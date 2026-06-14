<div>
    <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-4">Metode Pengiriman</h2>
    
    @if(!$selectedAddressId)
        <div class="p-6 text-center border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-xl bg-slate-50/50 dark:bg-slate-900/50">
            <p class="text-sm text-slate-500 dark:text-slate-400">Pilih alamat pengiriman terlebih dahulu untuk melihat opsi kurir.</p>
        </div>
    @else
        <div class="space-y-3 relative">
            {{-- Loading State Overlay --}}
            <div wire:loading wire:target="handleAddressSelected, fetchRates" class="absolute inset-0 bg-white/60 dark:bg-slate-950/60 backdrop-blur-[2px] z-10 flex flex-col items-center justify-center rounded-xl border border-slate-100 dark:border-slate-800">
                <svg class="animate-spin h-6 w-6 text-primary-600 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-xs font-medium text-slate-600 dark:text-slate-400 animate-pulse">Menghitung ongkir...</p>
            </div>

            @if(!$hasValidArea)
                <div wire:loading.remove wire:target="handleAddressSelected, fetchRates" class="p-6 text-center border border-red-200 dark:border-red-900/50 rounded-xl bg-red-50 dark:bg-red-900/20">
                    <p class="text-sm text-red-600 dark:text-red-400 font-medium mb-1">Alamat ini menggunakan format lama (Kecamatan tidak terhubung ke map).</p>
                    <p class="text-xs text-red-500 dark:text-red-500/80">Silakan klik tombol Edit (<svg class="w-3 h-3 inline pb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>) pada alamat di atas dan pilih ulang Area / Kecamatan Anda.</p>
                </div>
            @elseif(count($rates) === 0)
                <div wire:loading.remove wire:target="handleAddressSelected, fetchRates" class="p-6 text-center border border-red-200 dark:border-red-900/50 rounded-xl bg-red-50 dark:bg-red-900/20">
                    <p class="text-sm text-red-600 dark:text-red-400 font-medium">Tidak ada kurir (Sameday/Instant) yang tersedia untuk lokasi ini.</p>
                </div>
            @else
                @foreach($rates as $rate)
                    @php
                        $rateValue = $rate['courier_code'] . '|' . $rate['courier_service_code'] . '|' . $rate['price'];
                        $isSelected = $selectedRate === $rateValue;
                    @endphp
                    <label wire:key="{{ $rateValue }}" class="flex items-center justify-between p-4 rounded-xl border-2 cursor-pointer transition-all {{ $isSelected ? 'border-primary-400 bg-primary-50/50 dark:bg-primary-900/10' : 'border-slate-100 dark:border-slate-800 hover:border-slate-200 dark:hover:border-slate-700 bg-white dark:bg-slate-900/40' }}">
                        <div class="flex items-center gap-3">
                            <input type="radio" 
                                   name="shipping_service_selection" 
                                   value="{{ $rateValue }}" 
                                   wire:model.live="selectedRate"
                                   x-on:change="shippingCost = {{ $rate['price'] }}; shippingInfo = '{{ $rate['courier_name'] }} {{ $rate['courier_service_name'] }}'"
                                   class="text-primary-600 focus:ring-primary-500 dark:bg-slate-800 dark:border-slate-700">
                            <div>
                                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $rate['courier_name'] }} {{ $rate['courier_service_name'] }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Estimasi: {{ $rate['shipment_duration_range'] ?? '?' }} {{ $rate['shipment_duration_unit'] ?? 'jam' }}</p>
                            </div>
                        </div>
                        <span class="text-sm font-bold {{ $isSelected ? 'text-primary-700 dark:text-primary-400' : 'text-slate-900 dark:text-slate-100' }}">Rp {{ number_format($rate['price'], 0, ',', '.') }}</span>
                    </label>
                @endforeach
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
