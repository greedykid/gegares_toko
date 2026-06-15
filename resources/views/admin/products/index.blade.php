@extends('layouts.admin')
@section('page_title', 'Kelola Produk')
@section('content')
@php
    $sort = request('sort', 'created_at');
    $dir = request('direction', 'desc');
    
    if (!function_exists('sortUrl')) {
        function sortUrl($column, $currentSort, $currentDir) {
            $nextDir = ($currentSort === $column && $currentDir === 'asc') ? 'desc' : 'asc';
            return request()->fullUrlWithQuery(['sort' => $column, 'direction' => $nextDir]);
        }
    }
@endphp

<div x-data="{ 
    showModal: false, 
    editMode: false, 
    form: { id:null, slug:'', name:'', category_id:'', description:'', price:'', stock:'', is_featured:false, image:'' },
    imagePreview: null,
    
    {{-- Gallery State --}}
    existingGallery: [], 
    removedGalleryIds: [], 
    slotPreviews: {},

    {{-- Variants State --}}
    variants: [],
    removedVariantIds: [],
    addVariant() {
        this.variants.push({ id: '', name: '', price: '', stock: '' });
    },
    removeVariant(index) {
        let v = this.variants[index];
        if (v.id) {
            this.removedVariantIds.push(v.id);
        }
        this.variants.splice(index, 1);
    },
    
    previewImage(event) {
        const file = event.target.files[0];
        if (file) {
            this.imagePreview = URL.createObjectURL(file);
        }
    },
    
    {{-- Preview for a specific slot --}}
    previewSlot(event, index) {
        const file = event.target.files[0];
        if (file) {
            this.slotPreviews = { ...this.slotPreviews, [index]: URL.createObjectURL(file) };
        }
    },
    
    removeExistingImage(id) {
        if (!this.removedGalleryIds.includes(id)) {
            this.removedGalleryIds.push(id);
        }
    },

    {{-- Reset all states --}}
    resetGallery() {
        this.imagePreview = null;
        this.existingGallery = [];
        this.removedGalleryIds = [];
        this.slotPreviews = {};
        this.variants = [];
        this.removedVariantIds = [];
        {{-- Clear all file inputs --}}
        for(let i=0; i<6; i++) {
            const input = document.getElementById('galleryInput' + i);
            if (input) input.value = '';
        }
    }
}">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100 transition-colors duration-300">Manajemen Produk</h2>
        <button @click="resetGallery(); showModal=true; editMode=false; form={id:null,slug:'',name:'',category_id:'',description:'',price:'',stock:'',is_featured:false,image:''};"
                class="px-5 py-2.5 bg-primary-600 text-white text-sm font-semibold rounded-xl hover:bg-primary-700 shadow-sm transition-all">+ Tambah Produk</button>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5 hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Total Produk</p>
                    <p class="text-xl font-extrabold text-slate-900 dark:text-slate-100 mt-0.5">{{ number_format($totalProducts) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5 hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-900/40 flex items-center justify-center text-amber-500 dark:text-amber-400 transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" /></svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-amber-500 dark:text-amber-400 uppercase tracking-widest">Unggulan</p>
                    <p class="text-xl font-extrabold text-slate-900 dark:text-slate-100 mt-0.5">{{ number_format($featuredProducts) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5 hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-red-50 dark:bg-red-900/40 flex items-center justify-center text-red-500 dark:text-red-400 transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-red-400 dark:text-red-500 uppercase tracking-widest">Stok Habis</p>
                    <p class="text-xl font-extrabold text-slate-900 dark:text-slate-100 mt-0.5">{{ number_format($outOfStock) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5 hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-orange-50 dark:bg-orange-900/40 flex items-center justify-center text-orange-500 dark:text-orange-400 transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-orange-400 dark:text-orange-500 uppercase tracking-widest">Stok Menipis</p>
                    <p class="text-xl font-extrabold text-slate-900 dark:text-slate-100 mt-0.5">{{ number_format($lowStock) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-4 mb-6 transition-all duration-300">
        <form method="GET" action="{{ route('admin.products.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
            <div class="lg:col-span-1">
                <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5 ml-1">Cari Produk</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama produk..." 
                           class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all">
                    <div class="absolute left-3 top-2.5 text-slate-400 dark:text-slate-600">
                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5 ml-1">Kategori</label>
                <div x-data="{ 
                        open: false, 
                        selectedValue: '{{ request('category') }}', 
                        selectedLabel: '{{ request('category') ? $categories->firstWhere('id', request('category'))->name ?? 'Semua Kategori' : 'Semua Kategori' }}'
                     }" 
                     class="relative w-full">
                    <input type="hidden" name="category" :value="selectedValue">
                    <button @click="open = !open" type="button" 
                            class="w-full flex items-center justify-between pl-4 pr-3 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 cursor-pointer transition-all">
                        <span x-text="selectedLabel"></span>
                        <svg class="h-4 w-4 text-slate-400 dark:text-slate-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open" 
                         @click.outside="open = false" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl py-1 max-h-60 overflow-y-auto"
                         style="display: none;">
                        <button type="button" @click="selectedValue = ''; selectedLabel = 'Semua Kategori'; open = false; $nextTick(() => { $el.closest('form').submit() })"
                                class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                :class="selectedValue === '' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">
                            Semua Kategori
                        </button>
                        @foreach($categories as $cat)
                            <button type="button" @click="selectedValue = '{{ $cat->id }}'; selectedLabel = '{{ $cat->name }}'; open = false; $nextTick(() => { $el.closest('form').submit() })"
                                    class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                    :class="selectedValue === '{{ $cat->id }}' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">
                                {{ $cat->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5 ml-1">Stok</label>
                <div x-data="{ 
                        open: false, 
                        selectedValue: '{{ request('stock_status') }}', 
                        selectedLabel: '{{ request('stock_status') == 'out_of_stock' ? 'Stok Habis (0)' : (request('stock_status') == 'low_stock' ? 'Stok Menipis (1-4)' : (request('stock_status') == 'in_stock' ? 'Tersedia (5+)' : 'Semua Stok')) }}'
                     }" 
                     class="relative w-full">
                    <input type="hidden" name="stock_status" :value="selectedValue">
                    <button @click="open = !open" type="button" 
                            class="w-full flex items-center justify-between pl-4 pr-3 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 cursor-pointer transition-all">
                        <span x-text="selectedLabel"></span>
                        <svg class="h-4 w-4 text-slate-400 dark:text-slate-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open" 
                         @click.outside="open = false" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl py-1 overflow-hidden"
                         style="display: none;">
                        <button type="button" @click="selectedValue = ''; selectedLabel = 'Semua Stok'; open = false; $nextTick(() => { $el.closest('form').submit() })"
                                class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                :class="selectedValue === '' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">
                            Semua Stok
                        </button>
                        <button type="button" @click="selectedValue = 'out_of_stock'; selectedLabel = 'Stok Habis (0)'; open = false; $nextTick(() => { $el.closest('form').submit() })"
                                class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                :class="selectedValue === 'out_of_stock' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">
                            Stok Habis (0)
                        </button>
                        <button type="button" @click="selectedValue = 'low_stock'; selectedLabel = 'Stok Menipis (1-4)'; open = false; $nextTick(() => { $el.closest('form').submit() })"
                                class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                :class="selectedValue === 'low_stock' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">
                            Stok Menipis (1-4)
                        </button>
                        <button type="button" @click="selectedValue = 'in_stock'; selectedLabel = 'Tersedia (5+)'; open = false; $nextTick(() => { $el.closest('form').submit() })"
                                class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                :class="selectedValue === 'in_stock' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">
                            Tersedia (5+)
                        </button>
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5 ml-1">Status</label>
                <div x-data="{ 
                        open: false, 
                        selectedValue: '{{ request('is_featured') }}', 
                        selectedLabel: '{{ request('is_featured') === '1' ? 'Hanya Unggulan' : (request('is_featured') === '0' ? 'Reguler Saja' : 'Semua Status') }}'
                     }" 
                     class="relative w-full">
                    <input type="hidden" name="is_featured" :value="selectedValue">
                    <button @click="open = !open" type="button" 
                            class="w-full flex items-center justify-between pl-4 pr-3 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 cursor-pointer transition-all">
                        <span x-text="selectedLabel"></span>
                        <svg class="h-4 w-4 text-slate-400 dark:text-slate-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open" 
                         @click.outside="open = false" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl py-1 overflow-hidden"
                         style="display: none;">
                        <button type="button" @click="selectedValue = ''; selectedLabel = 'Semua Status'; open = false; $nextTick(() => { $el.closest('form').submit() })"
                                class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                :class="selectedValue === '' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">
                            Semua Status
                        </button>
                        <button type="button" @click="selectedValue = '1'; selectedLabel = 'Hanya Unggulan'; open = false; $nextTick(() => { $el.closest('form').submit() })"
                                class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                :class="selectedValue === '1' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">
                            Hanya Unggulan
                        </button>
                        <button type="button" @click="selectedValue = '0'; selectedLabel = 'Reguler Saja'; open = false; $nextTick(() => { $el.closest('form').submit() })"
                                class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                :class="selectedValue === '0' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">
                            Reguler Saja
                        </button>
                    </div>
                </div>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-slate-800 dark:bg-slate-700 text-white text-sm font-bold rounded-xl hover:bg-slate-900 dark:hover:bg-slate-600 shadow-sm transition-colors duration-200">Filter</button>
                @if(request()->anyFilled(['search', 'category', 'stock_status', 'is_featured']))
                    <a href="{{ route('admin.products.index') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-sm font-bold rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-all flex items-center justify-center" title="Reset">
                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden transition-all duration-300">
        <div class="overflow-x-auto lg:overflow-x-visible custom-scrollbar">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40">
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest rounded-tl-2xl">
                            <a href="{{ sortUrl('name', $sort, $dir) }}" class="inline-flex items-center gap-1 group hover:text-slate-600 dark:hover:text-slate-350 transition-colors">
                                Produk
                                @if($sort === 'name')
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
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                            <a href="{{ sortUrl('description', $sort, $dir) }}" class="inline-flex items-center gap-1 group hover:text-slate-600 dark:hover:text-slate-350 transition-colors">
                                Deskripsi
                                @if($sort === 'description')
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
                        <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                            <a href="{{ sortUrl('category_id', $sort, $dir) }}" class="inline-flex items-center gap-1 group hover:text-slate-600 dark:hover:text-slate-350 transition-colors">
                                Kategori
                                @if($sort === 'category_id')
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
                        <th class="px-6 py-4 text-right text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                            <a href="{{ sortUrl('price', $sort, $dir) }}" class="inline-flex items-center justify-end gap-1 group hover:text-slate-600 dark:hover:text-slate-350 transition-colors w-full">
                                Harga
                                @if($sort === 'price')
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
                        <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                            <a href="{{ sortUrl('stock', $sort, $dir) }}" class="inline-flex items-center justify-center gap-1 group hover:text-slate-600 dark:hover:text-slate-350 transition-colors mx-auto">
                                Stok
                                @if($sort === 'stock')
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
                        <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                            <a href="{{ sortUrl('is_featured', $sort, $dir) }}" class="inline-flex items-center justify-center gap-1 group hover:text-slate-600 dark:hover:text-slate-350 transition-colors mx-auto">
                                Unggulan
                                @if($sort === 'is_featured')
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
                        <th class="px-6 py-4 text-right text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest rounded-tr-2xl">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/60 dark:divide-slate-800/60">
                    @forelse($products as $product)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 shrink-0 border border-slate-100 dark:border-slate-700/60 shadow-xs">
                                    @if($product->image)
                                        <img src="{{ asset('storage/'.$product->image) }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-slate-50 dark:bg-slate-800">
                                            <svg class="w-5 h-5 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 18.75V5.25A2.25 2.25 0 0 1 5.25 3h13.5A2.25 2.25 0 0 1 21 5.25v13.5A2.25 2.25 0 0 1 18.75 21H5.25A2.25 2.25 0 0 1 3 18.75Z" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold text-slate-900 dark:text-slate-100 hover:text-primary-600 dark:hover:text-primary-400 transition-colors leading-none mb-1">{{ $product->name }}</span>
                                    <span class="text-[10px] text-slate-400 dark:text-slate-500 tracking-wider">/{{ $product->slug }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-500 dark:text-slate-400 max-w-[200px] truncate" title="{{ $product->description ?: '-' }}">{{ $product->description ?: '-' }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/50 dark:border-slate-700">
                                {{ $product->category->name ?? '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-right font-bold text-slate-900 dark:text-slate-100">{{ $product->formatted_price }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold {{ $product->stock == 0 ? 'bg-red-50 dark:bg-red-950/40 text-red-650 dark:text-red-400 border border-red-200/50 dark:border-red-950/30' : ($product->stock < 5 ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-650 dark:text-amber-400 border border-amber-200/50 dark:border-amber-950/30' : 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-650 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-950/30') }}">{{ $product->stock }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center">
                                <form method="POST" action="{{ route('admin.products.toggle-featured', $product) }}" class="inline-flex items-center gap-2">
                                    @csrf @method('PATCH')
                                    <label class="relative inline-flex items-center cursor-pointer group">
                                        <input type="checkbox" class="sr-only peer" {{ $product->is_featured ? 'checked' : '' }} onchange="this.closest('form').submit()">
                                        <div class="w-9 h-5 bg-slate-200 dark:bg-slate-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-amber-500 dark:peer-checked:bg-amber-600 group-hover:shadow-sm"></div>
                                    </label>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $product->is_featured ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500' }}">
                                        {{ $product->is_featured ? 'Unggulan' : 'Reguler' }}
                                    </span>
                                </form>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right flex items-center justify-end gap-2">
                            <div class="relative group/tooltip inline-flex">
                                <button @click="resetGallery(); showModal=true; editMode=true; form={id:{{ $product->id }},slug:'{{ $product->slug }}',name:'{{ addslashes($product->name) }}',category_id:'{{ $product->category_id }}',description:'{{ addslashes($product->description) }}',price:'{{ $product->price }}',stock:'{{ $product->stock }}',is_featured:{{ $product->is_featured ? 'true' : 'false' }}, image: '{{ $product->image }}'}; existingGallery={{ $product->images->toJson() }}; variants={{ $product->variants->toJson() }};"
                                        class="p-2 text-primary-600 dark:text-primary-400 hover:text-primary-750 hover:bg-primary-50 dark:hover:bg-primary-950/60 rounded-xl transition-all" title="Edit">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                </button>
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-slate-900 text-white text-[10px] font-bold rounded-lg shadow-xl opacity-0 group-hover/tooltip:opacity-100 transition-all pointer-events-none whitespace-nowrap z-50">
                                    Edit Produk
                                    <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></div>
                                </div>
                            </div>
                            <div class="relative group/tooltip inline-flex">
                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="inline" onsubmit="return confirm('Hapus produk ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/60 rounded-xl transition-all" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                    </button>
                                </form>
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-slate-900 text-white text-[10px] font-bold rounded-lg shadow-xl opacity-0 group-hover/tooltip:opacity-100 transition-all pointer-events-none whitespace-nowrap z-50">
                                    Hapus Produk
                                    <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 rounded-2xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-300 dark:text-slate-600 mb-4 transition-colors">
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                                </div>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 transition-colors">Produk Tidak Ditemukan</h3>
                                <p class="text-slate-500 dark:text-slate-500 text-sm mt-1 max-w-xs mx-auto transition-colors">Maaf, kami tidak dapat menemukan produk yang sesuai dengan filter atau kata kunci pencarian Anda.</p>
                                <div class="mt-6">
                                    <a href="{{ route('admin.products.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-bold rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors duration-200">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                                        Atur Ulang Semua Filter
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-3 border-t border-slate-50 dark:border-slate-800">{{ $products->links() }}</div>
    </div>

    {{-- Modal --}}
    <div x-show="showModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showModal=false"></div>
        <div class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-lg z-10 max-h-[90vh] flex flex-col border border-slate-200 dark:border-slate-800 transition-all duration-300 overflow-hidden" x-transition>
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 transition-colors" x-text="editMode ? 'Edit Produk' : 'Tambah Produk'"></h3>
            </div>

            <form :action="editMode ? '{{ url('admin/products') }}/'+form.slug : '{{ route('admin.products.store') }}'" method="POST" enctype="multipart/form-data" class="flex flex-col min-h-0 overflow-hidden">
                @csrf
                <template x-if="editMode"><input type="hidden" name="_method" value="PUT"></template>
                
                <!-- Modal Body (Scrollable) -->
                <div class="flex-1 p-6 overflow-y-auto space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Produk</label>
                        <input type="text" name="name" x-model="form.name" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Kategori</label>
                        <div x-data="{ 
                                open: false,
                                categories: [
                                    @foreach($categories as $cat)
                                        { id: '{{ $cat->id }}', name: '{{ addslashes($cat->name) }}' },
                                    @endforeach
                                ],
                                get selectedLabel() {
                                    let match = this.categories.find(c => String(c.id) === String(form.category_id));
                                    return match ? match.name : 'Pilih Kategori';
                                }
                             }" 
                             class="relative">
                            <input type="hidden" name="category_id" x-model="form.category_id" required>
                            <button @click="open = !open" type="button" 
                                    class="w-full flex items-center justify-between pl-4 pr-3 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 cursor-pointer transition-all">
                                <span x-text="selectedLabel"></span>
                                <svg class="h-4 w-4 text-slate-400 dark:text-slate-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>
                            <div x-show="open" 
                                 @click.outside="open = false" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl py-1 max-h-60 overflow-y-auto"
                                 style="display: none;">
                                <button type="button" @click="form.category_id = ''; open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                        :class="String(form.category_id) === '' ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''">
                                    Pilih Kategori
                                </button>
                                <template x-for="cat in categories" :key="cat.id">
                                    <button type="button" @click="form.category_id = cat.id; open = false"
                                            class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                            :class="String(form.category_id) === String(cat.id) ? 'bg-slate-50 dark:bg-slate-800/50 font-medium text-primary-600 dark:text-primary-400' : ''"
                                            x-text="cat.name">
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Deskripsi</label>
                        <textarea name="description" x-model="form.description" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Harga (Rp)</label>
                            <input type="number" name="price" x-model="form.price" required min="0" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Stok</label>
                            <input type="number" name="stock" x-model="form.stock" required min="0" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all">
                        </div>
                    </div>
                    {{-- Primary and Gallery Images --}}
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 transition-colors">Gambar Utama (Sampul)</label>
                            <div class="relative">
                                <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-2xl cursor-pointer bg-slate-50 dark:bg-slate-950 hover:bg-slate-100/50 dark:hover:bg-slate-800/30 hover:border-primary-300 transition-all overflow-hidden group">
                                    <template x-if="imagePreview || (editMode && form.image)">
                                        <img :src="imagePreview || '/storage/' + form.image" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!imagePreview && (!editMode || !form.image)">
                                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                            <svg class="w-8 h-8 mb-2 text-slate-400 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 18.75V5.25A2.25 2.25 0 0 1 5.25 3h13.5A2.25 2.25 0 0 1 21 5.25v13.5A2.25 2.25 0 0 1 18.75 21H5.25A2.25 2.25 0 0 1 3 18.75Z" /></svg>
                                            <p class="text-xs text-slate-500 dark:text-slate-500 font-medium transition-colors">Upload Produk Utama</p>
                                        </div>
                                    </template>
                                    <input type="file" name="image" class="hidden" accept="image/*" @change="previewImage">
                                </label>
                            <button type="button" x-show="imagePreview" @click="imagePreview = null"
                                        class="absolute top-2 right-2 p-1.5 bg-white/80 dark:bg-slate-900/80 backdrop-blur rounded-lg text-red-500 shadow-sm hover:bg-white dark:hover:bg-slate-800 transition-all">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 transition-colors">Galeri Tambahan (Maks 6)</label>
                                <span class="text-[10px] font-bold px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 rounded-full transition-colors" 
                                    x-text="(existingGallery.filter(img => !removedGalleryIds.includes(img.id)).length + Object.keys(slotPreviews).length) + '/6'"></span>
                            </div>
                            
                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                                {{-- Existing Images --}}
                                <template x-for="img in existingGallery" :key="img.id">
                                    <template x-if="!removedGalleryIds.includes(img.id)">
                                        <div class="relative aspect-square rounded-xl overflow-hidden border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 group transition-all">
                                            <img :src="'/storage/' + img.image_path" class="w-full h-full object-cover">
                                            <button type="button" @click="removeExistingImage(img.id)"
                                                    class="absolute top-1 right-1 p-1 bg-red-500 text-white rounded-md opacity-0 group-hover:opacity-100 transition-all z-10 shadow-sm hover:bg-red-600">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                            <input type="hidden" name="removed_gallery_ids[]" :value="img.id" x-show="removedGalleryIds.includes(img.id)">
                                        </div>
                                    </template>
                                </template>

                                {{-- Hidden inputs for removals --}}
                                <template x-for="id in removedGalleryIds" :key="'rem-'+id">
                                    <input type="hidden" name="removed_gallery_ids[]" :value="id">
                                </template>

                                {{-- New Upload Slots --}}
                                <template x-for="i in [0,1,2,3,4,5]" :key="i">
                                    <div x-show="i < (6 - existingGallery.filter(img => !removedGalleryIds.includes(img.id)).length)" class="relative aspect-square">
                                        {{-- The actual file input (ALWAY PERSISTENT in DOM) --}}
                                        <input type="file" name="gallery[]" :id="'galleryInput' + i" class="hidden" accept="image/*" @change="previewSlot($event, i)">
                                        
                                        {{-- Case: Image selected (Visual only toggle) --}}
                                        <div x-show="slotPreviews[i]" class="relative w-full h-full rounded-xl overflow-hidden border border-primary-100 dark:border-primary-900 bg-slate-50 dark:bg-slate-950 group transition-all">
                                            <img :src="slotPreviews[i]" class="w-full h-full object-cover">
                                            <button type="button" @click="delete slotPreviews[i]; document.getElementById('galleryInput' + i).value = ''"
                                                    class="absolute top-1 right-1 p-1 bg-red-500 text-white rounded-md opacity-0 group-hover:opacity-100 transition-all z-10 shadow-sm hover:bg-red-600">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                        </div>
                                        
                                        {{-- Case: Empty slot (Visual only toggle) --}}
                                        <label x-show="!slotPreviews[i]" :for="'galleryInput' + i"
                                            class="w-full h-full flex flex-col items-center justify-center border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-xl cursor-pointer bg-slate-50 dark:bg-slate-950 hover:bg-slate-100 dark:hover:bg-slate-800/50 hover:border-primary-300 transition-colors duration-200">
                                            <svg class="w-5 h-5 text-slate-400 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                        </label>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Product Variants --}}
                    <div class="border-t border-slate-100 dark:border-slate-800 pt-4 mt-4">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300">Variasi Produk</h4>
                                <p class="text-[10px] text-slate-500">Opsional (Misal: Pedas, Sedang). Biarkan kosong jika tidak ada.</p>
                            </div>
                            <button type="button" @click="addVariant()" class="text-xs font-semibold px-2 py-1 bg-primary-50 text-primary-600 hover:bg-primary-100 rounded-lg transition-colors">+ Tambah Varian</button>
                        </div>
                        
                        <div class="space-y-3">
                            <template x-for="(variant, index) in variants" :key="index">
                                <div class="flex items-end gap-2 p-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl relative group transition-colors">
                                    <input type="hidden" :name="'variants['+index+'][id]'" :value="variant.id">
                                    <div class="flex-1">
                                        <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Nama Variasi</label>
                                        <input type="text" :name="'variants['+index+'][name]'" x-model="variant.name" placeholder="Pedas" required class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs focus:outline-none focus:ring-1 focus:ring-primary-500">
                                    </div>
                                    <div class="flex-1">
                                        <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Harga (Opsional)</label>
                                        <input type="number" :name="'variants['+index+'][price]'" x-model="variant.price" placeholder="Sama" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs focus:outline-none focus:ring-1 focus:ring-primary-500">
                                    </div>
                                    <div class="w-20">
                                        <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Stok</label>
                                        <input type="number" :name="'variants['+index+'][stock]'" x-model="variant.stock" required min="0" class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs focus:outline-none focus:ring-1 focus:ring-primary-500">
                                    </div>
                                    <button type="button" @click="removeVariant(index)" class="p-2 mb-0.5 text-red-400 hover:text-red-600 transition-colors bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                            </template>
                            
                            <template x-for="rid in removedVariantIds">
                                <input type="hidden" name="removed_variant_ids[]" :value="rid">
                            </template>
                            
                            <template x-if="variants.length === 0">
                                <div class="text-center py-6 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-xl bg-slate-50/50 dark:bg-slate-900/50">
                                    <p class="text-xs text-slate-400">Punya banyak pilihan rasa atau ukuran? Tambahkan di sini.</p>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div class="p-4 bg-amber-50/50 dark:bg-amber-950/30 rounded-2xl border border-amber-100 dark:border-amber-900/40 mb-2 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-white dark:bg-slate-900 flex items-center justify-center shadow-sm text-amber-500 transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" /></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-900 dark:text-slate-100 transition-colors">Produk Unggulan</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-500 transition-colors">Tampilkan produk ini di bagian unggulan beranda</p>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_featured" value="1" class="sr-only peer" x-model="form.is_featured">
                                <div class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:inset-s-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-400"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer (Fixed) -->
                <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3 bg-white dark:bg-slate-900">
                    <button type="button" @click="showModal=false" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition-colors">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-primary-600 text-white text-sm font-semibold rounded-xl hover:bg-primary-700 shadow-sm transition-all duration-200">Simpan</button>
                </div>
            </form>
        </div>
        </div>
    </div>
</div>
@endsection
