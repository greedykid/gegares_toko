@props([
    'order',
    'size' => 'md',
])

@php
    // Order::getStatusColorAttribute() can return yellow, blue, indigo, green,
    // red or gray. The two inline maps this component replaces both handled only
    // four of those and sent the rest to a blue default, so a shipped order
    // (indigo) rendered blue. 'emerald' and 'orange' are kept as aliases so any
    // caller passing them keeps working.
    $palette = match ($order->status_color) {
        'green', 'emerald' => ['bg-emerald-50', 'dark:bg-emerald-950/40', 'text-emerald-600', 'dark:text-emerald-400', 'border-emerald-200/50', 'dark:border-emerald-900/30'],
        'red' => ['bg-red-50', 'dark:bg-red-950/40', 'text-red-600', 'dark:text-red-400', 'border-red-200/50', 'dark:border-red-900/30'],
        'orange' => ['bg-orange-50', 'dark:bg-orange-950/40', 'text-orange-600', 'dark:text-orange-400', 'border-orange-200/50', 'dark:border-orange-900/30'],
        'yellow' => ['bg-yellow-50', 'dark:bg-yellow-950/40', 'text-yellow-600', 'dark:text-yellow-400', 'border-yellow-200/50', 'dark:border-yellow-900/30'],
        'indigo' => ['bg-indigo-50', 'dark:bg-indigo-950/40', 'text-indigo-600', 'dark:text-indigo-400', 'border-indigo-200/50', 'dark:border-indigo-900/30'],
        'gray' => ['bg-slate-100', 'dark:bg-slate-800', 'text-slate-500', 'dark:text-slate-400', 'border-slate-200/50', 'dark:border-slate-700/30'],
        default => ['bg-blue-50', 'dark:bg-blue-950/40', 'text-blue-600', 'dark:text-blue-400', 'border-blue-200/50', 'dark:border-blue-900/30'],
    };

    $shape = $size === 'sm'
        ? 'px-1.5 py-0.5 rounded text-[10px] uppercase'
        : 'px-2.5 py-0.5 rounded-lg text-xs border';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center font-bold '.$shape.' '.implode(' ', $palette)]) }}>
    {{ $order->status_label }}
</span>
