@php
    $sort = $sort ?? request('sort', 'created_at');
    $dir = $dir ?? request('direction', 'desc');
    $pageIds = $pageIds ?? $reviews->pluck('id')->values();
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
                        Pengguna
                    </th>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                        Produk
                    </th>
                    <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                        Gambar
                    </th>
                    <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                        <a href="{{ sortUrl('rating', $sort, $dir) }}" class="inline-flex items-center justify-center gap-1 group hover:text-slate-600 dark:hover:text-slate-350 transition-colors mx-auto">
                            Rating
                            @if($sort === 'rating')
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
                        Komentar
                    </th>
                    <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                        <a href="{{ sortUrl('is_approved', $sort, $dir) }}" class="inline-flex items-center justify-center gap-1 group hover:text-slate-600 dark:hover:text-slate-350 transition-colors mx-auto">
                            Status
                            @if($sort === 'is_approved')
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
                @forelse($reviews as $review)
                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/20 transition-colors"
                    :class="isSelected({{ $review->id }}) && 'bg-primary-50/50 dark:bg-primary-950/20'">
                    <td class="admin-select-cell px-4 py-4 w-10">
                        <input type="checkbox" @change="toggle({{ $review->id }})" :checked="isSelected({{ $review->id }})"
                               class="w-4 h-4 align-middle rounded border-slate-300 dark:border-slate-600 text-primary-600 focus:ring-primary-500/30 cursor-pointer">
                    </td>
                    <td data-label="Pengguna" class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 shrink-0 flex items-center justify-center">
                                <div class="w-full h-full bg-primary-50 dark:bg-primary-950/40 flex items-center justify-center text-primary-600 dark:text-primary-400 font-bold text-xs">
                                    {{ substr($review->user->name ?? 'G', 0, 1) }}
                                </div>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold text-slate-900 dark:text-slate-100 leading-none mb-1">{{ $review->user->name ?? '-' }}</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 tracking-wider">{{ $review->created_at->format('d M Y') }}</span>
                            </div>
                        </div>
                    </td>
                    <td data-label="Produk" class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400 font-medium">
                        {{ $review->product->name ?? '-' }}
                    </td>
                    <td data-label="Gambar" class="px-6 py-4">
                        <div class="w-10 h-10 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 shadow-xs mx-auto shrink-0">
                            @if($review->image)
                                <button type="button" @click="reviewImage = '{{ asset('storage/'.$review->image) }}'" class="w-full h-full cursor-zoom-in hover:scale-105 transition-transform">
                                    <img src="{{ asset('storage/'.$review->image) }}" class="w-full h-full object-cover">
                                </button>
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-slate-50 dark:bg-slate-800">
                                    <svg class="w-5 h-5 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 18.75V5.25A2.25 2.25 0 0 1 5.25 3h13.5A2.25 2.25 0 0 1 21 5.25v13.5A2.25 2.25 0 0 1 18.75 21H5.25A2.25 2.25 0 0 1 3 18.75Z" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                    </td>
                    <td data-label="Rating" class="px-6 py-4">
                        <div class="flex items-center justify-center gap-0.5">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-3.5 h-3.5 {{ $i <= $review->rating ? 'text-amber-400 fill-amber-400' : 'text-slate-200 dark:text-slate-700 fill-slate-200 dark:fill-slate-700' }}" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endfor
                        </div>
                    </td>
                    <td data-label="Komentar" class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400 max-w-xs transition-colors truncate" title="{{ $review->comment ?? '-' }}">
                        {{ $review->comment ?? '-' }}
                    </td>
                    <td data-label="Status" class="px-6 py-4">
                        <div class="flex justify-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $review->is_approved ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-250/30 dark:border-emerald-900/30' : 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-250/30 dark:border-amber-900/30' }}">
                                {{ $review->is_approved ? 'Disetujui' : 'Pending' }}
                            </span>
                        </div>
                    </td>
                    <td data-label="Aksi" class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <div class="relative group/tooltip inline-flex">
                                <form method="POST" action="{{ route('admin.reviews.update', $review) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="is_approved" value="{{ $review->is_approved ? '0' : '1' }}">
                                    <button type="submit" class="p-2 {{ $review->is_approved ? 'text-amber-500 hover:bg-amber-50 dark:hover:bg-slate-800' : 'text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-slate-800' }} rounded-xl transition-all" title="{{ $review->is_approved ? 'Batalkan' : 'Setujui' }}">
                                        @if($review->is_approved)
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                        @endif
                                    </button>
                                </form>
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-slate-900 text-white text-[10px] font-bold rounded-lg shadow-xl opacity-0 group-hover/tooltip:opacity-100 transition-all pointer-events-none whitespace-nowrap z-50">
                                    {{ $review->is_approved ? 'Batalkan Persetujuan' : 'Setujui Ulasan' }}
                                    <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></div>
                                </div>
                            </div>

                            <div class="relative group/tooltip inline-flex">
                                <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" class="inline" onsubmit="return confirm('Hapus ulasan ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-slate-800 rounded-xl transition-all" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                    </button>
                                </form>
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-slate-900 text-white text-[10px] font-bold rounded-lg shadow-xl opacity-0 group-hover/tooltip:opacity-100 transition-all pointer-events-none whitespace-nowrap z-50">
                                    Hapus Ulasan
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
                                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 transition-colors">Ulasan Tidak Ditemukan</h3>
                                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1 max-w-xs mx-auto transition-colors">Tidak ada ulasan yang sesuai dengan filter atau kata kunci pencarian Anda untuk periode ini.</p>
                                <div class="mt-6">
                                    <a href="{{ route('admin.reviews.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-bold rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">
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
    <div class="px-6 py-3 border-t border-slate-200 dark:border-slate-800">{{ $reviews->links() }}</div>
