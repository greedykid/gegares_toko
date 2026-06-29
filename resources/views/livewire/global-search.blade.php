<div x-data="{ open: false }"
     @open-search.window="open = true; $nextTick(() => $refs.searchInput.focus())"
     @keydown.window.escape="open = false">

    {{-- Backdrop --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-60 bg-slate-900/60 backdrop-blur-md"
         style="display: none;"></div>

    {{-- Modal --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-8 sm:translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-8 sm:translate-y-4"
         class="fixed inset-x-0 top-16 sm:top-24 z-70 max-w-2xl mx-auto px-4 sm:px-0"
         style="display: none;">

        <div class="bg-white/95 dark:bg-slate-900/90 backdrop-blur-2xl rounded-4xl shadow-2xl ring-1 ring-slate-200/50 dark:ring-slate-800/80 overflow-hidden transition-colors duration-300" @click.away="open = false">
            {{-- Search Input Bar --}}
            <div class="relative flex items-center px-4 py-2 border-b border-slate-100/80 dark:border-slate-800/80">
                <svg class="w-6 h-6 text-primary-500 absolute left-6 border-transparent" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.35-4.35"/></svg>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       x-ref="searchInput"
                       placeholder="Cari jajanan pasar..."
                       class="w-full pl-12 pr-20 py-4 border-0 focus:ring-0 focus:ring-transparent focus:outline-none focus:border-transparent text-xl font-black text-slate-900 dark:text-white placeholder-slate-300 dark:placeholder-slate-600 bg-transparent tracking-tight">
                
                <div wire:loading class="absolute right-16">
                    <svg class="animate-spin h-6 w-6 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <div class="absolute right-4">
                    <button @click="open = false" class="p-2 text-slate-400 hover:text-slate-900 dark:hover:text-white rounded-2xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-all active:scale-95">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            {{-- Results --}}
            <div class="max-h-[60vh] overflow-y-auto w-full custom-scrollbar">
                @if(strlen($search) >= 2)
                    @if(count($results) > 0 || count($categoryResults) > 0)
                        <div class="p-4 sm:p-5 space-y-6">
                            {{-- Typo-tolerant hint --}}
                            @if($isFuzzy)
                                <div class="flex items-start gap-3 px-4 py-3 rounded-2xl bg-amber-50 dark:bg-amber-900/15 border border-amber-100 dark:border-amber-900/40">
                                    <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/></svg>
                                    <p class="text-xs font-bold text-amber-700 dark:text-amber-400 leading-relaxed">
                                        Tidak ada hasil persis untuk <span class="px-1 py-0.5 rounded bg-amber-100 dark:bg-amber-900/40">"{{ $search }}"</span>. Menampilkan jajanan dengan nama yang mirip:
                                    </p>
                                </div>
                            @endif

                            {{-- Categories Section --}}
                            @if(count($categoryResults) > 0)
                                <div>
                                    <h3 class="px-2 mb-3 text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest transition-colors">Kategori</h3>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        @foreach($categoryResults as $category)
                                            <a href="{{ route('products.index', ['category' => $category->slug]) }}" 
                                               class="flex items-center gap-4 px-4 py-3 rounded-2xl bg-slate-50/50 hover:bg-primary-50 dark:bg-slate-800/30 dark:hover:bg-primary-900/20 group transition-all duration-300">
                                                <div class="w-10 h-10 rounded-xl bg-white dark:bg-slate-800 text-slate-400 dark:text-slate-500 flex items-center justify-center group-hover:bg-primary-600 group-hover:text-white group-hover:scale-110 shadow-sm transition-all duration-300">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.659A2.25 2.25 0 0 0 9.568 3Z"/><path stroke-linecap="round" d="M6 6h.008v.008H6V6Z"/></svg>
                                                </div>
                                                <span class="text-sm font-bold text-slate-700 dark:text-slate-300 group-hover:text-primary-700 dark:group-hover:text-primary-400 transition-colors">{{ $category->name }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Products Section --}}
                            @if(count($results) > 0)
                                <div>
                                    <h3 class="px-2 mb-3 text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest transition-colors">Produk Jajanan</h3>
                                    <div class="space-y-2">
                                        @foreach($results as $product)
                                            <a href="{{ route('products.show', $product) }}" class="flex items-center gap-5 p-3 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-800/80 group transition-all duration-300">
                                                <div class="relative w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 overflow-hidden shrink-0 shadow-sm ring-1 ring-slate-200/50 dark:ring-slate-700/50 transition-colors">
                                                    @if($product->image)
                                                        <img src="{{ asset('storage/' . $product->image) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                                    @else
                                                        <div class="w-full h-full flex items-center justify-center text-slate-300 dark:text-slate-600">
                                                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="text-base font-extrabold text-slate-900 dark:text-white truncate group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">{{ $product->name }}</h4>
                                                    <div class="flex items-center gap-3 mt-1 text-sm font-bold">
                                                        <p class="text-primary-600 dark:text-primary-400 transition-colors">{{ $product->formatted_price }}</p>
                                                        @if($product->rating_count > 0)
                                                            <div class="w-1 h-1 rounded-full bg-slate-300 dark:bg-slate-600"></div>
                                                            <span class="text-slate-500 flex items-center gap-1 transition-colors">
                                                                <svg class="w-4 h-4 text-amber-500 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                                                {{ $product->rating_avg }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="w-10 h-10 rounded-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-center text-slate-400 opacity-0 group-hover:opacity-100 -translate-x-4 group-hover:translate-x-0 shadow-sm transition-all duration-300">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                    <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800/80">
                                        <a href="{{ route('products.index', ['search' => $search]) }}" class="block px-4 py-4 text-sm font-black text-center text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/10 hover:bg-primary-100 dark:hover:bg-primary-900/30 rounded-2xl transition-all">
                                            Lihat Semua Hasil Pencarian →
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="p-16 flex flex-col items-center justify-center text-center">
                            <div class="w-20 h-20 bg-slate-50 dark:bg-slate-800/50 rounded-3xl flex items-center justify-center mb-6 transition-colors shadow-inner shadow-slate-200/50 dark:shadow-none">
                                <svg class="w-10 h-10 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                            </div>
                            <h3 class="text-xl font-extrabold text-slate-900 dark:text-white transition-colors">Pencarian Tidak Ditemukan</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-2 font-medium max-w-[280px]">Kami tidak dapat menemukan jajanan pasar dengan kata kunci <span class="text-slate-900 dark:text-white font-bold bg-slate-100 dark:bg-slate-800 px-1 py-0.5 rounded">"{{ $search }}"</span>.</p>
                            <button @click="$wire.set('search', '')" class="mt-6 px-6 py-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-bold transition-colors">Hapus Pencarian</button>
                        </div>
                    @endif
                @else
                    <div class="p-16 flex flex-col items-center justify-center text-center">
                        <div class="relative w-20 h-20 bg-primary-50 dark:bg-primary-900/20 rounded-3xl flex items-center justify-center mb-6 transition-colors group">
                            <svg class="w-10 h-10 text-primary-500 dark:text-primary-400 relative z-10" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.35-4.35"/></svg>
                        </div>
                        <h3 class="text-xl font-extrabold text-slate-900 dark:text-white transition-colors">Eksplorasi Jajanan Pasar</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-2 font-medium max-w-[300px] leading-relaxed transition-colors">Mulai mengetik untuk menemukan dan memesan jajanan pasar berkualitas premium sekarang juga.</p>
                    </div>
                @endif
            </div>
            
            {{-- Footer Command Palette Tip --}}
            <div class="px-5 py-3.5 bg-slate-50/80 dark:bg-slate-800/30 border-t border-slate-100/80 dark:border-slate-800/80 flex items-center justify-center sm:justify-between transition-colors backdrop-blur-md">
                <span class="hidden sm:flex text-[11px] text-slate-500 dark:text-slate-400 font-bold flex items-center gap-1.5">Tekan <kbd class="px-2 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-600 dark:text-slate-300 shadow-sm">ESC</kbd> untuk tutup</span>
                <span class="text-[10px] text-slate-400 font-bold tracking-[0.2em] uppercase mx-auto sm:mx-0">Gegares Search</span>
            </div>
        </div>
    </div>
</div>

