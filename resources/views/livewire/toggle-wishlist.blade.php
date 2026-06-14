<div>
    @if($variant === 'button')
        <button wire:click.stop="toggle"
                class="flex items-center justify-center p-3.5 rounded-xl border border-slate-200 transition-all duration-300 group hover:border-emerald-200 hover:bg-emerald-50 {{ $isWishlisted ? 'bg-red-50 border-red-100!' : 'bg-white' }}"
                title="{{ $isWishlisted ? 'Hapus dari Wishlist' : 'Tambah ke Wishlist' }}">
            <svg class="w-6 h-6 transition-colors {{ $isWishlisted ? 'fill-red-500 text-red-500' : 'text-slate-400 group-hover:text-emerald-500' }}" 
                 fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
            </svg>
        </button>
    @else
        <button wire:click.stop="toggle"
                class="absolute top-3 right-3 p-2 rounded-full backdrop-blur-md shadow-sm transition-all duration-300 {{ $isWishlisted ? 'bg-red-50 text-red-500 hover:bg-red-100' : 'bg-white/80 text-slate-400 hover:text-red-500 hover:bg-white' }}"
                title="{{ $isWishlisted ? 'Hapus dari Wishlist' : 'Tambah ke Wishlist' }}">
            <svg class="w-5 h-5 {{ $isWishlisted ? 'fill-current' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
            </svg>
        </button>
    @endif
</div>
