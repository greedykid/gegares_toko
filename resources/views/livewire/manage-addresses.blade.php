<div>
    @if($addresses->count())
        <div class="space-y-3">
            @foreach($addresses as $address)
                <div wire:key="address-{{ $address->id }}" class="relative rounded-xl border-2 transition-all {{ $selectedAddressId == $address->id ? 'border-primary-400 bg-primary-50/50 dark:bg-primary-900/10' : 'border-slate-100 dark:border-slate-800 hover:border-slate-200 dark:hover:border-slate-700' }}">
                    <label class="flex items-start gap-4 p-4 cursor-pointer">
                        <input type="radio" 
                               name="address_id" 
                               value="{{ $address->id }}"
                               wire:model.live="selectedAddressId"
                               class="mt-1 text-primary-600 focus:ring-primary-500 dark:bg-slate-800 dark:border-slate-700">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-bold text-slate-900 dark:text-slate-100">{{ $address->label }}</span>
                                @if($address->is_primary)
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-400 rounded-md">Utama</span>
                                @endif
                            </div>
                            <p class="text-sm font-semibold text-slate-900 dark:text-slate-200">{{ $address->recipient_name }} — {{ $address->phone }}</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $address->address_line }}</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $address->city }}, {{ $address->province }} {{ $address->postal_code }}</p>
                        </div>
                    </label>
                    <div class="absolute top-4 right-4 flex items-center gap-2">
                        <button type="button" wire:click="editAddress({{ $address->id }})" class="p-1.5 text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/40 rounded-lg transition-colors" title="Edit Alamat">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/></svg>
                        </button>
                        @if(!$address->is_primary)
                            <button type="button" wire:click="setPrimary({{ $address->id }})" class="p-1.5 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/40 rounded-lg transition-colors" title="Jadikan Utama">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/></svg>
                            </button>
                        @endif
                        <button type="button" wire:click="deleteAddress({{ $address->id }})" wire:confirm="Yakin ingin menghapus alamat ini?" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/40 rounded-lg transition-colors" title="Hapus Alamat">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
        <button type="button" wire:click="createNew" class="mt-4 w-full py-3 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-xl text-sm font-semibold text-slate-500 dark:text-slate-500 hover:text-primary-600 dark:hover:text-primary-400 hover:border-primary-400 dark:hover:border-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-all flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Tambah Alamat Baru
        </button>
    @else
        <div class="text-center py-8 px-4 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-800">
            <div class="w-16 h-16 bg-slate-50 dark:bg-slate-900 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-slate-400 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 mb-1">Belum ada Alamat</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Silakan tambahkan alamat pengiriman untuk melanjutkan pembayaran.</p>
            <button type="button" wire:click="createNew" class="px-6 py-2.5 bg-primary-600 text-white text-sm font-semibold rounded-xl hover:bg-primary-700 transition-colors shadow-sm">
                + Tambah Alamat
            </button>
        </div>
        <input type="hidden" name="address_id" required> {{-- Prevent HTML5 form submit if no address --}}
    @endif

    {{-- Leaflet Map Assets (Pre-loaded) --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    {{-- Modal Form --}}
    @if($showModal)
        @teleport('body')
            <div class="fixed inset-0 z-100 flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
                <div class="relative bg-white dark:bg-slate-950 rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] flex flex-col overflow-hidden">
                    {{-- Header --}}
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-white dark:bg-slate-950 z-10 sticky top-0">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ $isEditing ? 'Edit Alamat' : 'Tambah Alamat Baru' }}</h3>
                        <button type="button" wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="p-6 overflow-y-auto">
                        <div class="space-y-4">
                            <div class="space-y-4">
                                {{-- Label Alamat --}}
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Label Alamat</label>
                                    <input type="text" wire:model="label" placeholder="Rumah, Kantor, dll" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all text-sm">
                                    @error('label') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                {{-- Alamat Utama Toggle Card --}}
                                <div class="flex items-center justify-between p-4 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 transition-all hover:bg-white dark:hover:bg-slate-800 hover:border-primary-100 dark:hover:border-primary-900/50 group shadow-sm">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-white dark:bg-slate-800 flex items-center justify-center text-primary-500 shadow-sm transition-transform group-hover:scale-110">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-900 dark:text-slate-100 leading-none">Alamat Utama</p>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Gunakan alamat ini sebagai tujuan utama pengiriman.</p>
                                        </div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" wire:model="is_primary" class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-500/20 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:inset-s-[2px] after:bg-white after:border-gray-300 dark:after:border-slate-600 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600 shadow-inner"></div>
                                    </label>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Penerima</label>
                                    <input type="text" wire:model="recipient_name" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all text-sm">
                                    @error('recipient_name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Nomor Telepon</label>
                                    <input type="text" wire:model="phone" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all text-sm">
                                    @error('phone') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>

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
                                    <input type="hidden" wire:model="area_id">
                                    @error('area_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                @else
                                    <div class="relative">
                                        <input type="text" 
                                               wire:model.live.debounce.500ms="searchQuery" 
                                               @class([
                                                   'w-full px-4 py-2.5 rounded-xl border bg-slate-50/50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-primary-500 focus:border-primary-500 text-sm transition-all',
                                                   'border-red-500' => $errors->has('area_id'),
                                                   'border-slate-200 dark:border-slate-700' => !$errors->has('area_id')
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

                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Cari Lokasi / Patokan <span class="text-xs text-slate-400 dark:text-slate-600 font-normal">(Opsional)</span></label>
                                <div class="relative">
                                    <input type="text" 
                                           wire:model.live.debounce.500ms="addressSearchQuery" 
                                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all text-sm" 
                                           placeholder="Cth: Monas, Grand Indonesia, atau nama gedung...">
                                    
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

                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Alamat Lengkap <span class="text-red-500">*</span></label>
                                <textarea wire:model="address_line" rows="3" 
                                          @class([
                                              'w-full px-4 py-2.5 rounded-xl border bg-slate-50/50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-primary-500 focus:border-primary-500 text-sm transition-all',
                                              'border-red-500' => $errors->has('address_line'),
                                              'border-slate-200 dark:border-slate-700' => !$errors->has('address_line')
                                          ])
                                          placeholder="Nama Jalan, Gedung, No. Rumah / RT RW, Patokan"></textarea>
                                @error('address_line') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            {{-- Leaflet Map Pin --}}
                            <div 
                                x-data="{
                                    map: null,
                                    marker: null,
                                    initMap() {
                                        if (this.map || typeof L === 'undefined') return;
                                        
                                        const lat = {{ $latitude ?? -6.2 }};
                                        const lng = {{ $longitude ?? 106.8166660 }};
                                        
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

                                        setTimeout(() => {
                                            this.map.invalidateSize();
                                        }, 200);
                                    },
                                    updateCoords(lat, lng) {
                                        const latFixed = parseFloat(lat).toFixed(7);
                                        const lngFixed = parseFloat(lng).toFixed(7);
                                        @this.set('latitude', parseFloat(latFixed));
                                        @this.set('longitude', parseFloat(lngFixed));
                                    }
                                }"
                                x-init="
                                    $watch('$wire.showModal', value => {
                                        if (value) setTimeout(() => initMap(), 100);
                                    });
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
                                <div class="flex items-center gap-4 mt-2">
                                    <div class="flex-1">
                                        <label class="text-[10px] uppercase tracking-wider font-bold text-slate-400 dark:text-slate-600">Latitude</label>
                                        <input type="text" readonly class="w-full px-3 py-1.5 rounded-lg border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 text-xs text-slate-600 dark:text-slate-400 font-mono" x-bind:value="$wire.latitude">
                                    </div>
                                    <div class="flex-1">
                                        <label class="text-[10px] uppercase tracking-wider font-bold text-slate-400 dark:text-slate-600">Longitude</label>
                                        <input type="text" readonly class="w-full px-3 py-1.5 rounded-lg border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 text-xs text-slate-600 dark:text-slate-400 font-mono" x-bind:value="$wire.longitude">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 flex justify-end gap-3 rounded-b-2xl">
                        <button type="button" wire:click="$set('showModal', false)" class="px-5 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="button" wire:click="save" class="px-5 py-2.5 bg-primary-600 text-white text-sm font-semibold rounded-xl hover:bg-primary-700 transition-colors shadow-sm">
                            Simpan Alamat
                        </button>
                    </div>
                </div>
            </div>
        @endteleport
    @endif

</div>
