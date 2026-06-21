@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}">
        {{-- Mobile View --}}
        <div class="flex gap-3 items-center justify-between sm:hidden px-1">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center justify-center w-11 h-11 text-slate-400 dark:text-slate-600 bg-white/40 dark:bg-slate-900/40 border border-slate-200/60 dark:border-slate-800/80 cursor-not-allowed rounded-xl opacity-60">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex items-center justify-center w-11 h-11 text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 hover:border-primary-500 dark:hover:border-primary-400 hover:text-primary-600 dark:hover:text-primary-400 rounded-xl shadow-xs transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                </a>
            @endif

            <div class="flex items-center justify-center py-2 px-4 bg-white dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 rounded-xl shadow-xs text-xs font-semibold text-slate-700 dark:text-slate-300">
                <span class="font-extrabold text-sm text-slate-900 dark:text-slate-100 bg-slate-50 dark:bg-slate-800/60 px-2 py-0.5 rounded-lg border border-slate-100 dark:border-slate-800">{{ $paginator->currentPage() }}</span>
                <span class="mx-1.5 text-slate-300 dark:text-slate-700 font-bold">/</span>
                <span class="font-semibold text-slate-500 dark:text-slate-400">{{ $paginator->lastPage() }}</span>
            </div>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex items-center justify-center w-11 h-11 text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 hover:border-primary-500 dark:hover:border-primary-400 hover:text-primary-600 dark:hover:text-primary-400 rounded-xl shadow-xs transition-all active:scale-95">
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
        </div>

        {{-- Desktop View --}}
        <div class="hidden sm:flex sm:justify-center">
            <div>
                <span class="inline-flex gap-1.5 shadow-sm rounded-xl">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                            <span class="inline-flex items-center px-3 py-2 text-sm font-bold text-slate-300 dark:text-slate-700 border-2 border-slate-100 dark:border-slate-800 cursor-not-allowed rounded-xl bg-white dark:bg-slate-900/40 opacity-70" aria-hidden="true">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                </svg>
                            </span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-3 py-2 text-sm font-bold text-slate-600 dark:text-slate-300 border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900/60 rounded-xl hover:border-primary-500 hover:text-primary-600 transition-colors active:scale-95" aria-label="{{ __('pagination.previous') }}">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                            </svg>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span class="inline-flex items-center px-4 py-2 text-sm font-bold text-slate-400 dark:text-slate-600 cursor-default">{{ $element }}</span>
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span class="inline-flex items-center px-4 py-2 text-sm font-black text-white dark:text-slate-900 border-2 border-slate-900 dark:border-white bg-slate-900 dark:bg-white rounded-xl shadow-md cursor-default">{{ $page }}</span>
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 border-2 border-slate-200 dark:border-slate-800 bg-transparent rounded-xl hover:border-slate-400 hover:text-slate-900 dark:hover:border-slate-600 dark:hover:text-white transition-colors active:scale-95" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-3 py-2 text-sm font-bold text-slate-600 dark:text-slate-300 border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900/60 rounded-xl hover:border-primary-500 hover:text-primary-600 transition-colors active:scale-95" aria-label="{{ __('pagination.next') }}">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                            <span class="inline-flex items-center px-3 py-2 text-sm font-bold text-slate-300 dark:text-slate-700 border-2 border-slate-100 dark:border-slate-800 cursor-not-allowed rounded-xl bg-white dark:bg-slate-900/40 opacity-70" aria-hidden="true">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
