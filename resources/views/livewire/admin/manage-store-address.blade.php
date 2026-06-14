<div class="space-y-8">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Left Column: Basic Info & Area --}}
        <div class="space-y-6">
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider transition-colors">Nama Toko (Shipper) <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="store_name" placeholder="Gegares Ecommerce" 
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-900/30 text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-4 focus:ring-primary-500/10 focus:border-primary-400 transition-all font-medium">
                    @error('store_name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider transition-colors">Nomor Telepon Toko <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="contact_phone" placeholder="0821..." 
                               class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-900/30 text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-4 focus:ring-primary-500/10 focus:border-primary-400 transition-all font-medium">
                        @error('contact_phone') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider transition-colors">Email Kontak <span class="text-xs text-slate-400 dark:text-slate-600 font-normal lowercase">(Opsional)</span></label>
                        <input type="email" wire:model="contact_email" placeholder="admin@gegares.com" 
                               class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-900/30 text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-4 focus:ring-primary-500/10 focus:border-primary-400 transition-all font-medium">
                        @error('contact_email') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <hr class="border-slate-100 dark:border-slate-800">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider transition-colors">Cari Area / Kecamatan <span class="text-red-500">*</span></label>
                    @if($area_id)
                        <div class="flex items-center justify-between p-4 rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50/30 dark:bg-emerald-900/10 transition-colors">
                            <div>
                                <p class="text-sm font-bold text-emerald-800 dark:text-emerald-400">{{ $city }}</p>
                                <p class="text-xs text-emerald-600 dark:text-emerald-500 font-medium transition-colors">{{ $province }} • {{ $postal_code }}</p>
                            </div>
                            <button type="button" wire:click="$set('area_id', '')" 
                                    class="text-xs font-extrabold text-emerald-700 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-900/40 hover:bg-emerald-200 dark:hover:bg-emerald-900/60 px-4 py-2 rounded-lg transition-all uppercase tracking-widest">
                                Ganti Area
                            </button>
                        </div>
                    @else
                        <div class="relative">
                            <input type="text" 
                                   wire:model.live.debounce.500ms="searchQuery" 
                                   class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-900/30 text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-4 focus:ring-primary-500/10 focus:border-primary-400 transition-all font-medium"
                                   placeholder="Ketik minimal 3 huruf (Cth: Tebet, Menteng...)">
                            
                            @if(count($areaSearchResults) > 0)
                                <div class="absolute z-60 w-full mt-2 bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 shadow-xl dark:shadow-black/60 overflow-hidden max-h-60 overflow-y-auto transition-all">
                                    <ul class="divide-y divide-slate-50 dark:divide-slate-800">
                                        @foreach($areaSearchResults as $area)
                                            <li>
                                                <button type="button" 
                                                        wire:click="selectArea('{{ $area['id'] }}', '{{ $area['name'] }}', '{{ $area['administrative_division_level_2_name'] }}', '{{ $area['administrative_division_level_1_name'] }}', '{{ $area['postal_code'] }}', '{{ $area['latitude'] ?? '' }}', '{{ $area['longitude'] ?? '' }}')"
                                                        class="w-full text-left px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                                                    <p class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ $area['name'] }}</p>
                                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $area['administrative_division_level_2_name'] }}, {{ $area['administrative_division_level_1_name'] }} • {{ $area['postal_code'] }}</p>
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @endif
                    @error('area_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider transition-colors">Cari Patokan Lokasi <span class="text-xs text-slate-400 dark:text-slate-600 font-normal lowercase">(Opsional)</span></label>
                    <div class="relative">
                        <input type="text" 
                               wire:model.live.debounce.500ms="addressSearchQuery" 
                               class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-900/30 text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-4 focus:ring-primary-500/10 focus:border-primary-400 transition-all font-medium text-sm" 
                               placeholder="Cth: Gerai Gegares Jembatan Besi, atau nama gedung...">
                        
                        @if(count($addressSearchResults) > 0)
                            <div class="absolute z-60 w-full mt-2 bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 shadow-xl dark:shadow-black/60 overflow-hidden max-h-60 overflow-y-auto transition-all">
                                <ul class="divide-y divide-slate-50 dark:divide-slate-800">
                                    @foreach($addressSearchResults as $result)
                                        <li>
                                            <button type="button" 
                                                    wire:click="selectAddressResult('{{ addslashes($result['display_name']) }}', '{{ $result['lat'] }}', '{{ $result['lon'] }}')"
                                                    class="w-full text-left px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                                                <p class="text-sm font-medium text-slate-800 dark:text-slate-200 line-clamp-2">{{ $result['display_name'] }}</p>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider transition-colors">Alamat Lengkap Toko <span class="text-red-500">*</span></label>
                    <textarea wire:model="address_line" rows="4" 
                              class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-900/30 text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-4 focus:ring-primary-500/10 focus:border-primary-400 transition-all font-medium text-sm"
                              placeholder="Nama Jalan, No. Rumah / RT RW, Patokan Detail"></textarea>
                    @error('address_line') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- Right Column: Map Pinpoint --}}
        <div class="space-y-6">
            <div 
                x-data="{
                    observer: null,
                    initMap() {
                        if (this.map) return;
                        if (typeof L === 'undefined') {
                            setTimeout(() => this.initMap(), 100);
                            return;
                        }
                        
                        const lat = {{ $latitude ?? -6.1558 }};
                        const lng = {{ $longitude ?? 106.8048 }};
                        
                        this.map = L.map($refs.mapContainer, {
                            zoomControl: true,
                            attributionControl: false
                        }).setView([lat, lng], 15);

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                        }).addTo(this.map);

                        this.marker = L.marker([lat, lng], {
                            draggable: true,
                            autoPan: true
                        }).addTo(this.map);

                        this.marker.on('dragend', () => {
                            const pos = this.marker.getLatLng();
                            this.updateCoords(pos.lat, pos.lng);
                        });

                        this.map.on('click', (e) => {
                            this.marker.setLatLng(e.latlng);
                            this.updateCoords(e.latlng.lat, e.latlng.lng);
                        });

                        // Robust fix for partial rendering in hidden/dynamic containers
                        this.observer = new ResizeObserver(() => {
                            if (this.map) {
                                this.map.invalidateSize();
                            }
                        });
                        this.observer.observe($refs.mapContainer);

                        setTimeout(() => this.map.invalidateSize(), 300);
                    },
                    updateCoords(lat, lng) {
                        @this.set('latitude', parseFloat(lat).toFixed(7));
                        @this.set('longitude', parseFloat(lng).toFixed(7));
                    }
                }"
                x-init="
                    initMap();
                    $watch('$wire.latitude', value => {
                        if (this.map && this.marker && value) {
                            const newPos = [value, $wire.longitude];
                            this.marker.setLatLng(newPos);
                            this.map.setView(newPos, 15);
                        }
                    });
                "
                class="space-y-4"
            >
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider transition-colors">Titik Jemput Kurir <span class="text-red-500">*</span></label>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mb-3 font-medium transition-colors">Klik pada peta atau geser pin tepat di depan pintu gerai Anda.</p>
                    <div wire:ignore>
                        <div x-ref="mapContainer" style="height: 380px;" class="rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-inner bg-slate-50 dark:bg-slate-950 transition-all"></div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 dark:text-slate-600 block mb-1">Latitude</label>
                        <span class="text-xs font-mono font-bold text-slate-600 dark:text-slate-300" x-text="$wire.latitude"></span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800">
                        <label class="text-[10px] uppercase tracking-widest font-extrabold text-slate-400 dark:text-slate-600 block mb-1">Longitude</label>
                        <span class="text-xs font-mono font-bold text-slate-600 dark:text-slate-300" x-text="$wire.longitude"></span>
                    </div>
                </div>
            </div>

            {{-- Save Button --}}
            <div class="pt-4">
                <button wire:click="save" 
                        class="w-full py-4 bg-primary-600 text-white rounded-2xl font-extrabold uppercase tracking-widest shadow-lg shadow-primary-200 dark:shadow-none hover:bg-primary-700 hover:-translate-y-0.5 transition-all flex items-center justify-center gap-3">
                    <svg wire:loading.remove wire:target="save" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0z" /></svg>
                    <svg wire:loading wire:target="save" class="animate-spin h-5 w-5" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Simpan Pengaturan
                </button>
            </div>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @endpush
</div>
