@section('page_title', 'AI Security Center')

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100 italic tracking-tight">AI SECURITY CENTER</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Monitoring & Mitigasi Ancaman AI Gegares Assistant</p>
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="clearLogs" class="px-4 py-2 text-xs font-bold uppercase tracking-wider text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all">
                Bersihkan Log (Low)
            </button>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="p-6 bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Total Kejadian</p>
                <p class="text-2xl font-black text-slate-900 dark:text-slate-100">{{ $stats['total_events'] }}</p>
            </div>
        </div>
        <div class="p-6 bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-red-50 flex items-center justify-center text-red-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" d="m12.75 3.03 5.668 8.606a.533.533 0 0 1-.144.748l-5.333 3.333a.533.533 0 0 1-.738-.137L6.5 6.96a.533.533 0 0 1 .144-.748l5.333-3.333a.533.533 0 0 1 .738.15Z"/><path stroke-linecap="round" d="M10.75 20.47 4.832 12.11a.533.533 0 0 1 .144-.748l5.333-3.333a.533.533 0 0 1 .738.137l5.669 8.606a.533.533 0 0 1-.144.748l-5.333 3.333a.533.533 0 0 1-.738-.137Z"/></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Kejadian Kritis</p>
                <p class="text-2xl font-black text-red-600 dark:text-red-400">{{ $stats['critical_events'] }}</p>
            </div>
        </div>
        <div class="p-6 bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-slate-900 flex items-center justify-center text-white">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.249-8.25-3.286zm0 13.036h.008v.008H12v-.008z"/></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">IP Diblokir</p>
                <p class="text-2xl font-black text-slate-900 dark:text-slate-100">{{ $stats['total_banned'] }}</p>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex items-center gap-6 border-b border-slate-100 dark:border-slate-800 overflow-x-auto no-scrollbar">
        <button wire:click="$set('activeTab', 'events')" class="pb-4 text-sm font-bold uppercase tracking-widest transition-all {{ $activeTab === 'events' ? 'text-primary-600 border-b-2 border-primary-600' : 'text-slate-400 hover:text-slate-600 border-b-2 border-transparent' }}">
            Event Logs
        </button>
        <button wire:click="$set('activeTab', 'banned_ips')" class="pb-4 text-sm font-bold uppercase tracking-widest transition-all {{ $activeTab === 'banned_ips' ? 'text-primary-600 border-b-2 border-primary-600' : 'text-slate-400 hover:text-slate-600 border-b-2 border-transparent' }}">
            IP Banned ({{ $stats['total_banned'] }})
        </button>
    </div>

    @if($activeTab === 'events')
        <div class="space-y-4">
            {{-- Filters --}}
            <div class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1">
                    <label class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1.5 block">Cari Payload/IP</label>
                    <input type="text" wire:model.live="search" placeholder="Contoh: 192.168..." class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm p-3 placeholder-slate-400 focus:ring-primary-500/20">
                </div>
                <div class="w-full md:w-48">
                    <label class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1.5 block">Severity</label>
                    <select wire:model.live="severity" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm p-3 focus:ring-primary-500/20">
                        <option value="">Semua</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>
            </div>

            {{-- Events Table --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50">
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500">Waktu</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500">Tipe & Severity</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500">IP & User</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500">Payload</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                        @forelse($events as $event)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <p class="text-[11px] font-bold text-slate-900 dark:text-slate-100">{{ $event->created_at->format('d M, H:i:s') }}</p>
                                    <p class="text-[9px] text-slate-400">{{ $event->created_at->diffForHumans() }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="block text-[11px] font-black uppercase tracking-tighter text-slate-900 dark:text-slate-100">{{ str_replace('_', ' ', $event->event_type) }}</span>
                                    @php
                                        $severityClasses = match($event->severity) {
                                            'critical' => 'bg-red-100 text-red-700',
                                            'high'     => 'bg-orange-100 text-orange-700',
                                            'medium'   => 'bg-amber-100 text-amber-700',
                                            default    => 'bg-slate-100 text-slate-600',
                                        };
                                    @endphp
                                    <span class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-bold uppercase mt-1 {{ $severityClasses }}">
                                        {{ $event->severity }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <p class="text-xs font-mono text-slate-600 dark:text-slate-300">{{ $event->ip_address }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $event->user?->name ?? 'Guest User' }}</p>
                                </td>
                                <td class="px-6 py-4 max-w-xs transition-all">
                                    <p class="text-[11px] text-slate-600 dark:text-slate-400 truncate hover:whitespace-normal cursor-help" title="{{ $event->payload }}">
                                        {{ $event->payload }}
                                    </p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button wire:click="banIp('{{ $event->ip_address }}', 'Manual ban from log: {{ $event->event_type }}')" class="text-[10px] font-bold uppercase text-red-600 hover:text-red-700 tracking-wider transition-colors">
                                        Ban IP
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic text-sm">Tidak ada kejadian keamanan tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $events->links() }}
            </div>
        </div>
    @else
        <div class="space-y-4">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50">
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500">IP Address</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500">Alasan</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500">Berakhir</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500">Dibuat</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                        @forelse($bannedIps as $banned)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap font-mono text-xs text-slate-900 dark:text-slate-100">
                                    {{ $banned->ip_address }}
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-600 dark:text-slate-400">
                                    {{ $banned->reason ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-[11px] font-bold {{ $banned->banned_until && $banned->banned_until->isPast() ? 'text-slate-400 line-through' : 'text-slate-900 dark:text-slate-100' }}">
                                        {{ $banned->banned_until ? $banned->banned_until->format('d M Y, H:i') : 'Permanen' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-[10px] text-slate-400">
                                    {{ $banned->created_at->format('d/m/y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button wire:click="unbanIp({{ $banned->id }})" class="text-[10px] font-bold uppercase text-emerald-600 hover:text-emerald-700 tracking-wider transition-colors">
                                        Lepas Blokir
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic text-sm">Tidak ada IP yang sedang diblokir.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
