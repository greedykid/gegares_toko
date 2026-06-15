<div x-data="{ open: false, uploading: false }" class="mt-4 border-t border-slate-100 dark:border-slate-800 pt-4">
    @if($isSubmitted && $existingReview)
        <div class="bg-emerald-50/50 dark:bg-emerald-900/10 rounded-xl p-4 border border-emerald-100 dark:border-emerald-900/30 flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1">
                    @for($i = 1; $i <= 5; $i++)
                        <svg class="w-4 h-4 {{ $i <= $existingReview->rating ? 'text-amber-400' : 'text-slate-200 dark:text-slate-800' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endfor
                </div>
                <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">Ulasan Anda</span>
            </div>
            
            @if($existingReview->comment)
                <p class="text-sm text-slate-600 dark:text-slate-400 italic">"{{ $existingReview->comment }}"</p>
            @endif

            @if($existingReview->image)
                <div class="w-16 h-16 rounded-lg overflow-hidden border border-emerald-100 dark:border-emerald-900/30">
                    <img src="{{ asset('storage/' . $existingReview->image) }}" class="w-full h-full object-cover">
                </div>
            @endif
        </div>
    @elseif($isSubmitted)
        <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 px-3 py-1.5 rounded-lg">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
            Ulasan telah dikirim
        </span>
    @else
        <button type="button" @click="open = !open" x-show="!open" class="text-sm font-semibold text-primary-600 hover:text-primary-700 transition-colors flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" /></svg>
            Tulis Ulasan
        </button>

        <div x-show="open" style="display: none;" x-collapse class="bg-slate-50/50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-100 dark:border-slate-800 mt-2">
            <form wire:submit.prevent="submit" class="space-y-4">
                
                {{-- Rating Stars --}}
                <div class="flex items-center gap-1" x-data="{ hover: 0, current: @entangle('rating') }">
                    <template x-for="i in 5">
                        <button type="button" 
                                @mouseenter="hover = i" 
                                @mouseleave="hover = 0" 
                                @click="current = i"
                                class="p-1 focus:outline-none transition-transform hover:scale-110">
                            <svg class="w-7 h-7 transition-colors duration-200" 
                                 :class="(hover >= i || current >= i) ? 'text-amber-400' : 'text-slate-300 dark:text-slate-700'" 
                                 fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </button>
                    </template>
                </div>
                @error('rating') <span class="text-xs text-red-500">{{ $message }}</span> @enderror

                {{-- Photo Upload --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Punya foto pesanan Anda?</label>
                    <div class="flex items-center gap-4">
                        <label class="cursor-pointer inline-flex items-center justify-center w-12 h-12 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors shadow-sm group">
                            <span class="sr-only">Pilih Foto</span>
                            <svg class="w-5 h-5 text-slate-400 group-hover:text-primary-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" /></svg>
                            <input type="file" wire:model="image" class="hidden" accept="image/*" @change="uploading = false" @click="uploading = true">
                        </label>

                        @if ($image)
                            <div class="relative w-12 h-12 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-800">
                                <img src="{{ $image->temporaryUrl() }}" class="w-full h-full object-cover">
                                <button type="button" wire:click="$set('image', null)" class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full p-0.5" title="Hapus foto">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        @endif

                        <div wire:loading wire:target="image" class="text-sm font-medium text-slate-500 flex items-center gap-2">
                             <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                             Mengunggah...
                        </div>
                    </div>
                    @error('image') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Comment --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Ceritakan pengalaman Anda (opsional)</label>
                    <textarea wire:model="comment" rows="3" 
                               class="w-full rounded-xl border border-slate-300 dark:border-slate-700 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 bg-white dark:bg-slate-950/50 text-slate-900 dark:text-slate-100 outline-none transition-colors"
                               placeholder="Apakah produk tiba dengan aman? Bagaimana rasanya?"></textarea>
                    @error('comment') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="flex gap-2 justify-end pt-1">
                    <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition-colors">Batal</button>
                    <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center gap-2 px-5 py-2 bg-primary-600 text-white text-sm font-bold rounded-xl shadow-sm hover:bg-primary-700 focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all disabled:opacity-50">
                        <svg wire:loading wire:target="submit" class="animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display:none;">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="submit">Kirim Ulasan</span>
                        <span wire:loading wire:target="submit">Mengirim...</span>
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
