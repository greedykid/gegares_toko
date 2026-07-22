<div class="flex flex-col lg:flex-row gap-3 lg:gap-8">
    {{-- Sidebar Navigation Tabs --}}
    <aside class="w-full lg:w-64 shrink-0">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800 p-2 lg:p-4 transition-all duration-300">
            <div class="flex flex-row lg:flex-col gap-1.5 overflow-x-auto lg:overflow-x-visible scrollbar-none w-full">
                <button wire:click="setTab('hero-cta')" type="button"
                    class="shrink-0 lg:w-full flex items-center gap-2 lg:gap-3 px-3.5 lg:px-4 py-2.5 lg:py-3 rounded-xl text-xs lg:text-sm font-bold uppercase tracking-wider transition-all cursor-pointer {{ $activeTab === 'hero-cta' ? 'bg-primary-500 text-white shadow-md shadow-primary-500/10' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-slate-900 dark:hover:text-white' }}">
                    <svg class="w-4.5 h-4.5 lg:w-5 lg:h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0h.5m-.5 0h-10.5m.5 0-1 3m9.5-3 1 3" />
                    </svg>
                    <span>Hero & CTA</span>
                </button>
                <button wire:click="setTab('faqs')" type="button"
                    class="shrink-0 lg:w-full flex items-center gap-2 lg:gap-3 px-3.5 lg:px-4 py-2.5 lg:py-3 rounded-xl text-xs lg:text-sm font-bold uppercase tracking-wider transition-all cursor-pointer {{ $activeTab === 'faqs' ? 'bg-primary-500 text-white shadow-md shadow-primary-500/10' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-slate-900 dark:hover:text-white' }}">
                    <svg class="w-4.5 h-4.5 lg:w-5 lg:h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                    </svg>
                    <span>Daftar FAQ</span>
                </button>
                <button wire:click="setTab('about')" type="button"
                    class="shrink-0 lg:w-full flex items-center gap-2 lg:gap-3 px-3.5 lg:px-4 py-2.5 lg:py-3 rounded-xl text-xs lg:text-sm font-bold uppercase tracking-wider transition-all cursor-pointer {{ $activeTab === 'about' ? 'bg-primary-500 text-white shadow-md shadow-primary-500/10' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-slate-900 dark:hover:text-white' }}">
                    <svg class="w-4.5 h-4.5 lg:w-5 lg:h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 00-18 9 9 0 000 18z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8h.01" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 12v4" />
                    </svg>
                    <span>Tentang Kami</span>
                </button>
                <button wire:click="setTab('contact')" type="button"
                    class="shrink-0 lg:w-full flex items-center gap-2 lg:gap-3 px-3.5 lg:px-4 py-2.5 lg:py-3 rounded-xl text-xs lg:text-sm font-bold uppercase tracking-wider transition-all cursor-pointer {{ $activeTab === 'contact' ? 'bg-primary-500 text-white shadow-md shadow-primary-500/10' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-slate-900 dark:hover:text-white' }}">
                    <svg class="w-4.5 h-4.5 lg:w-5 lg:h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                    </svg>
                    <span>Kontak</span>
                </button>
                <button wire:click="setTab('footer')" type="button"
                    class="shrink-0 lg:w-full flex items-center gap-2 lg:gap-3 px-3.5 lg:px-4 py-2.5 lg:py-3 rounded-xl text-xs lg:text-sm font-bold uppercase tracking-wider transition-all cursor-pointer {{ $activeTab === 'footer' ? 'bg-primary-500 text-white shadow-md shadow-primary-500/10' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-slate-900 dark:hover:text-white' }}">
                    <svg class="w-4.5 h-4.5 lg:w-5 lg:h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                    <span>Footer</span>
                </button>
                <button wire:click="setTab('integrations')" type="button"
                    class="shrink-0 lg:w-full flex items-center gap-2 lg:gap-3 px-3.5 lg:px-4 py-2.5 lg:py-3 rounded-xl text-xs lg:text-sm font-bold uppercase tracking-wider transition-all cursor-pointer {{ $activeTab === 'integrations' ? 'bg-primary-500 text-white shadow-md shadow-primary-500/10' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-slate-900 dark:hover:text-white' }}">
                    <svg class="w-4.5 h-4.5 lg:w-5 lg:h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
                    </svg>
                    <span>Integrasi</span>
                </button>
                <div class="w-2 shrink-0 lg:hidden"></div>
            </div>
        </div>
    </aside>

    {{-- Main Form Body --}}
    <div
        class="flex-1 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800 p-4 sm:p-8 transition-all duration-300">
        @if ($activeTab === 'integrations')
            {{-- Rendered outside the form above: nesting one form inside another is invalid HTML. --}}
            @livewire('admin.manage-integrations')
        @else
        <form wire:submit.prevent="save" class="space-y-6 sm:space-y-8">

            {{-- 1. TAB: HERO & CTA --}}
            @if ($activeTab === 'hero-cta')
                <div class="space-y-6">
                    <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h4 class="text-base font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wide">Hero & CTA Homepage</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Mengubah teks promosi utama dan banner ajakan bertindak di halaman Beranda.</p>
                    </div>

                    {{-- Hero Card Group --}}
                    <div class="bg-slate-50/40 dark:bg-slate-950/20 border border-slate-200/60 dark:border-slate-800/80 rounded-2xl p-4 sm:p-6 space-y-5">
                        <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-800/60">
                            <span class="p-1.5 rounded-lg bg-primary-50 dark:bg-primary-950 text-primary-600 dark:text-primary-400 border border-primary-100/40 dark:border-primary-900/30">
                                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                                </svg>
                            </span>
                            <span class="text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest">Section Hero Banner</span>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2 ml-1">Badge Promo Hero</label>
                                <input type="text" wire:model.defer="hero_badge"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-bold text-sm">
                                @error('hero_badge') <span class="text-red-500 text-xs mt-1 block font-bold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2 ml-1">Judul Utama Hero (Title)</label>
                                <input type="text" wire:model.defer="hero_title"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-bold text-sm">
                                @error('hero_title') <span class="text-red-500 text-xs mt-1 block font-bold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2 ml-1">Deskripsi Hero (Subtitle)</label>
                                <div class="relative" x-data="{ expanded: false }">
                                    <textarea wire:model.defer="hero_subtitle" :rows="expanded ? 8 : 3"
                                        class="w-full px-4 py-3 pr-10 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-sm leading-relaxed"></textarea>
                                    <button type="button" @click="expanded = !expanded"
                                        class="absolute bottom-3 right-3 p-1 rounded-lg bg-slate-100/80 hover:bg-slate-200 dark:bg-slate-800/80 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 active:scale-95 transition-all cursor-pointer"
                                        title="Expand/Collapse">
                                        <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                </div>
                                @error('hero_subtitle') <span class="text-red-500 text-xs mt-1 block font-bold">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- CTA Card Group --}}
                    <div class="bg-slate-50/40 dark:bg-slate-950/20 border border-slate-200/60 dark:border-slate-800/80 rounded-2xl p-4 sm:p-6 space-y-5">
                        <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-800/60">
                            <span class="p-1.5 rounded-lg bg-accent-50 dark:bg-accent-950 text-accent-600 dark:text-accent-400 border border-accent-100/40 dark:border-accent-900/30">
                                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                                </svg>
                            </span>
                            <span class="text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest">Section Call To Action (CTA)</span>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2 ml-1">Judul CTA Banner</label>
                                <input type="text" wire:model.defer="cta_title"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-bold text-sm">
                                @error('cta_title') <span class="text-red-500 text-xs mt-1 block font-bold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2 ml-1">Deskripsi CTA Banner</label>
                                <div class="relative" x-data="{ expanded: false }">
                                    <textarea wire:model.defer="cta_subtitle" :rows="expanded ? 8 : 3"
                                        class="w-full px-4 py-3 pr-10 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-sm leading-relaxed"></textarea>
                                    <button type="button" @click="expanded = !expanded"
                                        class="absolute bottom-3 right-3 p-1 rounded-lg bg-slate-100/80 hover:bg-slate-200 dark:bg-slate-800/80 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 active:scale-95 transition-all cursor-pointer"
                                        title="Expand/Collapse">
                                        <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                </div>
                                @error('cta_subtitle') <span class="text-red-500 text-xs mt-1 block font-bold">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- 2. TAB: FAQs LIST --}}
            @if ($activeTab === 'faqs')
                <div class="space-y-6">
                    <div
                        class="border-b border-slate-100 dark:border-slate-800 pb-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h4 class="text-base font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wide">
                                Daftar Tanya Jawab (FAQ)</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Tambahkan atau sesuaikan pertanyaan yang sering diajukan beserta jawabannya.</p>
                        </div>
                        <button type="button" wire:click="addFaq"
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-primary-100 dark:bg-primary-950/40 text-primary-700 dark:text-primary-400 text-xs font-bold rounded-xl border border-primary-200/20 hover:bg-primary-600 hover:text-white transition-all cursor-pointer w-full sm:w-auto">
                            <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Tambah FAQ Baru
                        </button>
                    </div>

                    <div class="space-y-4">
                        @foreach ($faq_items as $index => $faq)
                            <div
                                class="bg-slate-50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-4 sm:p-5 space-y-4 relative group transition-all duration-300">
                                
                                {{-- Card Header with Number and Delete Action --}}
                                <div class="flex items-center justify-between border-b border-slate-200/40 dark:border-slate-800/80 pb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-primary-100 dark:bg-primary-950/40 text-xs font-black text-primary-700 dark:text-primary-400 border border-primary-200/20">
                                            {{ $index + 1 }}
                                        </span>
                                        <h5 class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Pertanyaan FAQ</h5>
                                    </div>
                                    <button type="button" wire:click="removeFaq({{ $index }})"
                                        class="inline-flex items-center justify-center p-2 rounded-xl text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 active:scale-95 transition-all cursor-pointer lg:opacity-0 lg:group-hover:opacity-100 opacity-100"
                                        title="Hapus FAQ">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label
                                            class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2 ml-1">Pertanyaan</label>
                                        <input type="text" wire:model.defer="faq_items.{{ $index }}.q"
                                            placeholder="Contoh: Berapa lama waktu pengiriman?"
                                            class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-bold text-sm">
                                        @error('faq_items.' . $index . '.q') <span
                                            class="text-red-500 text-xs mt-1 block font-bold">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2 ml-1">Jawaban</label>
                                        <div class="relative" x-data="{ expanded: false }">
                                            <textarea wire:model.defer="faq_items.{{ $index }}.a" :rows="expanded ? 8 : 3"
                                                placeholder="Tuliskan jawaban yang rinci di sini..."
                                                class="w-full px-4 py-3 pr-10 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-sm leading-relaxed"></textarea>
                                            <button type="button" @click="expanded = !expanded"
                                                class="absolute bottom-3 right-3 p-1 rounded-lg bg-slate-100/80 hover:bg-slate-200 dark:bg-slate-800/80 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 active:scale-95 transition-all cursor-pointer"
                                                title="Expand/Collapse">
                                                <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                        </div>
                                        @error('faq_items.' . $index . '.a') <span
                                            class="text-red-500 text-xs mt-1 block font-bold">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 3. TAB: ABOUT PAGE --}}
            @if ($activeTab === 'about')
                <div class="space-y-6">
                    <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h4 class="text-base font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wide">Konten Halaman Tentang Kami</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Konfigurasi isian paragraf narasi, visi, misi, dan galeri proses produksi jajanan.</p>
                    </div>

                    {{-- Narrative Copy Card --}}
                    <div class="bg-slate-50/40 dark:bg-slate-950/20 border border-slate-200/60 dark:border-slate-800/80 rounded-2xl p-4 sm:p-6 space-y-5">
                        <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-800/60">
                            <span class="p-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 border border-emerald-100/40 dark:border-emerald-900/30">
                                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 00-18 9 9 0 000 18z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8h.01" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 12v4" />
                                </svg>
                            </span>
                            <span class="text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest">Narasi Utama & Visi</span>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label
                                    class="block text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2.5 ml-1">Judul Utama Halaman</label>
                                <input type="text" wire:model.defer="about_title"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-bold text-sm">
                                @error('about_title') <span
                                    class="text-red-500 text-xs mt-1 block font-bold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2.5 ml-1">Sub-judul Halaman</label>
                                <div class="relative" x-data="{ expanded: false }">
                                    <textarea wire:model.defer="about_subtitle" :rows="expanded ? 8 : 2"
                                        class="w-full px-4 py-3 pr-10 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-bold text-sm"></textarea>
                                    <button type="button" @click="expanded = !expanded"
                                        class="absolute bottom-3 right-3 p-1 rounded-lg bg-slate-100/80 hover:bg-slate-200 dark:bg-slate-800/80 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 active:scale-95 transition-all cursor-pointer"
                                        title="Expand/Collapse">
                                        <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                </div>
                                @error('about_subtitle') <span
                                    class="text-red-500 text-xs mt-1 block font-bold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2.5 ml-1">Judul Kisah/Story</label>
                                <input type="text" wire:model.defer="about_story_title"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-bold text-sm">
                                @error('about_story_title') <span
                                    class="text-red-500 text-xs mt-1 block font-bold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2.5 ml-1">Konten Narasi Kisah/Story (Gunakan enter/paragraf baru)</label>
                                <div class="relative" x-data="{ expanded: false }">
                                    <textarea wire:model.defer="about_story_content" :rows="expanded ? 14 : 6"
                                        class="w-full px-4 py-3 pr-10 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-sm leading-relaxed"></textarea>
                                    <button type="button" @click="expanded = !expanded"
                                        class="absolute bottom-3 right-3 p-1 rounded-lg bg-slate-100/80 hover:bg-slate-200 dark:bg-slate-800/80 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 active:scale-95 transition-all cursor-pointer"
                                        title="Expand/Collapse">
                                        <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                </div>
                                @error('about_story_content') <span
                                    class="text-red-500 text-xs mt-1 block font-bold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2.5 ml-1">Visi Perusahaan</label>
                                <div class="relative" x-data="{ expanded: false }">
                                    <textarea wire:model.defer="about_vision" :rows="expanded ? 8 : 3"
                                        class="w-full px-4 py-3 pr-10 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-sm leading-relaxed"></textarea>
                                    <button type="button" @click="expanded = !expanded"
                                        class="absolute bottom-3 right-3 p-1 rounded-lg bg-slate-100/80 hover:bg-slate-200 dark:bg-slate-800/80 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 active:scale-95 transition-all cursor-pointer"
                                        title="Expand/Collapse">
                                        <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                </div>
                                @error('about_vision') <span
                                    class="text-red-500 text-xs mt-1 block font-bold">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Misi Section Card --}}
                    <div class="bg-slate-50/40 dark:bg-slate-950/20 border border-slate-200/60 dark:border-slate-800/80 rounded-2xl p-4 sm:p-6 space-y-4 font-sans">
                        <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800/60 mb-2">
                            <div class="flex items-center gap-2">
                                <span class="p-1.5 rounded-lg bg-teal-50 dark:bg-teal-950 text-teal-605 dark:text-teal-400 border border-teal-100/40 dark:border-teal-900/30">
                                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                    </svg>
                                </span>
                                <span class="text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest">Butir-Butir Misi</span>
                            </div>
                            <button type="button" wire:click="addMission"
                                class="inline-flex items-center justify-center gap-1 px-3 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-lg hover:bg-primary-600 hover:text-white transition-all cursor-pointer">
                                + Tambah Misi
                            </button>
                        </div>

                        <div class="space-y-3">
                            @foreach ($about_mission as $index => $mission)
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-xs font-bold text-slate-400 dark:text-slate-655 w-5 shrink-0">{{ $index + 1 }}.</span>
                                    <input type="text" wire:model.defer="about_mission.{{ $index }}"
                                        placeholder="Contoh: Menjaga kebersihan dan rasa produk"
                                        class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-bold text-sm">
                                    <button type="button" wire:click="removeMission({{ $index }})"
                                        class="p-2.5 rounded-xl text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 active:scale-95 transition-all cursor-pointer shrink-0">
                                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                @error('about_mission.' . $index) <span
                                    class="text-red-500 text-xs mt-1 block font-bold ml-6">{{ $message }}</span> @enderror
                            @endforeach
                            
                            @if (empty($about_mission))
                                <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 italic text-center py-2">Belum ada misi yang ditambahkan.</p>
                            @endif
                        </div>
                    </div>

                    {{-- Gallery Section Card --}}
                    <div class="bg-slate-50/40 dark:bg-slate-950/20 border border-slate-200/60 dark:border-slate-800/80 rounded-2xl p-4 sm:p-6 space-y-4">
                        <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-800/60">
                            <span class="p-1.5 rounded-lg bg-amber-50 dark:bg-amber-950 text-amber-600 dark:text-amber-400 border border-amber-100/40 dark:border-amber-900/30">
                                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 18.75V5.25A2.25 2.25 0 0 1 5.25 3h13.5A2.25 2.25 0 0 1 21 5.25v13.5A2.25 2.25 0 0 1 18.75 21H5.25A2.25 2.25 0 0 1 3 18.75Z" />
                                </svg>
                            </span>
                            <span class="text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest">Galeri Proses Produksi</span>
                        </div>

                        {{-- Gallery Heading Texts --}}
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2.5 ml-1">Badge Galeri</label>
                                    <input type="text" wire:model.defer="about_gallery_badge" placeholder="Galeri Kegiatan"
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-bold text-sm">
                                    @error('about_gallery_badge') <span class="text-red-500 text-xs mt-1 block font-bold">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2.5 ml-1">Judul Galeri</label>
                                    <input type="text" wire:model.defer="about_gallery_title" placeholder="Proses Produksi Kami"
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-bold text-sm">
                                    @error('about_gallery_title') <span class="text-red-500 text-xs mt-1 block font-bold">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2.5 ml-1">Deskripsi Galeri</label>
                                <textarea wire:model.defer="about_gallery_subtitle" rows="2" placeholder="Melihat langsung bagaimana jajanan pasar legendaris kami dibuat..."
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-sm leading-relaxed"></textarea>
                                @error('about_gallery_subtitle') <span class="text-red-500 text-xs mt-1 block font-bold">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Existing Gallery Images --}}
                        @if (!empty($about_gallery))
                            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 gap-3 sm:gap-4 mb-4">
                                @foreach ($about_gallery as $index => $path)
                                    <div
                                        class="relative group aspect-square rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800 shadow-xs bg-slate-100 dark:bg-slate-950">
                                        <img src="{{ asset('storage/' . $path) }}" class="w-full h-full object-cover">
                                        
                                        {{-- Delete Button overlay - visible on mobile, hover on desktop --}}
                                        <div
                                            class="absolute inset-0 bg-black/30 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                            <button type="button" wire:click="removeGalleryImage({{ $index }})"
                                                class="absolute top-2 right-2 lg:relative lg:top-auto lg:right-auto p-2 rounded-xl bg-red-600/90 hover:bg-red-700 text-white font-bold transition-all transform hover:scale-105 active:scale-95 shadow-md cursor-pointer"
                                                title="Hapus Gambar">
                                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 italic pb-2">Belum ada gambar galeri yang diupload.</p>
                        @endif

                        {{-- Multi File Upload Drag Area --}}
                        <div
                            class="relative w-full border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-2xl p-5 sm:p-6 bg-slate-50/20 dark:bg-slate-900/10 flex flex-col items-center justify-center text-center transition-colors">
                            <input type="file" wire:model="new_gallery_images" multiple accept="image/*"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                            <svg class="w-8 h-8 text-slate-400 dark:text-slate-655 mb-2.5" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <span class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200">Pilih atau Seret Foto Proses Produksi</span>
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">Mendukung format JPG, PNG, WebP (Maks. 2MB per gambar)</span>
                        </div>
                        @error('new_gallery_images.*') <span
                            class="text-red-500 text-xs mt-1 block font-bold">{{ $message }}</span> @enderror

                        {{-- Uploading Previews --}}
                        @if (!empty($new_gallery_images))
                            <div
                                class="mt-4 p-4 bg-slate-50/50 dark:bg-slate-950/40 border border-slate-200 dark:border-slate-800 rounded-xl space-y-2">
                                <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Gambar Baru (Menunggu disimpan):</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($new_gallery_images as $image)
                                        <div
                                            class="relative w-12 h-12 sm:w-14 sm:h-14 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-800 bg-white shadow-xs">
                                            <img src="{{ $image->temporaryUrl() }}" class="w-full h-full object-cover">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- 4. TAB: CONTACTS --}}
            @if ($activeTab === 'contact')
                <div class="space-y-6">
                    <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h4 class="text-base font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wide">Informasi Kontak & Jam Kerja</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Mengubah rincian email, whatsapp, telepon, dan jam buka operasional toko.</p>
                    </div>

                    {{-- Contact Cards Wrapper --}}
                    <div class="bg-slate-50/40 dark:bg-slate-950/20 border border-slate-200/60 dark:border-slate-800/80 rounded-2xl p-4 sm:p-6 space-y-5">
                        <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-800/60">
                            <span class="p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950 text-indigo-605 dark:text-indigo-400 border border-indigo-100/40 dark:border-indigo-900/30">
                                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                                </svg>
                            </span>
                            <span class="text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest">Detail Kontak & Jam Buka</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label
                                    class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2 ml-1">Nomor WhatsApp Resmi (misal: 62812...)</label>
                                <input type="text" wire:model.defer="contact_whatsapp"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-bold text-sm">
                                @error('contact_whatsapp') <span
                                    class="text-red-500 text-xs mt-1 block font-bold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label
                                    class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2 ml-1">Nomor Telepon Toko (Untuk Tampilan Kontak)</label>
                                <input type="text" wire:model.defer="contact_phone"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-bold text-sm">
                                @error('contact_phone') <span
                                    class="text-red-500 text-xs mt-1 block font-bold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label
                                    class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2 ml-1">Email Resmi</label>
                                <input type="email" wire:model.defer="contact_email"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-bold text-sm">
                                @error('contact_email') <span
                                    class="text-red-500 text-xs mt-1 block font-bold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label
                                    class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2 ml-1">Jam Operasional / Informasi Kerja</label>
                                <div class="relative" x-data="{ expanded: false }">
                                    <textarea wire:model.defer="contact_hours" :rows="expanded ? 8 : 3"
                                        class="w-full px-4 py-3 pr-10 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-sm leading-relaxed"></textarea>
                                    <button type="button" @click="expanded = !expanded"
                                        class="absolute bottom-3 right-3 p-1 rounded-lg bg-slate-100/80 hover:bg-slate-200 dark:bg-slate-800/80 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 active:scale-95 transition-all cursor-pointer"
                                        title="Expand/Collapse">
                                        <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                </div>
                                @error('contact_hours') <span
                                    class="text-red-500 text-xs mt-1 block font-bold">{{ $message }}</span> @enderror
                            </div>

                            {{-- The text above is what visitors read; these two are what the
                                 shipping logic acts on. An order placed outside them waits for
                                 opening before a courier is called, because nobody is here to
                                 hand the parcel over. --}}
                            <div>
                                <label
                                    class="block text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2 ml-1">Jam Buka Toko (Penjemputan Kurir)</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 mb-1 ml-1">Buka</span>
                                        <input type="time" wire:model.defer="opens_at"
                                            class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-sm">
                                        @error('opens_at') <span
                                            class="text-red-500 text-xs mt-1 block font-bold">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 mb-1 ml-1">Tutup</span>
                                        <input type="time" wire:model.defer="closes_at"
                                            class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-sm">
                                        @error('closes_at') <span
                                            class="text-red-500 text-xs mt-1 block font-bold">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 ml-1 leading-snug">
                                    Pesanan di luar jam ini tetap diterima, tetapi kurir baru dipanggil saat toko buka kembali.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- 5. TAB: FOOTER --}}
            @if ($activeTab === 'footer')
                <div class="space-y-6">
                    <div>
                        <h4 class="text-base font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wide">Pengaturan Footer</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Mengatur logo metode pembayaran yang tampil di bagian bawah footer situs.</p>
                    </div>

                    {{-- Payment Method Logos Card (Footer) --}}
                    <div class="bg-slate-50/40 dark:bg-slate-950/20 border border-slate-200/60 dark:border-slate-800/80 rounded-2xl p-4 sm:p-6 space-y-4">
                        <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-800/60">
                            <span class="p-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 border border-emerald-100/40 dark:border-emerald-900/30">
                                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                </svg>
                            </span>
                            <span class="text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest">Logo Metode Pembayaran (Footer)</span>
                        </div>
                        <p class="text-xs font-semibold text-slate-400 dark:text-slate-500">Logo yang tampil di bagian bawah footer situs (misal: BCA, GoPay, QRIS). Disarankan format PNG transparan. Kosongkan untuk memakai logo bawaan.</p>

                        {{-- Existing Logos --}}
                        @if (!empty($payment_logos))
                            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 gap-3 sm:gap-4">
                                @foreach ($payment_logos as $index => $path)
                                    <div class="relative group aspect-video rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800 shadow-xs bg-white dark:bg-slate-900 flex items-center justify-center p-3">
                                        <img src="{{ asset('storage/' . $path) }}" class="max-w-full max-h-full object-contain" onerror="this.style.display='none'">
                                        <div class="absolute inset-0 bg-black/30 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                            <button type="button" wire:click="removePaymentLogo({{ $index }})"
                                                class="absolute top-2 right-2 lg:relative lg:top-auto lg:right-auto p-2 rounded-xl bg-red-600/90 hover:bg-red-700 text-white transition-all transform hover:scale-105 active:scale-95 shadow-md cursor-pointer"
                                                title="Hapus Logo">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 italic">Belum ada logo diupload — footer memakai logo bawaan (BCA, GoPay, QRIS).</p>
                        @endif

                        {{-- Upload Area --}}
                        <div class="relative w-full border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-2xl p-5 sm:p-6 bg-slate-50/20 dark:bg-slate-900/10 flex flex-col items-center justify-center text-center transition-colors">
                            <input type="file" wire:model="new_payment_logos" multiple accept="image/*"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                            <svg class="w-8 h-8 text-slate-400 dark:text-slate-600 mb-2.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <span class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200">Pilih atau Seret Logo Pembayaran</span>
                            <span class="text-[10px] font-semibold text-slate-400 dark:text-slate-500 mt-1">PNG/JPG, maksimal 1MB per file</span>

                            <div wire:loading wire:target="new_payment_logos" class="mt-3 text-[11px] font-bold text-primary-600 dark:text-primary-400">Mengunggah...</div>
                        </div>
                        @error('new_payment_logos.*') <span class="text-red-500 text-xs mt-1 block font-bold">{{ $message }}</span> @enderror
                    </div>
                </div>
            @endif

            {{-- Form Submit Area --}}
            <div class="border-t border-slate-100 dark:border-slate-800 pt-6 flex items-center justify-end">
                <button type="submit" wire:loading.attr="disabled"
                    class="w-full sm:w-auto px-6 py-3.5 bg-primary-600 dark:bg-primary-500 text-white text-sm font-black rounded-xl hover:bg-primary-700 active:scale-95 transition-all shadow-md shadow-primary-500/10 cursor-pointer flex items-center justify-center gap-2">
                    <span wire:loading.remove>Simpan Pengaturan</span>
                    <span wire:loading class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Menyimpan...
                    </span>
                </button>
            </div>
        </form>
        @endif
    </div>
</div>