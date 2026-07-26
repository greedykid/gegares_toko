@props([
    'column',
    'sort' => null,
    'dir' => null,
    'align' => 'left',
])

@php
    // Fall back to the query string so a table can drop the props entirely.
    $sort ??= request('sort', 'created_at');
    $dir ??= request('direction', 'desc');

    $active = $sort === $column;
    $nextDir = ($active && $dir === 'asc') ? 'desc' : 'asc';
    $url = request()->fullUrlWithQuery(['sort' => $column, 'direction' => $nextDir]);

    $cellAlign = match ($align) {
        'center' => 'text-center',
        'right' => 'text-right',
        default => 'text-left',
    };

    // The anchor is inline-flex, so it needs its own alignment: text-align on the
    // cell alone will not move it.
    $linkAlign = match ($align) {
        'center' => 'justify-center mx-auto',
        'right' => 'justify-end w-full',
        default => '',
    };
@endphp

<th {{ $attributes->merge(['class' => "px-6 py-4 {$cellAlign} text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest"]) }}>
    <a href="{{ $url }}"
       class="inline-flex items-center gap-1 {{ $linkAlign }} group hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
        {{ $slot }}
        @if($active)
            @if($dir === 'asc')
                <svg class="w-3 h-3 text-primary-500 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/></svg>
            @else
                <svg class="w-3 h-3 text-primary-500 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
            @endif
        @else
            <svg class="w-3 h-3 text-slate-300 dark:text-slate-600 group-hover:text-slate-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"/></svg>
        @endif
    </a>
</th>
