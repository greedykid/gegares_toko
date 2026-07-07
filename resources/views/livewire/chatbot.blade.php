<div class="fixed z-50 flex flex-col items-end" 
     x-data="{ open: @entangle('isOpen'), showScrollBottom: false }" 
     x-init="
         $watch('open', value => {
             document.cookie = 'gegares_chat_open=' + (value ? '1' : '0') + '; path=/; max-age=31536000; SameSite=Lax';
         });
     "
     @click="if ($event.target.closest('a')) { open = false; document.cookie = 'gegares_chat_open=0; path=/; max-age=31536000; SameSite=Lax'; }"
     :class="open ? 'inset-0 sm:inset-auto sm:bottom-6 sm:right-6' : 'bottom-6 right-6'"
     :style="open ? '' : 'bottom: 1.5rem; right: 1.5rem;'"
     style="bottom: 1.5rem; right: 1.5rem;"
     id="gegares-chatbot">
    {{-- Backdrop Overlay on Mobile --}}
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="open = false" 
         class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-40 sm:hidden" 
         style="display: none;"></div>

    {{-- Chat Window --}}
    <div x-show="open"
         x-transition:enter="transition transform ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-10 scale-90 origin-bottom-right"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100 origin-bottom-right"
         x-transition:leave="transition transform ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100 origin-bottom-right"
         x-transition:leave-end="opacity-0 translate-y-10 scale-90 origin-bottom-right"
         class="fixed inset-0 sm:static z-50 sm:z-auto sm:mb-4 w-full sm:w-[400px] h-[100dvh] sm:h-[600px] bg-white dark:bg-slate-950 rounded-none sm:rounded-2xl shadow-2xl dark:shadow-[0_25px_60px_-15px_rgba(0,0,0,0.8)] border-0 sm:border border-slate-200/60 dark:border-slate-800/60 flex flex-col overflow-hidden relative"
         style="display: none;">
        
        {{-- ═══ Header ═══ --}}
        <div class="relative px-4 py-3.5 bg-primary-700 dark:bg-primary-900 border-b border-primary-800 dark:border-primary-950 flex items-center justify-between">
            {{-- Brand --}}
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="w-9 h-9 rounded-xl bg-primary-800 dark:bg-primary-950 flex items-center justify-center text-white">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="4" r="1.5" fill="currentColor" />
                            <line x1="12" y1="5.5" x2="12" y2="8" />
                            <rect x="4" y="8" width="16" height="12" rx="4" fill="currentColor" fill-opacity="0.15" />
                            <circle cx="9" cy="13" r="1.5" fill="currentColor" />
                            <circle cx="15" cy="13" r="1.5" fill="currentColor" />
                            <path d="M10 17c1 1 3 1 4 0" />
                            <rect x="2" y="12" width="2" height="4" rx="1" fill="currentColor" />
                            <rect x="20" y="12" width="2" height="4" rx="1" fill="currentColor" />
                        </svg>
                    </div>
                    {{-- Online indicator --}}
                    <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-400 border-2 border-primary-700 dark:border-primary-900 rounded-full"></div>
                </div>
                <div>
                    <h3 class="font-bold text-white text-sm tracking-tight leading-none">Gegares Assistant</h3>
                    <p class="text-[10px] text-primary-200 dark:text-primary-400 mt-1 font-medium">Online • Siap membantu</p>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-0.5">
                <button wire:click="clearChat" 
                        onclick="confirm('Hapus riwayat chat?') || event.stopImmediatePropagation()"
                        class="p-2 text-primary-100 hover:text-white hover:bg-primary-800 dark:hover:bg-primary-950 rounded-lg transition-all" 
                        aria-label="Hapus Riwayat Chat"
                        title="Hapus Riwayat Chat">
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                </button>
                <button @click="open = false" 
                        class="p-2 text-primary-100 hover:text-white hover:bg-primary-800 dark:hover:bg-primary-950 rounded-lg transition-all"
                        aria-label="Tutup Chatbot">
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- ═══ Messages ═══ --}}
        <div class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar bg-slate-50/50 dark:bg-slate-950" 
             id="chat-messages"
             @scroll="showScrollBottom = $el.scrollHeight - $el.scrollTop - $el.clientHeight > 150"
             x-init="if($wire.isOpen) { setTimeout(() => $el.scrollTo({ top: $el.scrollHeight, behavior: 'instant' }), 100) }"
             wire:poll.10s="checkRecentPaidOrders">
            @foreach($chatHistory as $chatIndex => $chat)
                <div class="flex {{ $chat['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                    @if($chat['role'] === 'assistant')
                        {{-- Bot Avatar --}}
                        <div class="w-7 h-7 rounded-lg bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center shrink-0 mr-2 mt-1 text-primary-600 dark:text-primary-400">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="4" r="1.5" fill="currentColor" />
                                <line x1="12" y1="5.5" x2="12" y2="8" />
                                <rect x="4" y="8" width="16" height="12" rx="4" fill="currentColor" fill-opacity="0.15" />
                                <circle cx="9" cy="13" r="1.5" fill="currentColor" />
                                <circle cx="15" cy="13" r="1.5" fill="currentColor" />
                                <path d="M10 17c1 1 3 1 4 0" />
                                <rect x="2" y="12" width="2" height="4" rx="1" fill="currentColor" />
                                <rect x="20" y="12" width="2" height="4" rx="1" fill="currentColor" />
                            </svg>
                        </div>
                    @endif

                    @if($chatIndex === 0 && $chat['role'] === 'assistant')
                        {{-- Premium Welcome Dashboard Onboarding --}}
                        <div class="relative max-w-[85%] bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 rounded-2xl rounded-tl-sm shadow-sm border border-slate-100 dark:border-slate-800/80 p-4 space-y-4">
                            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                                <h4 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-1.5">
                                    <span>👋</span> Selamat Datang!
                                </h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                                    Saya adalah asisten virtual Gegares. Berikut beberapa hal yang bisa saya bantu:
                                </p>
                            </div>

                            <div class="space-y-2.5">
                                <div class="flex gap-2.5 items-start text-xs leading-relaxed">
                                    <span class="p-1 rounded-lg bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 mt-0.5 shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.637 10.637Z"/></svg>
                                    </span>
                                    <div>
                                        <p class="font-bold text-slate-900 dark:text-white text-[11.5px]">Rekomendasi & Cari Produk</p>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Tanyakan info menu, harga, stok terbaru, atau minta rekomendasi jajanan terbaik.</p>
                                    </div>
                                </div>

                                <div class="flex gap-2.5 items-start text-xs leading-relaxed">
                                    <span class="p-1 rounded-lg bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 mt-0.5 shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z"/><path stroke-linecap="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z"/></svg>
                                    </span>
                                    <div>
                                        <p class="font-bold text-slate-900 dark:text-white text-[11.5px]">Fitur Snap & Buy (Foto & Beli)</p>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Unggah atau potret foto kue tradisional jajanan pasar, AI kami akan mencarinya secara instan.</p>
                                    </div>
                                </div>

                                <div class="flex gap-2.5 items-start text-xs leading-relaxed">
                                    <span class="p-1 rounded-lg bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 mt-0.5 shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg>
                                    </span>
                                    <div>
                                        <p class="font-bold text-slate-900 dark:text-white text-[11.5px]">Lacak Status Pesanan</p>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Masukkan info resi atau tanyakan detail status transaksi terbaru Anda secara otomatis.</p>
                                    </div>
                                </div>
                            </div>

                            @if(isset($chat['suggestions']) && count($chat['suggestions']) > 0)
                                <div class="pt-3 border-t border-slate-100 dark:border-slate-800/60">
                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase mb-2">Pilih Pertanyaan Cepat:</p>
                                    <div class="flex flex-col gap-1.5">
                                        @foreach($chat['suggestions'] as $suggestion)
                                            <button @click="$wire.sendTemplate('{{ $suggestion }}')"
                                                    class="w-full text-left px-3 py-2 bg-slate-50 dark:bg-slate-800/40 border border-slate-200/40 dark:border-slate-700/30 rounded-xl text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-primary-50 dark:hover:bg-primary-950/30 hover:border-primary-200 dark:hover:border-primary-800 transition-all active:scale-[0.99] flex items-center justify-between">
                                                <span>{{ $suggestion }}</span>
                                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <p class="text-[10px] mt-1 text-slate-400 dark:text-slate-500 text-right select-none">{{ $chat['time'] }}</p>
                        </div>
                    @else
                        <div class="relative group/msg max-w-[80%] {{ $chat['role'] === 'user' 
                            ? 'bg-primary-600 text-white rounded-2xl rounded-tr-sm shadow-sm' 
                            : 'bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 rounded-2xl rounded-tl-sm shadow-sm border border-slate-100 dark:border-slate-800/80' }} px-3.5 py-2.5">
                            
                            {{-- Copy Button --}}
                            @if($chat['role'] === 'assistant' && (!isset($chat['type']) || $chat['type'] !== 'image'))
                                <button @click="navigator.clipboard.writeText(`{{ addslashes($chat['content']) }}`); window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Pesan berhasil disalin!' } }))" 
                                        class="absolute -right-7 top-1.5 p-1 text-slate-300 hover:text-primary-500 dark:text-slate-600 dark:hover:text-primary-400 opacity-0 group-hover/msg:opacity-100 transition-all duration-200" 
                                        aria-label="Salin Pesan"
                                        title="Salin">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                                    </svg>
                                </button>
                            @endif

                            {{-- Content --}}
                            @if(isset($chat['type']) && $chat['type'] === 'image')
                                <div class="rounded-xl overflow-hidden">
                                    <img src="{{ $chat['content'] }}" class="w-full h-auto object-cover max-h-48 rounded-xl" alt="Gambar dari pengguna">
                                </div>
                            @else
                                <div class="text-sm leading-relaxed prose prose-sm dark:prose-invert">
                                    {!! \App\Services\SecurityService::renderMarkdown($chat['content']) !!}
                                </div>

                                {{-- Product Cards --}}
                                @if(isset($chat['products']) && count($chat['products']) > 0)
                                    <div class="mt-3 space-y-2">
                                        @foreach($chat['products'] as $prod)
                                            <div class="bg-slate-50 dark:bg-slate-800/60 rounded-xl p-2.5 border border-slate-100 dark:border-slate-700/50 transition-all hover:border-primary-300 dark:hover:border-primary-600 group/card">
                                                <div class="flex items-center gap-2.5">
                                                    @if($prod['image'])
                                                        <div class="w-12 h-12 rounded-lg overflow-hidden bg-white dark:bg-slate-700 shrink-0 shadow-sm">
                                                            <img src="{{ $prod['image'] }}" class="w-full h-full object-cover group-hover/card:scale-110 transition-transform duration-500" alt="{{ $prod['name'] }}">
                                                        </div>
                                                    @endif
                                                    <div class="flex-1 min-w-0">
                                                        <a href="{{ $prod['url'] }}" class="text-xs font-bold text-slate-900 dark:text-slate-100 truncate hover:text-primary-600 dark:hover:text-primary-400 transition-colors block">{{ $prod['name'] }}</a>
                                                        <div class="flex items-center gap-1.5 mt-0.5">
                                                            <p class="text-xs text-primary-600 dark:text-primary-400 font-extrabold">{{ $prod['price'] }}</p>
                                                            @if(($prod['stock'] ?? 0) <= 0)
                                                                <span class="px-1.5 py-0.5 rounded bg-red-50 dark:bg-red-900/30 text-red-500 dark:text-red-400 text-[10px] font-bold uppercase">Habis</span>
                                                            @elseif($prod['stock'] <= 10)
                                                                <span class="px-1.5 py-0.5 rounded bg-amber-50 dark:bg-amber-900/30 text-amber-500 dark:text-amber-400 text-[10px] font-bold uppercase">Sisa {{ $prod['stock'] }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-1.5 mt-2 pt-2 border-t border-slate-100 dark:border-slate-700/40">
                                                    @if(($prod['stock'] ?? 0) > 0)
                                                        <button @click="$wire.addToCart({{ $prod['id'] }})" 
                                                                class="flex-1 flex items-center justify-center gap-1.5 py-2.5 bg-primary-600 text-white text-xs font-bold rounded-lg hover:bg-primary-700 transition-all active:scale-95">
                                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                                                            + Keranjang
                                                        </button>
                                                    @else
                                                        <button disabled class="flex-1 flex items-center justify-center py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-400 dark:text-slate-500 text-xs font-bold rounded-lg cursor-not-allowed">Habis</button>
                                                    @endif
                                                    <button @click="$wire.toggleWishlist({{ $prod['id'] }})"
                                                            class="p-2.5 rounded-lg transition-all active:scale-90 {{ ($prod['inWishlist'] ?? false) ? 'bg-red-50 dark:bg-red-900/40 text-red-500' : 'bg-slate-100 dark:bg-slate-700 text-slate-400 hover:text-red-500' }}"
                                                            aria-label="{{ ($prod['inWishlist'] ?? false) ? 'Hapus dari Wishlist' : 'Tambah ke Wishlist' }}"
                                                            title="{{ ($prod['inWishlist'] ?? false) ? 'Hapus dari Wishlist' : 'Tambah ke Wishlist' }}">
                                                        @if($prod['inWishlist'] ?? false)
                                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="m11.645 20.91-.007-.003-.022-.012a15.247 15.247 0 0 1-.383-.218 25.18 25.18 0 0 1-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.579 5.579 0 0 1 12 5.052 5.579 5.579 0 0 1 16.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 0 1-4.244 3.17 15.247 15.247 0 0 1-.383.219l-.022.012-.007.004-.003.001Z"/></svg>
                                                        @else
                                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                                                        @endif
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Order Cards --}}
                                @if(isset($chat['orders']) && count($chat['orders']) > 0)
                                    <div class="mt-3 space-y-2">
                                        @foreach($chat['orders'] as $order)
                                            <div class="bg-slate-50 dark:bg-slate-800/60 rounded-xl p-3 border border-slate-100 dark:border-slate-700/50 transition-all hover:border-primary-300 dark:hover:border-primary-600">
                                                <div class="flex items-center justify-between mb-2.5 pb-2.5 border-b border-slate-100 dark:border-slate-700/40">
                                                    <div class="flex items-center gap-2">
                                                        <div class="p-1.5 rounded-lg bg-primary-50 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400">
                                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                                                        </div>
                                                        <span class="text-xs font-bold text-slate-900 dark:text-slate-100 font-mono">#{{ $order['number'] }}</span>
                                                    </div>
                                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-{{ $order['color'] }}-100 text-{{ $order['color'] }}-700 dark:bg-{{ $order['color'] }}-900/30 dark:text-{{ $order['color'] }}-400 uppercase tracking-wider">
                                                        {{ $order['status'] }}
                                                    </span>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <div>
                                                        <p class="text-[10px] text-slate-400 uppercase font-bold tracking-tight">Total</p>
                                                        <p class="text-sm font-black text-slate-900 dark:text-slate-100">{{ $order['total'] }}</p>
                                                    </div>
                                                    <a href="{{ $order['url'] }}"
                                                       class="px-3 py-1.5 bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 text-xs font-bold rounded-lg hover:bg-primary-600 dark:hover:bg-primary-500 hover:text-white transition-all active:scale-95">
                                                        Detail →
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Custom Action Buttons --}}
                                @if(isset($chat['buttons']) && count($chat['buttons']) > 0)
                                    <div class="mt-3 flex flex-col gap-1.5">
                                        @foreach($chat['buttons'] as $btn)
                                            @if(isset($btn['action']))
                                                <button wire:click="{{ $btn['action'] }}"
                                                        class="w-full text-center py-2.5 rounded-xl text-xs font-extrabold transition-all active:scale-[0.98] flex items-center justify-center gap-1.5 {{ ($btn['style'] ?? 'primary') === 'primary' ? 'bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm shadow-emerald-600/10' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                                                    @if(($btn['style'] ?? 'primary') === 'primary')
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg>
                                                    @else
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75 12 3m0 0 3.75 3.75M12 3v18"/></svg>
                                                    @endif
                                                    {{ $btn['label'] }}
                                                </button>
                                            @else
                                                <a href="{{ $btn['url'] }}"
                                                   class="w-full text-center py-2.5 rounded-xl text-xs font-extrabold transition-all active:scale-[0.98] flex items-center justify-center gap-1.5 {{ ($btn['style'] ?? 'primary') === 'primary' ? 'bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm shadow-emerald-600/10' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                                                    @if(($btn['style'] ?? 'primary') === 'primary')
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg>
                                                    @else
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75 12 3m0 0 3.75 3.75M12 3v18"/></svg>
                                                    @endif
                                                    {{ $btn['label'] }}
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            @endif

                            {{-- Suggestions --}}
                            @if(isset($chat['suggestions']) && count($chat['suggestions']) > 0)
                                <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800/60 flex flex-wrap gap-1.5">
                                    @foreach($chat['suggestions'] as $suggestion)
                                        <button @click="$wire.sendTemplate('{{ $suggestion }}')"
                                                class="px-2.5 py-1 bg-primary-50 dark:bg-primary-900/20 border border-primary-200/60 dark:border-primary-800/40 rounded-lg text-xs font-semibold text-primary-700 dark:text-primary-300 hover:bg-primary-100 dark:hover:bg-primary-900/40 hover:border-primary-300 transition-all active:scale-95">
                                            {{ $suggestion }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif

                            <p class="text-[10px] mt-1.5 {{ $chat['role'] === 'user' ? 'text-white/50' : 'text-slate-400 dark:text-slate-500' }} text-right select-none">{{ $chat['time'] }}</p>
                        </div>
                    @endif
                </div>
            @endforeach

            {{-- Typing Indicator --}}
            @if($isTyping)
                <div class="flex justify-start">
                    <div class="w-7 h-7 rounded-lg bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center shrink-0 mr-2 mt-1 text-primary-600 dark:text-primary-400">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="4" r="1.5" fill="currentColor" />
                            <line x1="12" y1="5.5" x2="12" y2="8" />
                            <rect x="4" y="8" width="16" height="12" rx="4" fill="currentColor" fill-opacity="0.15" />
                            <circle cx="9" cy="13" r="1.5" fill="currentColor" />
                            <circle cx="15" cy="13" r="1.5" fill="currentColor" />
                            <path d="M10 17c1 1 3 1 4 0" />
                            <rect x="2" y="12" width="2" height="4" rx="1" fill="currentColor" />
                            <rect x="20" y="12" width="2" height="4" rx="1" fill="currentColor" />
                        </svg>
                    </div>
                    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800/80 px-4 py-3 rounded-2xl rounded-tl-sm shadow-sm flex items-center gap-1"
                         aria-live="polite"
                         aria-label="Asisten sedang mengetik...">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary-400 chatbot-dot-1"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-primary-400 chatbot-dot-2"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-primary-400 chatbot-dot-3"></span>
                    </div>
                </div>
            @endif
        </div>

        {{-- Floating Scroll to Bottom Button --}}
        <div class="relative w-full">
            <button x-show="showScrollBottom"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                    @click="const container = document.getElementById('chat-messages'); if(container) { container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' }); }"
                    class="absolute -top-12 left-1/2 -translate-x-1/2 z-30 flex items-center gap-1.5 px-3 py-1.5 bg-white dark:bg-slate-900 text-primary-600 dark:text-primary-400 rounded-full shadow-lg border border-slate-200/80 dark:border-slate-800/80 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-primary-700 dark:hover:text-primary-300 transition-all active:scale-95 cursor-pointer"
                    style="display: none;">
                <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3" />
                </svg>
                <span>Lihat pesan baru</span>
            </button>
        </div>

        {{-- ═══ Footer / Input ═══ --}}
        <div class="p-3 bg-slate-50 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800/80">
            {{-- Honeypot --}}
            <div class="hidden" aria-hidden="true">
                <input type="text" wire:model="honeyPot" tabindex="-1" autocomplete="off">
            </div>

            <div class="flex items-center gap-2">
                {{-- Snap & Buy --}}
                <div class="relative">
                    <input type="file" wire:model="image" class="hidden" id="chatbot-image-upload" accept="image/*">
                    <label for="chatbot-image-upload" 
                           class="flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-slate-950 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-primary-600 dark:hover:text-primary-400 hover:border-primary-400 dark:hover:border-primary-700 transition-all cursor-pointer active:scale-95 shadow-sm" 
                           aria-label="Unggah foto jajanan (Snap & Buy)"
                           title="Snap & Buy">
                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15a2.25 2.25 0 0 0 2.25-2.25V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z"/><path stroke-linecap="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z"/></svg>
                    </label>
                </div>

                {{-- Input --}}
                <div class="flex-1 relative">
                    <input type="text"
                           wire:model.defer="message"
                           wire:keydown.enter="sendMessage"
                           placeholder="Ketik pesan..."
                           aria-label="Ketik pesan di sini"
                           class="w-full pl-4 pr-10 py-2.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-sm dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all shadow-sm">
                    <button @click="$wire.sendMessage()" 
                            class="absolute right-1.5 top-1/2 -translate-y-1/2 p-1.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-all active:scale-90"
                            aria-label="Kirim pesan">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" d="M6 12L3.269 3.126A59.768 59.768 0 0 1 21.485 12 59.57 59.57 0 0 1 3.27 20.876L5.999 12zm0 0h7.5"/></svg>
                    </button>
                </div>
            </div>
            <p class="text-[10px] text-slate-400 dark:text-slate-500 text-center mt-2.5 select-none font-medium">Powered by Gegares AI</p>
        </div>
    </div>

    <button @click="open = !open"
            :class="{ 'hidden! sm:flex!': open }"
            aria-label="Tanya AI Chatbot"
            class="group relative flex items-center justify-center w-14 h-14 rounded-2xl bg-primary-600 hover:bg-primary-700 text-white shadow-lg shadow-primary-600/30 hover:shadow-primary-600/50 hover:-translate-y-0.5 active:scale-95 transition-all duration-300 z-50">
        {{-- Notification Ring --}}
        <div class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 border-2 border-white dark:border-slate-900 rounded-full flex items-center justify-center transition-opacity duration-300" 
             x-show="!open" x-transition.opacity>
            <span class="absolute w-full h-full rounded-full bg-red-500 animate-ping opacity-50"></span>
        </div>
        
        <div class="relative w-6 h-6 flex items-center justify-center">
            <svg class="absolute inset-0 w-6 h-6 transition-all duration-500"
                 :class="open ? 'opacity-0 scale-50 rotate-90' : 'opacity-100 scale-100 rotate-0'"
                 fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 0 1 .778-.332 48.294 48.294 0 0 0 5.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
            </svg>
            <svg class="absolute inset-0 w-6 h-6 transition-all duration-500"
                 :class="open ? 'opacity-100 scale-100 rotate-0' : 'opacity-0 scale-50 -rotate-90'"
                 x-cloak fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </div>
    </button>
</div>

@script
<script>
    $wire.on('bot-replied', () => {
        setTimeout(() => {
            const container = document.getElementById('chat-messages');
            if (container) container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
        }, 100);
    });
    $wire.on('user-messaged', () => {
        setTimeout(() => {
            const container = document.getElementById('chat-messages');
            if (container) container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
        }, 50);
    });
    $wire.on('chat-opened', () => {
        setTimeout(() => {
            const container = document.getElementById('chat-messages');
            if (container) container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
        }, 100);
    });
</script>
@endscript
