<div class="relative" x-data="{ open: false }" x-init="$watch('open', value => { if (value) { $wire.markAsRead() } })" @click.away="open = false">
    <button @click="open = !open" aria-label="Notifikasi" class="relative inline-flex items-center justify-center w-10 h-10 rounded-xl text-slate-500 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-slate-100 dark:hover:bg-slate-900 transition-all duration-200 active:scale-90" title="Notifikasi">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
        </svg>
        @if($unreadCount > 0)
            <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] flex items-center justify-center px-1 text-[10px] font-bold bg-red-500 text-white rounded-full shadow-sm">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown Panel --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 origin-top-right -translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 origin-top-right translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100 origin-top-right translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 origin-top-right -translate-y-2"
         class="absolute -right-12 sm:right-0 mt-3 w-80 sm:w-[400px] bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xl rounded-2xl overflow-hidden z-50 text-left" style="display:none;">
        
        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200 dark:border-slate-800/80 bg-slate-50 dark:bg-slate-950">
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200">Notifikasi</h3>
            <span class="px-2.5 py-0.5 text-[10px] font-bold bg-red-500/10 text-red-600 dark:text-red-400 rounded-full">{{ $unreadCount }} Baru</span>
        </div>

        {{-- Items List --}}
        <div class="divide-y divide-slate-100 dark:divide-slate-800/40 max-h-96 overflow-y-auto custom-scrollbar">
            @forelse($notificationsList as $notification)
                <a href="{{ $notification['url'] }}" 
                   class="flex gap-4 p-4 hover:bg-slate-50/80 dark:hover:bg-slate-800/30 transition-all duration-200 group border-l-4 {{ $notification['is_unread'] ? 'border-primary-500 bg-primary-50/15 dark:bg-primary-950/10' : 'border-transparent' }}">
                    
                    {{-- Status Icon Container --}}
                    @if($notification['icon'] === 'primary')
                         <div class="w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-950/40 flex items-center justify-center shrink-0 border border-primary-100/50 dark:border-primary-900/30 text-primary-600 dark:text-primary-400">
                             <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                 {!! $notification['svg'] !!}
                             </svg>
                         </div>
                    @elseif($notification['icon'] === 'amber')
                         <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/40 flex items-center justify-center shrink-0 border border-amber-100/50 dark:border-amber-900/30 text-amber-500 dark:text-amber-400">
                             <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                 {!! $notification['svg'] !!}
                             </svg>
                         </div>
                    @elseif($notification['icon'] === 'emerald')
                         <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 flex items-center justify-center shrink-0 border border-emerald-100/50 dark:border-emerald-900/30 text-emerald-600 dark:text-emerald-400">
                             <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                 {!! $notification['svg'] !!}
                             </svg>
                         </div>
                    @elseif($notification['icon'] === 'indigo')
                         <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 flex items-center justify-center shrink-0 border border-indigo-100/50 dark:border-indigo-900/30 text-indigo-600 dark:text-indigo-400">
                             <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                 {!! $notification['svg'] !!}
                             </svg>
                         </div>
                    @elseif($notification['icon'] === 'red')
                         <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-900/20 flex items-center justify-center shrink-0 border border-red-100/30 text-red-500 dark:text-red-400">
                             <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                 {!! $notification['svg'] !!}
                             </svg>
                         </div>
                    @endif

                    {{-- Text Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <h4 class="text-xs font-bold text-slate-800 dark:text-slate-100 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors truncate">{{ $notification['title'] }}</h4>
                                @if($notification['is_unread'])
                                    <span class="w-1.5 h-1.5 bg-primary-500 dark:bg-primary-400 rounded-full shadow-sm shrink-0 animate-pulse"></span>
                                @endif
                            </div>
                            <span class="text-[9px] font-semibold text-slate-400 dark:text-slate-500 whitespace-nowrap">{{ $notification['time'] }}</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-300 mt-1 leading-relaxed font-medium">{!! $notification['description'] !!}</p>
                    </div>
                </a>
            @empty
                <div class="flex flex-col items-center justify-center py-12 px-4 text-center">
                    <div class="w-12 h-12 rounded-full bg-slate-50 dark:bg-slate-950 flex items-center justify-center text-slate-400 dark:text-slate-600 mb-3 border border-slate-100 dark:border-slate-800/50">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                    </div>
                    <p class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Tidak Ada Notifikasi</p>
                    <p class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 mt-1 leading-relaxed max-w-[240px]">Semua pemberitahuan Anda akan muncul di sini.</p>
                </div>
            @endforelse
        </div>

        {{-- Footer --}}
        <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-800/80 bg-slate-50 dark:bg-slate-950 text-center">
            <a href="{{ route('settings.index') }}#notifications" class="inline-flex items-center justify-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition-all duration-150">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.43l-1.003.828c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.99l1.005.831a1.125 1.125 0 0 1 .26 1.43l-1.297 2.247a1.125 1.125 0 0 1-1.37.491l-1.216-.456c-.356-.133-.751-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.43l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.75-.43-.99l-1.004-.831a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.645-.869L9.594 3.94ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />
                </svg>
                Atur Preferensi Notifikasi
            </a>
        </div>
    </div>
</div>
