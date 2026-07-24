@php
    $sort = $sort ?? request('sort', 'created_at');
    $dir = $dir ?? request('direction', 'desc');
    $pageIds = $pageIds ?? $coupons->pluck('id')->values();
    if (!function_exists('sortUrl')) {
        function sortUrl($column, $currentSort, $currentDir) {
            $nextDir = ($currentSort === $column && $currentDir === 'asc') ? 'desc' : 'asc';
            return request()->fullUrlWithQuery(['sort' => $column, 'direction' => $nextDir]);
        }
    }
@endphp
    <div class="overflow-x-auto lg:overflow-x-visible custom-scrollbar">
        <table class="w-full admin-table">
            <thead>
                <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40">
                    <th class="admin-select-cell px-4 py-4 w-10 rounded-tl-2xl">
                        <input type="checkbox" @change="toggleAll(@json($pageIds))" :checked="allSelected(@json($pageIds))"
                               class="w-4 h-4 align-middle rounded border-slate-300 dark:border-slate-600 text-primary-600 focus:ring-primary-500/30 cursor-pointer">
                    </th>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                        <a href="{{ sortUrl('code', $sort, $dir) }}" class="inline-flex items-center gap-1 group hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                            Kode Kupon
                            @if($sort === 'code')
                                @if($dir === 'asc')
                                    <svg class="w-3 h-3 text-primary-500 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/></svg>
                                @else
                                    <svg class="w-3 h-3 text-primary-500 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                @endif
                            @else
                                <svg class="w-3 h-3 text-slate-300 dark:text-slate-600 group-hover:text-slate-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"/></svg>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                        <a href="{{ sortUrl('value', $sort, $dir) }}" class="inline-flex items-center gap-1 group hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                            Tipe & Nilai
                            @if($sort === 'value')
                                @if($dir === 'asc')
                                    <svg class="w-3 h-3 text-primary-500 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/></svg>
                                @else
                                    <svg class="w-3 h-3 text-primary-500 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                @endif
                            @else
                                <svg class="w-3 h-3 text-slate-300 dark:text-slate-600 group-hover:text-slate-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"/></svg>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                        <a href="{{ sortUrl('usage_limit', $sort, $dir) }}" class="inline-flex items-center gap-1 group hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                            Sisa Kuota
                            @if($sort === 'usage_limit')
                                @if($dir === 'asc')
                                    <svg class="w-3 h-3 text-primary-500 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/></svg>
                                @else
                                    <svg class="w-3 h-3 text-primary-500 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                @endif
                            @else
                                <svg class="w-3 h-3 text-slate-300 dark:text-slate-600 group-hover:text-slate-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"/></svg>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                        <a href="{{ sortUrl('is_active', $sort, $dir) }}" class="inline-flex items-center gap-1 group hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                            Status & Waktu
                            @if($sort === 'is_active')
                                @if($dir === 'asc')
                                    <svg class="w-3 h-3 text-primary-500 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/></svg>
                                @else
                                    <svg class="w-3 h-3 text-primary-500 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                @endif
                            @else
                                <svg class="w-3 h-3 text-slate-300 dark:text-slate-600 group-hover:text-slate-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"/></svg>
                            @endif
                        </a>
                    </th>
                    <th class="px-6 py-4 text-right text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest rounded-tr-2xl">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/60 dark:divide-slate-800/60">
                @forelse($coupons as $coupon)
                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/20 transition-colors"
                    :class="isSelected({{ $coupon->id }}) && 'bg-primary-50/50 dark:bg-primary-950/20'">
                    <td class="admin-select-cell px-4 py-4 w-10">
                        <input type="checkbox" @change="toggle({{ $coupon->id }})" :checked="isSelected({{ $coupon->id }})"
                               class="w-4 h-4 align-middle rounded border-slate-300 dark:border-slate-600 text-primary-600 focus:ring-primary-500/30 cursor-pointer">
                    </td>
                    <td data-label="Kode Kupon" class="px-6 py-4">
                        <div class="flex flex-col">
                            <span class="font-extrabold text-slate-900 dark:text-white uppercase tracking-wider text-sm">{{ $coupon->code }}</span>
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 tracking-wide mt-1 leading-none">Min. Beli: Rp {{ number_format($coupon->min_purchase, 0, ',', '.') }}</span>
                        </div>
                    </td>
                    <td data-label="Tipe & Nilai" class="px-6 py-4">
                        @if($coupon->type == 'percent')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-650 dark:text-emerald-400 border border-emerald-250/30 dark:border-emerald-900/30">
                                {{ rtrim(rtrim($coupon->value, '0'), '.') }}%
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-650 dark:text-emerald-400 border border-emerald-250/30 dark:border-emerald-900/30">
                                Rp {{ number_format($coupon->value, 0, ',', '.') }}
                            </span>
                        @endif
                    </td>
                    <td data-label="Sisa Kuota" class="px-6 py-4 text-xs font-semibold text-slate-600 dark:text-slate-300">
                        @if($coupon->usage_limit)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/50 dark:border-slate-700">
                                {{ $coupon->usage_limit - $coupon->used_count }} / {{ $coupon->usage_limit }}
                            </span>
                        @else
                            <span class="italic text-slate-400 dark:text-slate-500">Tak Terbatas</span>
                        @endif
                    </td>
                    <td data-label="Status" class="px-6 py-4">
                        <div class="flex flex-col gap-1.5 items-start">
                            @if($coupon->is_active && (!$coupon->end_date || $coupon->end_date > now()))
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-250/30 dark:border-emerald-900/30">Aktif</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-red-50 dark:bg-red-950/40 text-red-650 dark:text-red-400 border border-red-250/30 dark:border-red-900/30">Nonaktif</span>
                            @endif
                            @if($coupon->end_date)
                                <span class="text-[9px] text-slate-400 dark:text-slate-500 uppercase tracking-widest font-semibold">s.d {{ $coupon->end_date->format('d M y') }}</span>
                            @else
                                <span class="text-[9px] text-slate-400 dark:text-slate-500 uppercase tracking-widest font-semibold">Selamanya</span>
                            @endif
                        </div>
                    </td>
                    <td data-label="Aksi" class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button x-data="" @click="$dispatch('open-modal', 'edit-coupon-{{ $coupon->id }}')" 
                                    class="p-2 text-primary-600 dark:text-primary-400 hover:text-primary-750 hover:bg-primary-50 dark:hover:bg-primary-950/60 rounded-xl transition-all" title="Edit">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/></svg>
                            </button>
                            <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus kupon ini?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/60 rounded-xl transition-all" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                </button>
                            </form>
                        </div>

                        {{-- Modal Edit --}}
                        <div x-data="{ show: false }" x-show="show" 
                             @open-modal.window="if ($event.detail === 'edit-coupon-{{ $coupon->id }}') { show = true; } else if ($event.detail === 'close-all-modals') { show = false; }" 
                             @close-modal.window="show = false" 
                             @keydown.escape.window="show = false" 
                             class="fixed inset-0 z-50 overflow-y-auto text-left" style="display: none;">
                            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                <div x-show="show" 
                                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                     class="absolute inset-0 transition-opacity bg-slate-900/80 backdrop-blur-md" @click="show = false"></div>
                                
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                                
                                <div x-show="show" 
                                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                     class="relative z-10 inline-block w-full max-w-lg overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl rounded-2xl">
                                    
                                    {{-- Modal Header --}}
                                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                                        <h3 class="text-lg font-bold text-slate-900 dark:text-white leading-none">Edit Kupon</h3>
                                        <button @click="show = false" class="text-slate-400 hover:text-slate-650 dark:hover:text-slate-250 transition-colors">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>

                                    <form action="{{ route('admin.coupons.update', $coupon) }}" method="POST" class="flex flex-col min-h-0 overflow-hidden">
                                        @csrf @method('PUT')
                                        <div class="p-6 space-y-4 text-left">
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tipe Diskon</label>
                                                    <div x-data="{ 
                                                            open: false, 
                                                            selectedValue: '{{ $coupon->type }}',
                                                            selectedLabel: '{{ $coupon->type == 'percent' ? 'Persentase (%)' : 'Nominal (Rp)' }}'
                                                         }" 
                                                         class="relative">
                                                        <input type="hidden" name="type" :value="selectedValue">
                                                        <button @click="open = !open" type="button" 
                                                                class="w-full flex items-center justify-between pl-4 pr-3 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 cursor-pointer transition-all">
                                                            <span x-text="selectedLabel"></span>
                                                            <svg class="h-4 w-4 text-slate-400 dark:text-slate-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                            </svg>
                                                        </button>
                                                        <div x-show="open" 
                                                             @click.outside="open = false" 
                                                             x-transition:enter="transition ease-out duration-100"
                                                             x-transition:enter-start="opacity-0 scale-95"
                                                             x-transition:enter-end="opacity-100 scale-100"
                                                             x-transition:leave="transition ease-in duration-75"
                                                             x-transition:leave-start="opacity-100 scale-100"
                                                             x-transition:leave-end="opacity-0 scale-95"
                                                             class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl py-1 overflow-hidden"
                                                             style="display: none;">
                                                            <button type="button" @click="selectedValue = 'fixed'; selectedLabel = 'Nominal (Rp)'; open = false"
                                                                    class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                                                    :class="selectedValue === 'fixed' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">Nominal (Rp)</button>
                                                            <button type="button" @click="selectedValue = 'percent'; selectedLabel = 'Persentase (%)'; open = false"
                                                                    class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                                                    :class="selectedValue === 'percent' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">Persentase (%)</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nilai Diskon</label>
                                                    <input type="number" name="value" value="{{ rtrim(rtrim($coupon->value, '0'), '.') }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all">
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Min. Belanja</label>
                                                    <input type="number" name="min_purchase" value="{{ rtrim(rtrim($coupon->min_purchase, '0'), '.') }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Batas Kuota</label>
                                                    <input type="number" name="usage_limit" value="{{ $coupon->usage_limit }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all">
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Mulai Berlaku</label>
                                                    <input type="datetime-local" name="start_date" value="{{ $coupon->start_date?->format('Y-m-d\TH:i') }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Batas Berlaku</label>
                                                    <input type="datetime-local" name="end_date" value="{{ $coupon->end_date?->format('Y-m-d\TH:i') }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all">
                                                </div>
                                            </div>

                                            <div class="p-4 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-100 dark:border-slate-800">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-10 h-10 rounded-full bg-white dark:bg-slate-900 flex items-center justify-center shadow-sm text-primary-600 dark:text-primary-400">
                                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <p class="text-sm font-bold text-slate-900 dark:text-slate-100">Status Kupon</p>
                                                            <p class="text-xs text-slate-500 dark:text-slate-500">Tentukan apakah kupon ini aktif digunakan</p>
                                                        </div>
                                                    </div>
                                                    <label class="relative inline-flex items-center cursor-pointer">
                                                        <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ $coupon->is_active ? 'checked' : '' }}>
                                                        <div class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:inset-s-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3 bg-white dark:bg-slate-900">
                                            <button type="button" @click="show = false" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition-colors">Batal</button>
                                            <button type="submit" class="px-5 py-2.5 bg-primary-600 text-white text-sm font-semibold rounded-xl hover:bg-primary-700 shadow-sm transition-all duration-200">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-20 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-16 h-16 rounded-2xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-300 dark:text-slate-600 mb-4 transition-colors">
                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" /></svg>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 transition-colors">Kupon Tidak Ditemukan</h3>
                            <p class="text-slate-500 dark:text-slate-500 text-sm mt-1 max-w-xs mx-auto transition-colors">Belum ada kupon yang dibuat atau kupon yang sesuai tidak ditemukan.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($coupons->hasPages())
    <div class="px-6 py-3 border-t border-slate-200 dark:border-slate-800">
        {{ $coupons->links() }}
    </div>
    @endif
