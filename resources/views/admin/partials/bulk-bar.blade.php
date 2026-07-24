@props(['route', 'noun' => 'item'])
{{-- Floating bulk-action bar. Must live inside an x-data="adminListView(...)"
     scope so it shares `selected` / `count` / clearSelection(). It renders a
     hidden form that posts the selected ids to the given DELETE route. --}}
<div x-cloak x-show="count > 0"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-4"
     class="admin-bulk-bar fixed bottom-6 left-1/2 -translate-x-1/2 z-40 flex items-center gap-1 sm:gap-2 px-3 py-2.5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-2xl shadow-black/25">
    <span class="text-sm font-bold text-slate-700 dark:text-slate-100 px-2 whitespace-nowrap">
        <span x-text="count"></span> dipilih
    </span>
    <div class="w-px h-6 bg-slate-200 dark:bg-slate-700 mx-1"></div>
    <form method="POST" action="{{ $route }}" x-ref="bulkForm"
          @submit="if (!confirm('Hapus ' + count + ' {{ $noun }} terpilih? Tindakan ini tidak dapat dibatalkan.')) $event.preventDefault()">
        @csrf @method('DELETE')
        <template x-for="id in selected" :key="id">
            <input type="hidden" name="ids[]" :value="id">
        </template>
        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-sm font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
            Hapus
        </button>
    </form>
    <button type="button" @click="clearSelection()" title="Batal pilih"
            class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
    </button>
</div>
