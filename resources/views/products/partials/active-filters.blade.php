{{--
    Badges for the filters currently narrowing the catalogue.

    Included twice: under the results header, and again in the empty state —
    when a filter narrows the catalogue to nothing these badges are the only way
    back out, so they have to survive the "no results" branch.

    Each badge is a plain link, so it rides on the existing server-side filtering
    and keeps working without JS. Sort has no badge: it orders rather than
    filters, and the header already shows it.

    @param array $activeFilters  [['label' => string, 'removeUrl' => string], ...]
--}}
@if (!empty($activeFilters))
    <div class="flex flex-wrap items-center gap-2 mb-6" aria-label="Filter aktif">
        <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Filter aktif</span>

        @foreach ($activeFilters as $filter)
            <a href="{{ $filter['removeUrl'] }}"
                class="group inline-flex items-center gap-1.5 pl-3 pr-2 py-1.5 rounded-full bg-primary-50 dark:bg-primary-950/40 text-primary-700 dark:text-primary-300 border border-primary-100 dark:border-primary-900/50 text-xs font-bold hover:bg-primary-100 dark:hover:bg-primary-900/50 transition-colors"
                title="Hapus filter ini">
                <span>{{ $filter['label'] }}</span>
                <span aria-hidden="true"
                    class="flex items-center justify-center w-4 h-4 rounded-full bg-primary-200/70 dark:bg-primary-800/70 text-primary-800 dark:text-primary-200 group-hover:bg-primary-600 group-hover:text-white transition-colors">
                    <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke-width="3.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </span>
                <span class="sr-only">Hapus filter {{ $filter['label'] }}</span>
            </a>
        @endforeach

        @if (count($activeFilters) > 1)
            {{-- Clears everything, matching the sidebar's "Reset Filter" link so the
                 two controls with the same name cannot disagree. --}}
            <a href="{{ route('products.index') }}"
                class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 underline underline-offset-2 transition-colors">
                Reset Filter
            </a>
        @endif
    </div>
@endif
