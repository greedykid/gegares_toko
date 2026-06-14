@extends('layouts.admin')
@section('page_title', 'Activity Logs')
@section('content')
<div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col h-full">
    {{-- Header --}}
    <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Rekam Jejak Aktivitas</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Daftar log aktivitas perubahan (CRUD) di dalam sistem.</p>
        </div>
    </div>

    {{-- Content --}}
    <div class="flex-1 overflow-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
            <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 font-semibold sticky top-0 z-10">
                <tr>
                    <th scope="col" class="px-6 py-4">Waktu</th>
                    <th scope="col" class="px-6 py-4">Aktor</th>
                    <th scope="col" class="px-6 py-4">Aktivitas</th>
                    <th scope="col" class="px-6 py-4">Target (ID)</th>
                    <th scope="col" class="px-6 py-4 text-right">Properti</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-600 dark:text-slate-300">
                @forelse($logs as $log)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="block font-medium text-slate-900 dark:text-slate-100">{{ $log->created_at->format('d M Y') }}</span>
                            <span class="text-xs text-slate-500">{{ $log->created_at->format('H:i') }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex flex-shrink-0 items-center justify-center font-bold text-xs uppercase">
                                    {{ substr($log->causer->name ?? 'S', 0, 1) }}
                                </div>
                                <span class="font-medium text-slate-900 dark:text-slate-100">{{ $log->causer->name ?? 'System' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $color = match($log->description) {
                                    'created' => 'text-emerald-600 bg-emerald-50 dark:text-emerald-400 dark:bg-emerald-900/40',
                                    'updated' => 'text-blue-600 bg-blue-50 dark:text-blue-400 dark:bg-blue-900/40',
                                    'deleted' => 'text-red-600 bg-red-50 dark:text-red-400 dark:bg-red-900/40',
                                    default => 'text-slate-600 bg-slate-50 dark:text-slate-400 dark:bg-slate-800'
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $color }} uppercase">
                                {{ $log->description }}
                            </span>
                        </td>
                        <td class="px-6 py-4 font-mono text-xs">
                            <span class="text-slate-400">{{ Str::afterLast($log->subject_type, '\\') }}</span>
                            @if($log->subject_id)
                             #{{ $log->subject_id }}
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button type="button" 
                                    x-data=""
                                    x-on:click="$dispatch('open-modal', 'log-{{ $log->id }}')"
                                    class="text-primary-600 hover:text-primary-700 font-semibold text-xs">Lihat Perubahan</button>
                            
                            {{-- Modal Properties --}}
                            <div x-data="{ show: false }"
                                 x-show="show"
                                 @open-modal.window="if ($event.detail === 'log-{{ $log->id }}') show = true"
                                 @close-modal.window="show = false"
                                 @keydown.escape.window="show = false"
                                 style="display: none;"
                                 class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                
                                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                    <div x-show="show" x-transition.opacity class="absolute inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity" @click="show = false" aria-hidden="true"></div>
                                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                    
                                    <div x-show="show" 
                                         x-transition:enter="ease-out duration-300" 
                                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                                         x-transition:leave="ease-in duration-200" 
                                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                                         class="relative z-10 inline-block align-bottom bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-slate-100 dark:border-slate-800">
                                        
                                        <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                                            <h3 class="text-lg font-bold text-slate-900 dark:text-white" id="modal-title">Detail Perubahan (#{{ $log->id }})</h3>
                                            <button @click="show = false" type="button" class="text-slate-400 hover:text-slate-500">
                                                <span class="sr-only">Close</span>
                                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                        </div>
                                        <div class="px-6 py-5 text-sm">
                                            @php
                                                // Spatie v5 uses attribute_changes for model attributes, 
                                                // while properties is used for other metadata.
                                                $changes = $log->attribute_changes; 
                                                $metadata = $log->properties;
                                                
                                                $hasOld = isset($changes['old']);
                                                $hasAttributes = isset($changes['attributes']);
                                                $hasMetadata = $metadata && count($metadata) > 0;
                                            @endphp

                                            @if(($changes && count($changes) > 0) || $hasMetadata)
                                                <div class="space-y-6">
                                                    @if($hasOld || $hasAttributes)
                                                        @if($hasOld)
                                                            <div class="space-y-2">
                                                                <div class="flex items-center gap-2 text-red-600 dark:text-red-400">
                                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                                    <p class="font-bold uppercase tracking-wider text-[11px]">Data Sebelumnya</p>
                                                                </div>
                                                                <div class="bg-slate-50 dark:bg-slate-950 p-4 rounded-xl ring-1 ring-slate-200 dark:ring-slate-800">
                                                                    <pre class="text-xs overflow-x-auto text-slate-700 dark:text-slate-300 font-mono">@json($changes['old'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)</pre>
                                                                </div>
                                                            </div>
                                                        @endif

                                                        @if($hasAttributes)
                                                            <div class="space-y-2">
                                                                <div class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400">
                                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                                    <p class="font-bold uppercase tracking-wider text-[11px]">{{ $hasOld ? 'Perubahan Baru' : 'Data Terdaftar' }}</p>
                                                                </div>
                                                                <div class="bg-slate-50 dark:bg-slate-950 p-4 rounded-xl ring-1 ring-slate-200 dark:ring-slate-800">
                                                                    <pre class="text-xs overflow-x-auto text-slate-700 dark:text-slate-300 font-mono">@json($changes['attributes'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)</pre>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if($hasMetadata)
                                                        <div class="space-y-2">
                                                            <div class="flex items-center gap-2 text-primary-600 dark:text-primary-400">
                                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                                <p class="font-bold uppercase tracking-wider text-[11px]">Metadata / Properti Kustom</p>
                                                            </div>
                                                            <div class="bg-slate-50 dark:bg-slate-950 p-4 rounded-xl ring-1 ring-slate-200 dark:ring-slate-800">
                                                                    <pre class="text-xs overflow-x-auto text-slate-700 dark:text-slate-300 font-mono">@json($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)</pre>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="py-12 flex flex-col items-center justify-center text-center">
                                                    <div class="w-16 h-16 rounded-2xl bg-slate-50 dark:bg-slate-800/50 flex items-center justify-center text-slate-300 dark:text-slate-700 mb-4">
                                                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.58 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.58 4 8 4s8-1.79 8-4M4 7c0-2.21 3.58-4 8-4s8 1.79 8 4m0 5c0 2.21-3.58 4-8 4s-8-1.79-8-4" /></svg>
                                                    </div>
                                                    <p class="text-slate-500 dark:text-slate-400 font-medium">Tidak ada rincian atribut yang berubah.</p>
                                                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Aktivitas {{ $log->description }} pada target terpantau berhasil dicatat.</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                            Dalam keadaan tenang. Belum ada aktivitas krusial.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($logs->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection
