@props([
    'stock',
    'threshold' => 5,
    // The cart drawer never flagged an out-of-stock line (its condition was
    // `stock <= 5 && stock > 0`), so this stays opt-out rather than silently
    // introducing a new state there.
    'outOfStock' => true,
])

@if($stock <= 0)
    @if($outOfStock)
        <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 mt-1 px-1.5 py-0.5 rounded bg-red-50 dark:bg-red-900/20 text-red-500 dark:text-red-400 text-[9px] font-bold uppercase']) }}>Stok Habis</span>
    @endif
@elseif($stock <= $threshold)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 mt-1 px-1.5 py-0.5 rounded bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 text-[9px] font-bold uppercase']) }}>
        <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 6a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 6Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
        Sisa {{ $stock }}
    </span>
@endif
