@extends('layouts.admin')
@section('page_title', 'Kelola Produk')
@section('content')
@php
    $sort = request('sort', 'created_at');
    $dir = request('direction', 'desc');
    
@endphp

<div x-data="{ 
    showModal: false, 
    editMode: false, 
    form: { id:null, slug:'', name:'', category_id:'', description:'', price:'', stock:'', reserved_quantity:0, is_featured:false, image:'' },
    removeImageField: false,

    {{-- One dropzone feeds both file inputs: the first photo is the cover
         (`image`), the rest ride along as `gallery[]`. --}}
    newFiles: [],
    dragging: false,
    limitNotice: '',
    
    {{-- Gallery State --}}
    existingGallery: [], 
    removedGalleryIds: [], 

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
    
    {{-- A saved cover survives an edit unless the admin removes it. --}}
    coverKept() {
        return !!(this.editMode && this.form.image && !this.removeImageField);
    },

    keptGallery() {
        return this.existingGallery.filter(img => !this.removedGalleryIds.includes(img.id));
    },

    {{-- One cover plus six gallery images, matching what the controller stores. --}}
    roomLeft() {
        const coverRoom = this.coverKept() ? 0 : 1;
        const galleryRoom = 6 - this.keptGallery().length;

        return Math.max(0, coverRoom + galleryRoom - this.newFiles.length);
    },

    addFiles(list) {
        const picked = Array.from(list || []).filter(f => f.type.startsWith('image/'));
        const room = this.roomLeft();

        picked.slice(0, room).forEach(file => {
            this.newFiles.push({ file, url: URL.createObjectURL(file) });
        });

        this.limitNotice = picked.length > room
            ? (picked.length - room) + ' foto tidak dimuat — maksimal 7 foto per produk (1 sampul + 6 galeri).'
            : '';

        this.syncFileInputs();
    },

    removeNewFile(index) {
        URL.revokeObjectURL(this.newFiles[index].url);
        this.newFiles.splice(index, 1);
        this.limitNotice = '';
        this.syncFileInputs();
    },

    removeCover() {
        this.form.image = '';
        this.removeImageField = true;
        {{-- The first pending photo is promoted to cover, so re-split. --}}
        this.syncFileInputs();
    },

    {{-- Split the queue across the two inputs the controller already expects. --}}
    syncFileInputs() {
        const cover = new DataTransfer();
        const gallery = new DataTransfer();

        this.newFiles.forEach((item, index) => {
            const target = (!this.coverKept() && index === 0) ? cover : gallery;
            target.items.add(item.file);
        });

        if (this.$refs.coverInput) this.$refs.coverInput.files = cover.files;
        if (this.$refs.galleryInput) this.$refs.galleryInput.files = gallery.files;
    },
    
    removeExistingImage(id) {
        if (!this.removedGalleryIds.includes(id)) {
            this.removedGalleryIds.push(id);
        }
    },

    {{-- Reset all states --}}
    resetGallery() {
        this.removeImageField = false;
        this.existingGallery = [];
        this.removedGalleryIds = [];
        this.newFiles.forEach(item => URL.revokeObjectURL(item.url));
        this.newFiles = [];
        this.dragging = false;
        this.limitNotice = '';
        this.variants = [];
        this.removedVariantIds = [];
        this.syncFileInputs();
    }
}">
    {{-- Page header: title + short blurb (left), Export/Import CSV + Add (right) --}}
    <div class="flex flex-col gap-4 mb-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Kelola Produk</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola inventaris, harga, dan ketersediaan produk toko Anda.</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            {{-- Export / Import CSV --}}
            <div x-data="{ open: false }" class="relative">
                <button type="button" @click="open = !open"
                        class="inline-flex items-center gap-2 h-10 px-4 text-sm font-semibold rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 0 0-2.25 2.25v9a2.25 2.25 0 0 0 2.25 2.25h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25H15M9 12l3 3m0 0 3-3m-3 3V2.25"/></svg>
                    Export / Import
                    <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                </button>
                <div x-show="open" x-cloak @click.outside="open = false" x-transition.opacity.duration.100ms
                     class="absolute right-0 mt-2 w-44 z-50 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xl py-1">
                    <a href="{{ route('admin.products.export') }}"
                       class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        Export CSV
                    </a>
                    <button type="button" @click="open = false; $dispatch('open-import')"
                            class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V3m0 0L7.5 7.5M12 3l4.5 4.5M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5"/></svg>
                        Import CSV
                    </button>
                </div>
            </div>
            {{-- Add --}}
            <button @click="resetGallery(); showModal=true; editMode=false; form={id:null,slug:'',name:'',category_id:'',description:'',price:'',stock:'',reserved_quantity:0,is_featured:false,image:''};"
                    class="inline-flex items-center gap-2 h-10 px-5 bg-primary-600 text-white text-sm font-semibold rounded-xl hover:bg-primary-700 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Tambah Produk
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
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

    @php
        $pageIds = $products->pluck('id')->values();
        $stockTab = request('stock_status', '');
        $stockTabs = ['' => 'Semua', 'in_stock' => 'Tersedia', 'low_stock' => 'Menipis', 'out_of_stock' => 'Habis'];
    @endphp
    <div x-data="adminListView('products')" :class="grid ? 'admin-grid-view' : ''" class="admin-list-card transition-all duration-300">
        <div class="flex flex-col-reverse gap-3 pb-3 sm:flex-row sm:items-center sm:justify-between">
            {{-- Left: search + compact Filter popover + view toggle --}}
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <form method="GET" action="{{ route('admin.products.index') }}" class="relative flex flex-1 items-center gap-2 sm:flex-none" x-data="{ filterOpen: false, loading: false }">
                    {{-- keep the active status tab + current sort when searching/filtering --}}
                    <input type="hidden" name="stock_status" value="{{ request('stock_status') }}">
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                    <input type="hidden" name="direction" value="{{ request('direction') }}">

                    {{-- Search (live AJAX filter) --}}
                    <div class="relative flex-1 min-w-0 sm:flex-none">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 dark:text-slate-500">
                            <svg x-show="!loading" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                            <svg x-show="loading" x-cloak class="w-4 h-4 animate-spin text-primary-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        </span>
                        <input type="text" name="search" data-live-search data-target="#productsTable" value="{{ request('search') }}" placeholder="Cari produk..."
                               autocomplete="off"
                               class="w-full sm:w-80 lg:w-96 h-10 pl-10 pr-3 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all">
                    </div>

                    {{-- Filter button + popover, wrapped so the popover anchors under the button --}}
                    <div class="relative shrink-0">
                    <button type="button" @click="filterOpen = !filterOpen"
                            class="inline-flex items-center gap-1.5 h-10 px-3.5 text-sm font-semibold rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z"/></svg>
                        <span>Filter</span>
                        @if(request()->anyFilled(['category','is_featured']))
                            <span class="w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                        @endif
                    </button>

                    {{-- Opens left-aligned under the button on desktop, right-aligned on
                         mobile, width capped to viewport → never overflows/scrolls. --}}
                    <div x-show="filterOpen" x-cloak @click.outside="filterOpen = false"
                         x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                         class="absolute right-0 sm:right-auto sm:left-0 top-full mt-2 z-50 w-[min(17rem,calc(100vw-2.5rem))] sm:w-72 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl p-3 space-y-3">
                        {{-- Kategori (custom dropdown) --}}
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">Kategori</label>
                            <div x-data="{ open:false, val:'{{ request('category') }}', label:'{{ request('category') ? addslashes($categories->firstWhere('id', request('category'))->name ?? 'Semua Kategori') : 'Semua Kategori' }}' }" class="relative">
                                <input type="hidden" name="category" :value="val">
                                <button type="button" @click="open=!open"
                                        class="w-full flex items-center justify-between gap-2 px-3 py-2 text-sm rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 hover:border-primary-300 dark:hover:border-primary-700 transition-colors">
                                    <span x-text="label" class="truncate"></span>
                                    <svg class="w-4 h-4 shrink-0 text-slate-400 transition-transform duration-200" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                                </button>
                                <div x-show="open" x-cloak @click.outside="open=false" x-transition.opacity.duration.100ms
                                     class="absolute z-50 mt-1 w-full max-h-56 overflow-y-auto rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-lg py-1 custom-scrollbar">
                                    <button type="button" @click="val='';label='Semua Kategori';open=false"
                                            class="w-full text-left px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors" :class="val==='' && 'text-primary-600 dark:text-primary-400 font-semibold'">Semua Kategori</button>
                                    @foreach($categories as $cat)
                                        <button type="button" @click="val='{{ $cat->id }}';label='{{ addslashes($cat->name) }}';open=false"
                                                class="w-full text-left px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors" :class="val==='{{ $cat->id }}' && 'text-primary-600 dark:text-primary-400 font-semibold'">{{ $cat->name }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        {{-- Status Unggulan (custom dropdown) --}}
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">Status Unggulan</label>
                            <div x-data="{ open:false, val:'{{ request('is_featured') }}', label:'{{ request('is_featured')==='1' ? 'Hanya Unggulan' : (request('is_featured')==='0' ? 'Reguler Saja' : 'Semua Status') }}' }" class="relative">
                                <input type="hidden" name="is_featured" :value="val">
                                <button type="button" @click="open=!open"
                                        class="w-full flex items-center justify-between gap-2 px-3 py-2 text-sm rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 hover:border-primary-300 dark:hover:border-primary-700 transition-colors">
                                    <span x-text="label" class="truncate"></span>
                                    <svg class="w-4 h-4 shrink-0 text-slate-400 transition-transform duration-200" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                                </button>
                                <div x-show="open" x-cloak @click.outside="open=false" x-transition.opacity.duration.100ms
                                     class="absolute z-50 mt-1 w-full rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-lg py-1">
                                    <button type="button" @click="val='';label='Semua Status';open=false" class="w-full text-left px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors" :class="val==='' && 'text-primary-600 dark:text-primary-400 font-semibold'">Semua Status</button>
                                    <button type="button" @click="val='1';label='Hanya Unggulan';open=false" class="w-full text-left px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors" :class="val==='1' && 'text-primary-600 dark:text-primary-400 font-semibold'">Hanya Unggulan</button>
                                    <button type="button" @click="val='0';label='Reguler Saja';open=false" class="w-full text-left px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors" :class="val==='0' && 'text-primary-600 dark:text-primary-400 font-semibold'">Reguler Saja</button>
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-2 pt-1">
                            <button type="submit" class="flex-1 px-3 py-2 bg-primary-600 text-white text-sm font-bold rounded-lg hover:bg-primary-700 transition-colors">Terapkan</button>
                            @if(request()->anyFilled(['search','category','is_featured']))
                                <a href="{{ route('admin.products.index') }}" class="px-3 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-sm font-bold rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">Reset</a>
                            @endif
                        </div>
                    </div>
                    </div>
                </form>
                <div class="shrink-0">
                    @include('admin.partials.view-toggle')
                </div>
            </div>

            {{-- Right: status quick-filter tabs (reuse the stock_status query filter) --}}
            <div class="inline-flex items-center h-10 p-1 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 max-w-full overflow-x-auto scrollbar-none shrink-0" role="group" aria-label="Filter stok">
                @foreach($stockTabs as $val => $label)
                    <a href="{{ request()->fullUrlWithQuery(['stock_status' => $val, 'page' => null]) }}"
                       class="inline-flex items-center justify-center h-full px-3.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-all {{ (string) $stockTab === (string) $val ? 'bg-primary-600 text-white shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100/60 dark:hover:bg-slate-800/60' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>
        {{-- Table + pagination (replaced in place by live AJAX search) --}}
        <div id="productsTable">
            @include('admin.products._table')
        </div>

        {{-- Floating bulk-action bar (shares selection state via adminListView) --}}
        @include('admin.partials.bulk-bar', ['route' => route('admin.products.bulk-destroy'), 'noun' => 'produk'])
    </div>

    {{-- Modal --}}
    <div x-show="showModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showModal=false"></div>
        <div class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-lg z-10 max-h-[90vh] flex flex-col border border-slate-200 dark:border-slate-800 transition-all duration-300 overflow-hidden" x-transition>
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 transition-colors" x-text="editMode ? 'Edit Produk' : 'Tambah Produk'"></h3>
            </div>

            {{-- The update URL comes from the named route so it uses the Indonesian
                 resource path (/admin/produk/{slug}); hardcoding /admin/products/
                 here previously 404'd on save. Products are bound by slug. --}}
            <form :action="editMode ? '{{ route('admin.products.update', ['product' => '__SLUG__']) }}'.replace('__SLUG__', form.slug) : '{{ route('admin.products.store') }}'" method="POST" enctype="multipart/form-data" class="flex flex-col min-h-0 overflow-hidden">
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
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Stok Tersedia</label>
                            <input type="number" name="stock" x-model="form.stock" required min="0" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 transition-all">
                            {{-- The column means "still sellable", not "in the warehouse":
                                 an order reserves its units the moment it is placed. Saying so
                                 here is what stops a stocktake being typed straight into it. --}}
                            <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400 leading-snug"
                               x-show="!editMode || !form.reserved_quantity">
                                Jumlah yang masih bisa dipesan, bukan jumlah di gudang.
                            </p>
                            <template x-if="editMode && form.reserved_quantity">
                                <p class="mt-1 text-[11px] text-indigo-600 dark:text-indigo-400 leading-snug">
                                    <span x-text="form.reserved_quantity"></span> unit sedang ditahan pesanan aktif,
                                    jadi total di gudang <span class="font-bold" x-text="Number(form.stock || 0) + Number(form.reserved_quantity)"></span>.
                                </p>
                            </template>
                        </div>
                    </div>
                    {{-- Product photos: one dropzone, first photo is the cover --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 transition-colors">Foto Produk</label>
                                <p class="text-[10px] text-slate-500">Foto pertama otomatis jadi sampul. Bisa pilih beberapa sekaligus atau seret ke sini.</p>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 rounded-full transition-colors shrink-0"
                                  x-text="((coverKept() ? 1 : 0) + keptGallery().length + newFiles.length) + '/7'"></span>
                        </div>

                        {{-- Both inputs stay hidden; their FileLists are written by syncFileInputs() --}}
                        <input type="file" name="image" x-ref="coverInput" class="hidden" accept="image/*">
                        <input type="file" name="gallery[]" x-ref="galleryInput" class="hidden" accept="image/*" multiple>
                        <input type="file" x-ref="pickInput" class="hidden" accept="image/*" multiple
                               @change="addFiles($event.target.files); $event.target.value = ''">
                        <input type="hidden" name="remove_image" :value="removeImageField ? '1' : '0'">

                        <template x-for="id in removedGalleryIds" :key="'rem-'+id">
                            <input type="hidden" name="removed_gallery_ids[]" :value="id">
                        </template>

                        <div class="rounded-2xl border-2 border-dashed p-3 transition-colors"
                             :class="dragging
                                ? 'border-primary-400 bg-primary-50/60 dark:bg-primary-950/30'
                                : 'border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950'"
                             @dragover.prevent="dragging = true"
                             @dragenter.prevent="dragging = true"
                             @dragleave.prevent="dragging = false"
                             @drop.prevent="dragging = false; addFiles($event.dataTransfer.files)">

                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                                {{-- Saved cover --}}
                                <template x-if="coverKept()">
                                    <div class="relative aspect-square rounded-xl overflow-hidden border border-primary-200 dark:border-primary-900 bg-white dark:bg-slate-900 group">
                                        <img :src="'/storage/' + form.image" class="w-full h-full object-cover">
                                        <span class="absolute bottom-1 left-1 px-1.5 py-0.5 text-[9px] font-bold bg-primary-600 text-white rounded-md">SAMPUL</span>
                                        <button type="button" @click="removeCover()"
                                                class="absolute top-1 right-1 p-1 bg-red-500 text-white rounded-md opacity-0 group-hover:opacity-100 transition-all z-10 shadow-sm hover:bg-red-600">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </button>
                                    </div>
                                </template>

                                {{-- Saved gallery --}}
                                <template x-for="img in existingGallery" :key="img.id">
                                    <template x-if="!removedGalleryIds.includes(img.id)">
                                        <div class="relative aspect-square rounded-xl overflow-hidden border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 group transition-all">
                                            <img :src="'/storage/' + img.image_path" class="w-full h-full object-cover">
                                            <button type="button" @click="removeExistingImage(img.id); syncFileInputs()"
                                                    class="absolute top-1 right-1 p-1 bg-red-500 text-white rounded-md opacity-0 group-hover:opacity-100 transition-all z-10 shadow-sm hover:bg-red-600">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                        </div>
                                    </template>
                                </template>

                                {{-- Newly picked photos --}}
                                <template x-for="(item, index) in newFiles" :key="item.url">
                                    <div class="relative aspect-square rounded-xl overflow-hidden border border-primary-100 dark:border-primary-900 bg-white dark:bg-slate-900 group transition-all">
                                        <img :src="item.url" class="w-full h-full object-cover">
                                        <span x-show="!coverKept() && index === 0" class="absolute bottom-1 left-1 px-1.5 py-0.5 text-[9px] font-bold bg-primary-600 text-white rounded-md">SAMPUL</span>
                                        <button type="button" @click="removeNewFile(index)"
                                                class="absolute top-1 right-1 p-1 bg-red-500 text-white rounded-md opacity-0 group-hover:opacity-100 transition-all z-10 shadow-sm hover:bg-red-600">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </button>
                                    </div>
                                </template>

                                {{-- Add tile --}}
                                <button type="button" x-show="roomLeft() > 0" @click="$refs.pickInput.click()"
                                        class="aspect-square flex flex-col items-center justify-center gap-1 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800/50 hover:border-primary-300 transition-colors duration-200">
                                    <svg class="w-5 h-5 text-slate-400 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                    <span class="text-[9px] font-semibold text-slate-400 dark:text-slate-600" x-text="'Sisa ' + roomLeft()"></span>
                                </button>
                            </div>

                            <p x-show="(coverKept() ? 1 : 0) + keptGallery().length + newFiles.length === 0"
                               class="text-center text-xs text-slate-500 dark:text-slate-500 py-4">
                                Seret foto ke sini, atau klik kotak <span class="font-semibold">+</span> untuk memilih beberapa foto sekaligus.
                            </p>
                        </div>

                        <p x-show="limitNotice" x-cloak x-text="limitNotice" class="mt-2 text-[11px] font-medium text-amber-600 dark:text-amber-500"></p>
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
                                    <button type="button" @click="removeVariant(index)" class="p-2 mb-0.5 text-red-400 hover:text-red-600 transition-colors bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-700" title="Hapus">
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

                <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3 bg-white dark:bg-slate-900">
                    <button type="button" @click="showModal=false" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition-colors">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-primary-600 text-white text-sm font-semibold rounded-xl hover:bg-primary-700 transition-all duration-200">Simpan</button>
                </div>
            </form>
        </div>
        </div>
    </div>
</div>

{{-- Import CSV modal (opens via $dispatch('open-import') or when validation fails) --}}
<div x-data="{ open: {{ $errors->has('file') ? 'true' : 'false' }} }" x-show="open" x-cloak
     @open-import.window="open = true"
     class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="open = false"></div>
    <div class="relative z-10 w-full max-w-md rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-2xl">
        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-1">Impor Produk (CSV)</h3>
        <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Kolom: <code class="text-xs bg-slate-100 dark:bg-slate-800 px-1 py-0.5 rounded">name, slug, category, price, stock, is_available, is_featured, description</code>. Baris dicocokkan berdasarkan <b>slug</b> (diperbarui) atau dibuat baru; kategori harus sudah ada.</p>
        <form method="POST" action="{{ route('admin.products.import') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="file" name="file" accept=".csv,text/csv" required
                   class="block w-full text-sm text-slate-600 dark:text-slate-300 cursor-pointer rounded-xl border border-slate-200 dark:border-slate-800 p-2 file:mr-3 file:rounded-lg file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-700 dark:file:bg-primary-950/40 dark:file:text-primary-400 hover:file:bg-primary-100">
            @error('file') <p class="text-red-500 text-xs font-bold">{{ $message }}</p> @enderror
            <div class="flex items-center justify-between gap-3 pt-2">
                <a href="{{ route('admin.products.export') }}" class="text-xs font-semibold text-primary-600 dark:text-primary-400 hover:underline">Unduh contoh format</a>
                <div class="flex gap-2">
                    <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-primary-600 text-white text-sm font-semibold rounded-xl hover:bg-primary-700 transition-all">Impor</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
