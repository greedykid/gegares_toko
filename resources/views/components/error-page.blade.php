@props([
    'code',
    'title',
    'message',
    'tone' => 'primary',   // primary | amber | red
])

@php
    $palette = match ($tone) {
        'amber' => [
            'ring' => 'bg-amber-50 dark:bg-amber-950/30 ring-amber-100 dark:ring-amber-900/40',
            'icon' => 'text-amber-500 dark:text-amber-400',
            'code' => 'text-amber-500/15 dark:text-amber-400/10',
        ],
        'red' => [
            'ring' => 'bg-red-50 dark:bg-red-950/30 ring-red-100 dark:ring-red-900/40',
            'icon' => 'text-red-500 dark:text-red-400',
            'code' => 'text-red-500/15 dark:text-red-400/10',
        ],
        default => [
            'ring' => 'bg-primary-50 dark:bg-primary-950/30 ring-primary-100 dark:ring-primary-900/40',
            'icon' => 'text-primary-600 dark:text-primary-400',
            'code' => 'text-primary-600/15 dark:text-primary-400/10',
        ],
    };
@endphp

<div class="relative min-h-[70vh] flex items-center justify-center px-5 py-16 overflow-hidden">
    {{-- The code sits behind the content as a watermark: readable as decoration,
         never competing with the sentence that actually explains the problem. --}}
    <span aria-hidden="true"
          class="pointer-events-none select-none absolute inset-0 flex items-center justify-center
                 text-[9rem] sm:text-[14rem] font-black leading-none tracking-tighter {{ $palette['code'] }}">
        {{ $code }}
    </span>

    <div class="relative w-full max-w-lg text-center">
        <div class="mx-auto w-20 h-20 rounded-3xl flex items-center justify-center ring-1 {{ $palette['ring'] }}">
            <div class="w-9 h-9 {{ $palette['icon'] }}">
                {{ $icon }}
            </div>
        </div>

        <p class="mt-6 text-[11px] font-black uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">
            Error {{ $code }}
        </p>

        <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-slate-100">
            {{ $title }}
        </h1>

        <p class="mt-3 text-sm sm:text-[15px] leading-relaxed text-slate-500 dark:text-slate-400">
            {{ $message }}
        </p>

        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
            {{ $actions ?? '' }}
        </div>

        {{-- Kept for every code: a customer who lands here has usually already
             failed at something, and the shop is easier to reach than a retry. --}}
        <p class="mt-8 text-xs text-slate-400 dark:text-slate-500">
            Butuh bantuan?
            <a href="{{ route('products.index') }}" class="font-bold text-primary-600 dark:text-primary-400 hover:underline">
                Lihat semua produk
            </a>
            atau hubungi kami lewat tombol chat di pojok layar.
        </p>
    </div>
</div>
