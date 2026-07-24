@php
    $sort = $sort ?? request('sort', 'created_at');
    $dir = $dir ?? request('direction', 'desc');
    $pageIds = $pageIds ?? $products->pluck('id')->values();
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
                            <a href="{{ sortUrl('name', $sort, $dir) }}" class="inline-flex items-center gap-1 group hover:text-slate-600 dark:hover:text-slate-350 transition-colors">
                                Produk
                                @if($sort === 'name')
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
                            <a href="{{ sortUrl('description', $sort, $dir) }}" class="inline-flex items-center gap-1 group hover:text-slate-600 dark:hover:text-slate-350 transition-colors">
                                Deskripsi
                                @if($sort === 'description')
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
                            <a href="{{ sortUrl('category_id', $sort, $dir) }}" class="inline-flex items-center gap-1 group hover:text-slate-600 dark:hover:text-slate-350 transition-colors">
                                Kategori
                                @if($sort === 'category_id')
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
                        <th class="px-6 py-4 text-right text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                            <a href="{{ sortUrl('price', $sort, $dir) }}" class="inline-flex items-center justify-end gap-1 group hover:text-slate-600 dark:hover:text-slate-350 transition-colors w-full">
                                Harga
                                @if($sort === 'price')
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
                        <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                            <a href="{{ sortUrl('stock', $sort, $dir) }}" class="inline-flex items-center justify-center gap-1 group hover:text-slate-600 dark:hover:text-slate-350 transition-colors mx-auto">
                                Stok
                                @if($sort === 'stock')
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
                        <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                            <a href="{{ sortUrl('is_featured', $sort, $dir) }}" class="inline-flex items-center justify-center gap-1 group hover:text-slate-600 dark:hover:text-slate-350 transition-colors mx-auto">
                                Unggulan
                                @if($sort === 'is_featured')
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
                    @forelse($products as $product)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/20 transition-colors"
                        :class="isSelected({{ $product->id }}) && 'bg-primary-50/50 dark:bg-primary-950/20'">
                        <td class="admin-select-cell px-4 py-4 w-10">
                            <input type="checkbox" @change="toggle({{ $product->id }})" :checked="isSelected({{ $product->id }})"
                                   class="w-4 h-4 align-middle rounded border-slate-300 dark:border-slate-600 text-primary-600 focus:ring-primary-500/30 cursor-pointer">
                        </td>
                        <td data-label="Produk" class="px-6 py-4">
                            <div class="flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 shrink-0 border border-slate-100 dark:border-slate-700/60 shadow-xs">
                                    @if($product->image)
                                        <img src="{{ asset('storage/'.$product->image) }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-slate-50 dark:bg-slate-800">
                                            <svg class="w-5 h-5 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 18.75V5.25A2.25 2.25 0 0 1 5.25 3h13.5A2.25 2.25 0 0 1 21 5.25v13.5A2.25 2.25 0 0 1 18.75 21H5.25A2.25 2.25 0 0 1 3 18.75Z" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold text-slate-900 dark:text-slate-100 hover:text-primary-600 dark:hover:text-primary-400 transition-colors leading-none mb-1">{{ $product->name }}</span>
                                    <span class="text-[10px] text-slate-400 dark:text-slate-500 tracking-wider">/{{ $product->slug }}</span>
                                </div>
                            </div>
                        </td>
                        <td data-label="Deskripsi" class="px-6 py-4 text-xs text-slate-500 dark:text-slate-400 max-w-[200px] truncate" title="{{ $product->description ?: '-' }}">{{ $product->description ?: '-' }}</td>
                        <td data-label="Kategori" class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/50 dark:border-slate-700">
                                {{ $product->category->name ?? '-' }}
                            </span>
                        </td>
                        <td data-label="Harga" class="px-6 py-4 text-sm text-right font-bold text-slate-900 dark:text-slate-100">{{ $product->formatted_price }}</td>
                        <td data-label="Stok" class="px-6 py-4">
                            {{-- Availability switch (what customers see) plus the stock
                                 counter, which keeps running behind the scenes. --}}
                            <div class="flex flex-col items-center gap-1.5">
                                <form method="POST" action="{{ route('admin.products.toggle-availability', $product) }}" class="inline-flex items-center gap-2">
                                    @csrf @method('PATCH')
                                    <label class="relative inline-flex items-center cursor-pointer group" title="{{ $product->is_available ? 'Tandai habis' : 'Tandai tersedia' }}">
                                        <input type="checkbox" class="sr-only peer" {{ $product->is_available ? 'checked' : '' }} onchange="this.closest('form').submit()">
                                        <div class="w-9 h-5 bg-slate-200 dark:bg-slate-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500 dark:peer-checked:bg-emerald-600 group-hover:shadow-sm"></div>
                                    </label>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $product->is_available ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400' : 'bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400' }}">
                                        {{ $product->is_available ? 'Tersedia' : 'Habis' }}
                                    </span>
                                </form>

                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-bold {{ $product->isOutOfStock() ? 'bg-red-50 dark:bg-red-950/40 text-red-650 dark:text-red-400 border border-red-200/50 dark:border-red-950/30' : ($product->isLowStock() ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-650 dark:text-amber-400 border border-amber-200/50 dark:border-amber-950/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200/50 dark:border-slate-700') }}">
                                    Tersedia: {{ $product->stock }}
                                </span>

                                {{-- Held by live orders. Shown only when there is
                                     something to hold, so the common case stays quiet. --}}
                                @if($product->reserved_quantity)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-900/30"
                                          title="Sudah dipesan pelanggan dan dipotong dari stok tersedia. Total di gudang: {{ $product->stock + $product->reserved_quantity }}.">
                                        {{ $product->reserved_quantity }} tertahan
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td data-label="Unggulan" class="px-6 py-4">
                            <div class="flex items-center justify-center">
                                <form method="POST" action="{{ route('admin.products.toggle-featured', $product) }}" class="inline-flex items-center gap-2">
                                    @csrf @method('PATCH')
                                    <label class="relative inline-flex items-center cursor-pointer group">
                                        <input type="checkbox" class="sr-only peer" {{ $product->is_featured ? 'checked' : '' }} onchange="this.closest('form').submit()">
                                        <div class="w-9 h-5 bg-slate-200 dark:bg-slate-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-amber-500 dark:peer-checked:bg-amber-600 group-hover:shadow-sm"></div>
                                    </label>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $product->is_featured ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500' }}">
                                        {{ $product->is_featured ? 'Unggulan' : 'Reguler' }}
                                    </span>
                                </form>
                            </div>
                        </td>
                        <td data-label="Aksi" class="px-6 py-4 text-right flex items-center justify-end gap-2">
                            <div class="relative group/tooltip inline-flex">
                                <button @click="resetGallery(); showModal=true; editMode=true; form={id:{{ $product->id }},slug:'{{ $product->slug }}',name:'{{ addslashes($product->name) }}',category_id:'{{ $product->category_id }}',description:'{{ addslashes($product->description) }}',price:'{{ $product->price }}',stock:'{{ $product->stock }}',reserved_quantity:{{ (int) $product->reserved_quantity }},is_featured:{{ $product->is_featured ? 'true' : 'false' }}, image: '{{ $product->image }}'}; existingGallery={{ $product->images->toJson() }}; variants={{ $product->variants->toJson() }};"
                                        class="p-2 text-primary-600 dark:text-primary-400 hover:text-primary-750 hover:bg-primary-50 dark:hover:bg-primary-950/60 rounded-xl transition-all" title="Edit">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                </button>
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-slate-900 text-white text-[10px] font-bold rounded-lg shadow-xl opacity-0 group-hover/tooltip:opacity-100 transition-all pointer-events-none whitespace-nowrap z-50">
                                    Edit Produk
                                    <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></div>
                                </div>
                            </div>
                            <div class="relative group/tooltip inline-flex">
                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="inline" onsubmit="return confirm('Hapus produk ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/60 rounded-xl transition-all" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                    </button>
                                </form>
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-slate-900 text-white text-[10px] font-bold rounded-lg shadow-xl opacity-0 group-hover/tooltip:opacity-100 transition-all pointer-events-none whitespace-nowrap z-50">
                                    Hapus Produk
                                    <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 rounded-2xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-300 dark:text-slate-600 mb-4 transition-colors">
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                                </div>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 transition-colors">Produk Tidak Ditemukan</h3>
                                <p class="text-slate-500 dark:text-slate-500 text-sm mt-1 max-w-xs mx-auto transition-colors">Maaf, kami tidak dapat menemukan produk yang sesuai dengan filter atau kata kunci pencarian Anda.</p>
                                <div class="mt-6">
                                    <a href="{{ route('admin.products.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-bold rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors duration-200">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                                        Atur Ulang Semua Filter
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-3 border-t border-slate-200 dark:border-slate-800">{{ $products->links() }}</div>
