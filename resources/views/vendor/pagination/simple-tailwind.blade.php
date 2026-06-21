@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex gap-2 items-center justify-between">

        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center justify-center w-11 h-11 text-slate-400 dark:text-slate-600 bg-white/40 dark:bg-slate-900/40 border border-slate-200/60 dark:border-slate-800/80 cursor-not-allowed rounded-xl opacity-60">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center justify-center w-11 h-11 text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 hover:border-primary-500 dark:hover:border-primary-400 hover:text-primary-600 dark:hover:text-primary-400 rounded-xl shadow-xs transition-all active:scale-95">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
            </a>
        @endif

        <div class="text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900/80 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-800">
            {{ $paginator->currentPage() }}
        </div>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center justify-center w-11 h-11 text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 hover:border-primary-500 dark:hover:border-primary-400 hover:text-primary-600 dark:hover:text-primary-400 rounded-xl shadow-xs transition-all active:scale-95">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </a>
        @else
            <span class="inline-flex items-center justify-center w-11 h-11 text-slate-400 dark:text-slate-600 bg-white/40 dark:bg-slate-900/40 border border-slate-200/60 dark:border-slate-800/80 cursor-not-allowed rounded-xl opacity-60">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </span>
        @endif

    </nav>
@endif
