<div>
    {{-- Leaflet assets (same as the authenticated address form) --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    @if($saved)
        {{-- Saved summary card --}}
        <div class="rounded-xl border-2 border-primary-400 bg-primary-50/50 dark:bg-primary-900/10 p-4 flex items-start gap-4">
            <div class="w-10 h-10 rounded-full bg-white dark:bg-slate-800 flex items-center justify-center text-primary-500 shrink-0 shadow-sm">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-bold text-slate-900 dark:text-slate-100">{{ $label }}</span>
                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-400 rounded-md">Alamat Tujuan</span>
                </div>
                <p class="text-sm font-semibold text-slate-900 dark:text-slate-200">{{ $recipient_name }} — {{ $phone }}</p>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $address_line }}</p>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $city }}, {{ $province }} {{ $postal_code }}</p>
            </div>
            <button type="button" wire:click="edit" class="text-xs font-semibold text-primary-700 dark:text-primary-400 bg-primary-100 dark:bg-primary-950 hover:bg-primary-200 dark:hover:bg-primary-900 px-3 py-1.5 rounded-lg transition-colors shrink-0">
                Ubah
            </button>
        </div>
    @else
        <div class="space-y-4">
            {{-- Recipient --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Penerima <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="recipient_name" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all text-sm">
                    @error('recipient_name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Nomor Telepon <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="phone" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all text-sm">
                    @error('phone') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Label Alamat</label>
                <input type="text" wire:model="label" placeholder="Rumah, Kantor, dll" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all text-sm">
                @error('label') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            {{-- Area / Kecamatan --}}
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Cari Area / Kecamatan <span class="text-red-500">*</span></label>
                @if($area_id)
                    <div class="flex items-center justify-between p-3 rounded-xl border border-emerald-200 dark:border-emerald-900/50 bg-emerald-50/50 dark:bg-emerald-900/20">
                        <div>
                            <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-400">{{ $city }}</p>
                            <p class="text-xs text-emerald-600 dark:text-emerald-500">{{ $province }} • {{ $postal_code }}</p>
                        </div>
                        <button type="button" wire:click="$set('area_id', '')" class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-950 hover:bg-emerald-200 dark:hover:bg-emerald-900 px-3 py-1.5 rounded-lg transition-colors">
                            Ganti Area
                        </button>
                    </div>
                    @error('area_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                @else
                    <div class="relative">
                        <input type="text"
                               wire:model.live.debounce.500ms="searchQuery"
                               @class([
                                   'w-full px-4 py-2.5 rounded-xl border bg-slate-50/50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-primary-500 focus:border-primary-500 text-sm transition-all',
                                   'border-red-500' => $errors->has('area_id'),
                                   'border-slate-200 dark:border-slate-700' => !$errors->has('area_id'),
                               ])
                               placeholder="Ketik minimal 3 huruf (Cth: Tebet, Menteng... - Khusus DKI Jakarta)">

                        <div wire:loading wire:target="searchQuery" class="absolute right-3 top-3 text-slate-400">
                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>

                        @if(count($areaSearchResults) > 0)
                            <div class="absolute z-60 w-full mt-2 bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 shadow-xl overflow-hidden max-h-60 overflow-y-auto">
                                <ul class="divide-y divide-slate-50 dark:divide-slate-800">
                                    @foreach($areaSearchResults as $area)
                                        <li>
                                            <button type="button"
                                                    wire:click="selectArea('{{ $area['id'] }}', '{{ $area['name'] }}', '{{ $area['administrative_division_level_2_name'] }}', '{{ $area['administrative_division_level_1_name'] }}', '{{ $area['postal_code'] }}', '{{ $area['latitude'] ?? '' }}', '{{ $area['longitude'] ?? '' }}')"
                                                    class="w-full text-left px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors focus:bg-slate-50 focus:outline-none">
                                                <p class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ $area['name'] }}</p>
                                                <p class="text-xs text-slate-500 dark:text-slate-500 mt-0.5">{{ $area['administrative_division_level_2_name'] }}, {{ $area['administrative_division_level_1_name'] }} • {{ $area['postal_code'] }}</p>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @elseif(strlen($searchQuery) > 2)
                            <div wire:loading.remove wire:target="searchQuery" class="absolute z-60 w-full mt-2 bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 shadow-xl p-4 text-center">
                                <p class="text-sm text-slate-500 dark:text-slate-400">Area tidak ditemukan. Coba kata kunci lain.</p>
                            </div>
                        @endif
                    </div>
                    @error('area_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                @endif
            </div>

            {{-- Landmark search --}}
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Cari Lokasi / Patokan <span class="text-xs text-slate-400 dark:text-slate-600 font-normal">(Opsional)</span></label>
                <div class="relative">
                    <input type="text"
                           wire:model.live.debounce.500ms="addressSearchQuery"
                           @disabled(empty($area_id))
                           @class([
                               'w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all text-sm',
                               'opacity-60 cursor-not-allowed' => empty($area_id),
                           ])
                           placeholder="{{ empty($area_id) ? 'Pilih area/kecamatan dulu di atas' : 'Cari jalan/gang/patokan di ' . $city }}">

                    @if(empty($area_id))
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Hasil pencarian akan menyempit ke kecamatan yang Anda pilih.</p>
                    @endif

                    <div wire:loading wire:target="addressSearchQuery" class="absolute right-3 top-3 text-slate-400">
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>

                    @if(count($addressSearchResults) > 0)
                        <div class="absolute z-60 w-full mt-2 bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 shadow-xl overflow-hidden max-h-60 overflow-y-auto">
                            <ul class="divide-y divide-slate-50 dark:divide-slate-800">
                                @foreach($addressSearchResults as $result)
                                    <li>
                                        <button type="button"
                                                wire:click="selectAddressResult('{{ addslashes($result['display_name']) }}', '{{ $result['lat'] }}', '{{ $result['lon'] }}')"
                                                class="w-full text-left px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors focus:bg-slate-50 focus:outline-none">
                                            <p class="text-sm font-medium text-slate-800 dark:text-slate-200 line-clamp-2">{{ $result['display_name'] }}</p>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Full address --}}
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Alamat Lengkap <span class="text-red-500">*</span></label>
                <textarea wire:model="address_line" rows="3"
                          @class([
                              'w-full px-4 py-2.5 rounded-xl border bg-slate-50/50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-primary-500 focus:border-primary-500 text-sm transition-all',
                              'border-red-500' => $errors->has('address_line'),
                              'border-slate-200 dark:border-slate-700' => !$errors->has('address_line'),
                          ])
                          placeholder="Nama Jalan, Gedung, No. Rumah / RT RW, Patokan"></textarea>
                @error('address_line') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            {{-- Leaflet map pin --}}
            <div
                x-data="{
                    map: null,
                    marker: null,
                    initMap() {
                        if (this.map || typeof L === 'undefined') return;
                        const lat = {{ $latitude ?? -6.2 }};
                        const lng = {{ $longitude ?? 106.8166660 }};
                        this.map = L.map($refs.mapContainer, { zoomControl: true, attributionControl: false }).setView([lat, lng], 15);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(this.map);
                        this.marker = L.marker([lat, lng], { draggable: true, autoPan: true }).addTo(this.map);
                        this.marker.on('dragend', () => { const pos = this.marker.getLatLng(); this.updateCoords(pos.lat, pos.lng); });
                        this.map.on('click', (e) => { this.marker.setLatLng(e.latlng); this.updateCoords(e.latlng.lat, e.latlng.lng); });
                        setTimeout(() => { this.map.invalidateSize(); }, 200);
                    },
                    updateCoords(lat, lng) {
                        const latFixed = parseFloat(lat).toFixed(7);
                        const lngFixed = parseFloat(lng).toFixed(7);
                        @this.set('latitude', parseFloat(latFixed));
                        @this.set('longitude', parseFloat(lngFixed));
                    }
                }"
                x-init="
                    setTimeout(() => initMap(), 200);
                    $watch('$wire.latitude', value => {
                        if (this.map && this.marker && value) {
                            const newPos = [value, $wire.longitude];
                            this.marker.setLatLng(newPos);
                            this.map.setView(newPos, 15);
                        }
                    });
                "
                class="space-y-2"
            >
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Titik Lokasi di Peta <span class="text-red-500">*</span></label>
                <p class="text-xs text-slate-400 dark:text-slate-500 mb-2">Geser pin untuk mengatur posisi yang akurat.</p>
                <div wire:ignore>
                    <div x-ref="mapContainer" style="height: 220px; width: 100%;" class="rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden z-0"></div>
                </div>
            </div>

            <button type="button" wire:click="save" class="w-full py-3 bg-primary-600 text-white text-sm font-semibold rounded-xl hover:bg-primary-700 transition-colors shadow-sm">
                Simpan Alamat
            </button>
        </div>
    @endif
</div>
