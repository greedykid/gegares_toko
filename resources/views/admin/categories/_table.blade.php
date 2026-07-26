@php
    $sort = $sort ?? request('sort', 'created_at');
    $dir = $dir ?? request('direction', 'desc');
    $pageIds = $pageIds ?? $categories->pluck('id')->values();
@endphp
        <div class="overflow-x-auto lg:overflow-x-visible custom-scrollbar">
            <table class="w-full admin-table">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40">
                        <th class="admin-select-cell px-4 py-4 w-10 rounded-tl-2xl">
                            <input type="checkbox" @change="toggleAll(@json($pageIds))" :checked="allSelected(@json($pageIds))"
                                   class="w-4 h-4 align-middle rounded border-slate-300 dark:border-slate-600 text-primary-600 focus:ring-primary-500/30 cursor-pointer">
                        </th>
                        <x-admin.sort-header column="name" :sort="$sort" :dir="$dir">Nama</x-admin.sort-header>
                        <x-admin.sort-header column="description" :sort="$sort" :dir="$dir">Deskripsi</x-admin.sort-header>
                        <x-admin.sort-header column="products_count" :sort="$sort" :dir="$dir" align="center">Produk</x-admin.sort-header>
                        <x-admin.sort-header column="is_active" :sort="$sort" :dir="$dir" align="center">Status</x-admin.sort-header>
                        <th class="px-6 py-4 text-right text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest rounded-tr-2xl">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/60 dark:divide-slate-800/60">
                    @foreach($categories as $cat)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/20 transition-colors"
                        :class="isSelected({{ $cat->id }}) && 'bg-primary-50/50 dark:bg-primary-950/20'">
                        <td class="admin-select-cell px-4 py-4 w-10">
                            <input type="checkbox" @change="toggle({{ $cat->id }})" :checked="isSelected({{ $cat->id }})"
                                   class="w-4 h-4 align-middle rounded border-slate-300 dark:border-slate-600 text-primary-600 focus:ring-primary-500/30 cursor-pointer">
                        </td>
                        <td data-label="Nama" class="px-6 py-4">
                            <div class="flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 shrink-0 border border-slate-100 dark:border-slate-700/60 shadow-xs">
                                    @if($cat->image)
                                        <img src="{{ asset('storage/'.$cat->image) }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-slate-50 dark:bg-slate-800">
                                            <svg class="w-5 h-5 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 18.75V5.25A2.25 2.25 0 0 1 5.25 3h13.5A2.25 2.25 0 0 1 21 5.25v13.5A2.25 2.25 0 0 1 18.75 21H5.25A2.25 2.25 0 0 1 3 18.75Z" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold text-slate-900 dark:text-slate-100 hover:text-primary-600 dark:hover:text-primary-400 transition-colors leading-none mb-1">{{ $cat->name }}</span>
                                    <span class="text-[10px] text-slate-400 dark:text-slate-500 tracking-wider">/{{ $cat->slug }}</span>
                                </div>
                            </div>
                        </td>
                        <td data-label="Deskripsi" class="px-6 py-4 text-xs text-slate-500 dark:text-slate-400">
                            @if($cat->description)
                                <span class="max-w-xs truncate block" title="{{ $cat->description }}">{{ $cat->description }}</span>
                            @else
                                <span class="italic text-slate-500 dark:text-slate-600">Tidak ada deskripsi</span>
                            @endif
                        </td>
                        <td data-label="Produk" class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/50 dark:border-slate-700">
                                {{ $cat->products_count }}
                            </span>
                        </td>
                        <td data-label="Status" class="px-6 py-4">
                            <div class="flex items-center justify-center">
                                <form method="POST" action="{{ route('admin.categories.toggle-status', $cat) }}" class="inline-flex items-center gap-2.5">
                                    @csrf @method('PATCH')
                                    <label class="relative inline-flex items-center cursor-pointer group">
                                        <input type="checkbox" class="sr-only peer" {{ $cat->is_active ? 'checked' : '' }} onchange="this.closest('form').submit()">
                                        <div class="w-9 h-5 bg-slate-200 dark:bg-slate-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500 dark:peer-checked:bg-emerald-600 group-hover:shadow-sm"></div>
                                    </label>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $cat->is_active ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500' }}">
                                        {{ $cat->is_active ? 'Aktif' : 'Off' }}
                                    </span>
                                </form>
                            </div>
                        </td>
                        <td data-label="Aksi" class="px-6 py-4 text-right flex items-center justify-end gap-2">
                            <div class="relative group/tooltip inline-flex">
                                <button @click="showModal = true; editMode = true; form = { id: {{ $cat->id }}, slug: '{{ $cat->slug }}', name: '{{ $cat->name }}', description: '{{ addslashes($cat->description) }}', is_active: {{ $cat->is_active ? 'true' : 'false' }}, image: '{{ $cat->image }}' }; imagePreview = null;"
                                        class="p-2 text-primary-600 dark:text-primary-400 hover:text-primary-700 hover:bg-primary-50 dark:hover:bg-primary-950/60 rounded-xl transition-all" title="Edit">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                </button>
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-slate-900 text-white text-[10px] font-bold rounded-lg shadow-xl opacity-0 group-hover/tooltip:opacity-100 transition-all pointer-events-none whitespace-nowrap z-50">
                                    Edit Kategori
                                    <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></div>
                                </div>
                            </div>
                            <div class="relative group/tooltip inline-flex">
                                <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" class="inline" onsubmit="return confirm('Hapus kategori ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/60 rounded-xl transition-all" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                    </button>
                                </form>
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-slate-900 text-white text-[10px] font-bold rounded-lg shadow-xl opacity-0 group-hover/tooltip:opacity-100 transition-all pointer-events-none whitespace-nowrap z-50">
                                    Hapus Kategori
                                    <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-3 border-t border-slate-200 dark:border-slate-800">{{ $categories->links() }}</div>
