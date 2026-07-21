@extends('layouts.app')
@section('title', $product->name)
@section('meta_description', Str::limit($product->description, 160))

@section('og_title', $product->name . ' — Gegares')
@section('og_description', Str::limit($product->description, 150))
@section('og_image', $product->image ? asset('storage/' . $product->image) : asset('images/logo.png'))
@section('content')
@php
    // Store WhatsApp number for the out-of-stock pre-order button (same
    // normalisation the contact page uses: strip non-digits, 0xxx → 62xxx).
    $store = new \Illuminate\Support\Fluent(\Illuminate\Support\Facades\Cache::remember('store_settings', 86400, fn () => (\App\Models\StoreSetting::first() ?? new \App\Models\StoreSetting())->toArray()));
    $preorderWa = preg_replace('/[^0-9]/', '', $store->contact_whatsapp ?? $store->contact_phone ?? '6281234567890');
    if (str_starts_with($preorderWa, '0')) {
        $preorderWa = '62' . substr($preorderWa, 1);
    }
    $preorderText = "Halo Gegares, saya ingin *pre-order* produk *{$product->name}* yang sedang habis stok. Apakah masih bisa dipesan?\n\nTautan produk: " . route('products.show', $product);
    $preorderUrl = 'https://wa.me/' . $preorderWa . '?text=' . rawurlencode($preorderText);
@endphp
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-10" x-data="{ reviewImage: null }">
    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-1.5 text-sm mb-8">
        <a href="{{ route('home') }}" class="text-slate-400 dark:text-slate-500 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Beranda</a>
        <svg class="w-3.5 h-3.5 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
        <a href="{{ route('products.index') }}" class="text-slate-400 dark:text-slate-500 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Produk</a>
        <svg class="w-3.5 h-3.5 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold truncate max-w-[200px]">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-14">
        {{-- ═══ Image Gallery ═══ --}}
        @php
            $allImages = collect();
            if($product->image) {
                $allImages->push(asset('storage/' . $product->image));
            }
            foreach($product->images as $img) {
                $allImages->push(asset('storage/' . $img->image_path));
            }
        @endphp

        <div x-data="{ 
                activeIndex: 0, 
                isOpen: false, 
                images: @js($allImages),
                next() { this.activeIndex = (this.activeIndex + 1) % this.images.length },
                prev() { this.activeIndex = (this.activeIndex - 1 + this.images.length) % this.images.length }
            }" 
            @keyup.escape.window="isOpen = false"
            @keyup.right.window="if(isOpen) next()"
            @keyup.left.window="if(isOpen) prev()">
            
            {{-- Main Image --}}
            <div class="relative group">
                <div @click="isOpen = true" class="aspect-square rounded-2xl overflow-hidden bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800/60 shadow-sm cursor-zoom-in {{ $product->isOutOfStock() ? 'product-out-of-stock' : '' }}">
                    <template x-if="images.length > 0">
                        <img :src="images[activeIndex]" alt="{{ $product->name }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                    </template>
                    <template x-if="images.length === 0">
                        @include('components.image-placeholder')
                    </template>

                    {{-- Gradient overlay --}}
                    <div class="absolute inset-0 bg-linear-to-t from-black/10 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

                    @if($product->isOutOfStock())
                        <div class="absolute top-4 left-4">
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold bg-red-500 text-white rounded-xl shadow-lg">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z" clip-rule="evenodd"/></svg>
                                Stok Habis
                            </span>
                        </div>
                    @endif

                    {{-- Image counter --}}
                    <template x-if="images.length > 1">
                        <div class="absolute bottom-4 left-4 px-2.5 py-1 bg-black/50 backdrop-blur-sm text-white text-[11px] font-bold rounded-lg">
                            <span x-text="activeIndex + 1"></span>/<span x-text="images.length"></span>
                        </div>
                    </template>
                </div>

                {{-- Zoom trigger --}}
                <button @click="isOpen = true" 
                        class="absolute bottom-4 right-4 p-2.5 bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm text-slate-600 dark:text-slate-300 rounded-xl shadow-md border border-slate-200/50 dark:border-slate-700/50 opacity-0 group-hover:opacity-100 transition-all duration-300 hover:text-primary-600 dark:hover:text-primary-400 active:scale-95">
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                    </svg>
                </button>

                {{-- Nav arrows --}}
                <template x-if="images.length > 1">
                    <div>
                        <button @click.stop="prev()" class="absolute left-3 top-1/2 -translate-y-1/2 p-2 bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm text-slate-600 dark:text-slate-300 rounded-xl shadow-md opacity-0 group-hover:opacity-100 transition-all duration-300 hover:bg-white dark:hover:bg-slate-700 active:scale-95">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                        </button>
                        <button @click.stop="next()" class="absolute right-3 top-1/2 -translate-y-1/2 p-2 bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm text-slate-600 dark:text-slate-300 rounded-xl shadow-md opacity-0 group-hover:opacity-100 transition-all duration-300 hover:bg-white dark:hover:bg-slate-700 active:scale-95">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                        </button>
                    </div>
                </template>
            </div>

            {{-- Thumbnails --}}
            <template x-if="images.length > 1">
                <div class="grid grid-cols-5 gap-2.5 mt-3">
                    <template x-for="(img, index) in images" :key="index">
                        <button @click="activeIndex = index" 
                                class="aspect-square rounded-xl overflow-hidden border-2 transition-all duration-200"
                                :class="activeIndex === index ? 'border-primary-500 ring-2 ring-primary-200 dark:ring-primary-800/50 shadow-sm' : 'border-transparent hover:border-slate-200 dark:hover:border-slate-700 opacity-60 hover:opacity-100'">
                            <img :src="img" alt="" class="w-full h-full object-cover">
                        </button>
                    </template>
                </div>
            </template>

            {{-- Fullscreen Slideshow Modal --}}
            <div x-show="isOpen" 
                 x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-100 flex items-center justify-center bg-slate-900/95 backdrop-blur-md p-4 lg:p-12" style="display: none;">
                <button @click="isOpen = false" class="absolute top-6 right-6 p-3 text-white/70 hover:text-white transition-colors z-110">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
                <button @click="prev()" class="absolute left-6 top-1/2 -translate-y-1/2 p-4 text-white/50 hover:text-white hover:bg-white/10 rounded-full transition-all z-110">
                    <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
                </button>
                <button @click="next()" class="absolute right-6 top-1/2 -translate-y-1/2 p-4 text-white/50 hover:text-white hover:bg-white/10 rounded-full transition-all z-110">
                    <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                </button>
                <div class="w-full h-full flex flex-col items-center justify-center gap-8 text-center">
                    <div class="relative max-w-5xl max-h-[70vh] w-full flex items-center justify-center">
                        <img :src="images[activeIndex]" :key="activeIndex"
                             x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             class="max-w-full max-h-[70vh] object-contain rounded-lg shadow-2xl">
                    </div>
                    <div class="flex items-center gap-3 overflow-x-auto pb-2 px-4 max-w-full no-scrollbar">
                        <template x-for="(img, index) in images" :key="'modal-'+index">
                            <button @click="activeIndex = index" class="w-16 h-16 rounded-lg overflow-hidden border-2 transition-all shrink-0"
                                    :class="activeIndex === index ? 'border-primary-500 scale-105 shadow-lg' : 'border-white/20 opacity-50 hover:opacity-100'">
                                <img :src="img" alt="" class="w-full h-full object-cover">
                            </button>
                        </template>
                    </div>
                    <div class="text-white/60 text-sm font-medium"><span x-text="activeIndex + 1" class="text-white"></span> / <span x-text="images.length"></span></div>
                </div>
            </div>
        </div>

        {{-- ═══ Product Info ═══ --}}
        {{-- Alpine state sits on the whole info column so the price block near the
             top can react to the variant chosen further down. --}}
        <div class="lg:py-2" x-data="{
            qty: 1,
            {{-- 0 when the admin switched the product off, so the toggle really blocks buying --}}
            baseMax: {{ $product->isOutOfStock() ? 0 : $product->stock }},
            basePrice: {{ $product->price }},
            variants: {{ $product->variants->toJson() }},
            selectedVariantId: null,
            loading: false,

            get max() {
                {{-- Variants are optional: with none picked the customer buys the
                     base product at its own unit price and stock. --}}
                if (this.selectedVariantId) {
                    const v = this.variants.find(v => v.id === this.selectedVariantId);
                    return v ? v.stock : 0;
                }
                return this.baseMax;
            },
            get hasBuyableVariant() {
                return this.variants.some(v => v.stock > 0);
            },
            get currentPrice() {
                {{-- A variant price REPLACES the base price; blank means 'same as base'. --}}
                if (this.variants.length > 0 && this.selectedVariantId) {
                    const v = this.variants.find(v => v.id === this.selectedVariantId);
                    return Number(v.price) > 0 ? Number(v.price) : this.basePrice;
                }
                return this.basePrice;
            },
            get currentPriceLabel() {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(this.currentPrice);
            },
            get canAddToCart() {
                return this.max > 0;
            }
        }">
            {{-- Category --}}
            <a href="{{ route('products.index', ['category' => $product->category->slug ?? '']) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1 bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 text-[11px] font-bold rounded-lg uppercase tracking-wide hover:bg-primary-100 dark:hover:bg-primary-900/50 transition-colors">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path stroke-linecap="round" d="M6 6h.008v.008H6V6Z"/></svg>
                {{ $product->category->name }}
            </a>
            
            {{-- Name --}}
            <h1 class="mt-4 text-2xl lg:text-3xl font-black text-slate-900 dark:text-slate-100 leading-tight">{{ $product->name }}</h1>

            {{-- Rating --}}
            <div class="flex items-center gap-3 mt-3">
                <div class="flex items-center gap-0.5">
                    @for($i = 1; $i <= 5; $i++)
                        <svg class="w-4.5 h-4.5 {{ ($product->rating_count ?? 0) > 0 && $i <= round($product->rating_avg ?? 0) ? 'text-amber-400' : 'text-slate-200 dark:text-slate-700' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endfor
                </div>
                @if(($product->rating_count ?? 0) > 0)
                    <span class="text-sm text-slate-500 dark:text-slate-400 font-medium">{{ number_format($product->rating_avg, 1) }} <span class="text-slate-400 dark:text-slate-500">·</span> {{ $product->rating_count }} ulasan</span>
                @else
                    <span class="text-sm text-slate-400 dark:text-slate-500 italic">Belum ada ulasan</span>
                @endif
            </div>

            {{-- Divider --}}
            <div class="my-5 border-t border-slate-100 dark:border-slate-800/50"></div>

            {{-- Price Block --}}
            <div class="flex items-end gap-3">
                {{-- Follows the selected variant; the server value shows until Alpine boots. --}}
                <span class="text-3xl font-black text-slate-900 dark:text-slate-100" x-text="currentPriceLabel">{{ $product->formatted_price }}</span>
                <span class="text-sm text-slate-400 dark:text-slate-500 font-medium mb-1">/ pcs</span>
            </div>

            {{-- Stock Status --}}
            <div class="mt-3">
                @if($product->isOutOfStock())
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-red-50 dark:bg-red-900/20 rounded-lg">
                        <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                        <span class="text-xs font-bold text-red-600 dark:text-red-400">Stok Habis</span>
                    </div>
                @elseif($product->isLowStock())
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-amber-50 dark:bg-amber-900/20 rounded-lg">
                        <span class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></span>
                        <span class="text-xs font-bold text-amber-600 dark:text-amber-400">Stok menipis — segera habis!</span>
                    </div>
                @else
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg">
                        <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">Stok tersedia</span>
                    </div>
                @endif
            </div>

            {{-- Description --}}
            <div class="mt-6">
                <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Deskripsi</h3>
                <div class="prose prose-sm prose-slate dark:prose-invert max-w-none">
                    <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">{{ $product->description }}</p>
                </div>
            </div>

            {{-- Divider --}}
            <div class="my-6 border-t border-slate-100 dark:border-slate-800/50"></div>

            {{-- Add to Cart --}}
            <div class="space-y-4">

                <template x-if="variants.length > 0">
                    <div class="pt-2">
                        <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-3">
                            Pilih Variasi <span class="normal-case tracking-normal font-medium text-slate-400 dark:text-slate-500">(opsional — kosongkan untuk harga satuan)</span>
                        </h3>
                        <div class="flex flex-wrap gap-2.5">
                            <template x-for="variant in variants" :key="variant.id">
                                {{-- Clicking the active chip clears it, so the customer can go back to the base price. --}}
                                <button @click="if(variant.stock > 0) { selectedVariantId = (selectedVariantId === variant.id ? null : variant.id); qty = 1; }"
                                        type="button"
                                        :disabled="variant.stock <= 0"
                                        :class="[
                                            'px-4 py-2 border rounded-xl text-sm transition-all focus:outline-none flex flex-col items-center justify-center min-w-[80px]',
                                            selectedVariantId === variant.id ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 font-bold shadow-sm ring-1 ring-primary-500' : (variant.stock > 0 ? 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:border-primary-300 dark:hover:border-primary-700' : 'border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 text-slate-400 dark:text-slate-600 cursor-not-allowed')
                                        ]">
                                    <span x-text="variant.name" class="font-semibold block"></span>
                                    {{-- The variant's own price, not a surcharge. --}}
                                    <span x-show="Number(variant.price) > 0" class="text-[10px] mt-0.5 font-medium" :class="selectedVariantId === variant.id ? '' : 'text-emerald-600 dark:text-emerald-400'" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(variant.price)"></span>
                                    <span x-show="variant.stock <= 0" class="text-[10px] mt-0.5 text-red-500 font-bold">Habis</span>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>

                <template x-if="canAddToCart">
                    <div class="space-y-4">
                        {{-- Quantity --}}
                        <div class="flex items-center gap-4 py-2">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Jumlah</span>
                            <div class="flex items-center bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/50 rounded-xl overflow-hidden">
                                <button @click="qty = Math.max(1, qty - 1)" type="button"
                                        class="w-10 h-10 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors text-lg font-medium active:scale-95">−</button>
                                <input type="number" x-model.number="qty" min="1" :max="max"
                                       class="w-12 h-10 text-center bg-transparent text-sm font-bold text-slate-900 dark:text-slate-100 focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                <button @click="qty = Math.min(max, qty + 1)" type="button"
                                        class="w-10 h-10 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors text-lg font-medium active:scale-95">+</button>
                            </div>
                            <span class="text-xs text-slate-400 dark:text-slate-500" x-show="qty > 1 && variants.length === 0">
                                Total: <span class="font-bold text-slate-600 dark:text-slate-300" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(basePrice * qty)"></span>
                            </span>
                            <span class="text-xs text-slate-400 dark:text-slate-500" x-show="qty > 1 && variants.length > 0">
                                Total: <span class="font-bold text-slate-600 dark:text-slate-300" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(currentPrice * qty)"></span>
                            </span>
                        </div>

                        {{-- Buttons --}}
                        <div class="flex gap-2.5">
                            @auth
                                <button @click="loading = true; Livewire.dispatch('add-to-cart-qty', { productId: {{ $product->id }}, quantity: qty, variantId: selectedVariantId })"
                                        @cart-updated.window="loading = false"
                                        :disabled="loading"
                                        :class="loading ? 'opacity-70 cursor-not-allowed' : ''"
                                        class="flex-1 flex items-center justify-center gap-2.5 py-3.5 bg-primary-600 text-white font-bold rounded-xl hover:bg-primary-700 transition-all duration-200 active:scale-[0.98] shadow-lg shadow-primary-600/20">
                                    <svg x-show="loading" class="animate-spin w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display:none;">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <svg x-show="!loading" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                                    <span x-text="loading ? 'Memproses...' : 'Tambah ke Keranjang'">Tambah ke Keranjang</span>
                                </button>
                            @else
                                <a href="{{ route('login') }}" class="flex-1 flex items-center justify-center gap-2.5 py-3.5 bg-primary-600 text-white font-bold rounded-xl hover:bg-primary-700 transition-all active:scale-[0.98] shadow-lg shadow-primary-600/20">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                                    Masuk untuk Belanja
                                </a>
                            @endauth

                            <div class="flex gap-2">
                                @auth
                                    @livewire('toggle-wishlist', ['productId' => $product->id, 'variant' => 'button'], 'detail-wishlist-' . $product->id)
                                @else
                                    <a href="{{ route('login') }}" class="flex items-center justify-center p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-400 dark:text-slate-500 hover:border-red-200 dark:hover:border-red-800 hover:text-red-500 dark:hover:text-red-400 transition-all duration-300" title="Masuk untuk wishlist">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
                                    </a>
                                @endauth

                                <button @click="if (navigator.share) { navigator.share({ title: '{{ $product->name }} — Gegares', text: '{{ Str::limit($product->description, 100) }}', url: window.location.href }).catch(err => console.log('Share failed', err)); } else { navigator.clipboard.writeText(window.location.href); window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Tautan produk berhasil disalin!' } })); }" 
                                        class="flex items-center justify-center p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-400 dark:text-slate-500 hover:border-primary-200 dark:hover:border-primary-800 hover:text-primary-500 dark:hover:text-primary-400 transition-all duration-300" 
                                        title="Bagikan Produk">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
                {{-- Base stock is gone but a variant is still buyable. --}}
                <template x-if="!canAddToCart && !selectedVariantId && hasBuyableVariant">
                    <button disabled class="w-full py-3.5 bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 font-bold rounded-xl cursor-not-allowed">
                        Pilih Variasi Terlebih Dahulu
                    </button>
                </template>
                {{-- Nothing buyable at all — base and every variant are out. --}}
                <template x-if="!canAddToCart && !hasBuyableVariant">
                    {{-- Out of stock: offer a WhatsApp pre-order instead of a dead button. --}}
                    <div class="space-y-2">
                        <a href="{{ $preorderUrl }}" target="_blank" rel="noopener"
                           class="w-full flex items-center justify-center gap-2.5 py-3.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 transition-all duration-200 active:scale-[0.98] shadow-lg shadow-emerald-600/20">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.372-.025-.521-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.71.306 1.263.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            Pre-order via WhatsApp
                        </a>
                        <p class="text-center text-xs text-slate-400 dark:text-slate-500">Stok sedang habis — pesan lebih dulu lewat WhatsApp, kami kabari saat tersedia.</p>
                    </div>
                </template>
            </div>

            {{-- Trust Signals --}}
            <div class="mt-8 grid grid-cols-3 gap-3">
                <div class="flex flex-col items-center gap-1.5 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/30 border border-slate-100 dark:border-slate-800/40">
                    <svg class="w-5 h-5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                    <span class="text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center">Aman & Higienis</span>
                </div>
                <div class="flex flex-col items-center gap-1.5 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/30 border border-slate-100 dark:border-slate-800/40">
                    <svg class="w-5 h-5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                    <span class="text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center">Pengiriman Cepat</span>
                </div>
                <div class="flex flex-col items-center gap-1.5 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/30 border border-slate-100 dark:border-slate-800/40">
                    <svg class="w-5 h-5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z"/></svg>
                    <span class="text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center">Fresh & Homemade</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ Customer Reviews ═══ --}}
    <section class="mt-16 lg:mt-20 pt-10 border-t border-slate-100 dark:border-slate-800/50">
        @if($product->reviews->count())
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-xl font-black text-slate-900 dark:text-slate-100 flex items-center gap-2.5">
                    Ulasan Pelanggan
                    <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-lg bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 text-sm font-bold">{{ $product->rating_count }}</span>
                </h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($product->reviews as $review)
                    <div class="p-5 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800/60 hover:border-primary-100 dark:hover:border-primary-900/30 transition-all duration-300">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-linear-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-sm font-bold shadow-sm">
                                    {{ strtoupper(substr($review->user->name ?? 'A', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ $review->user->name ?? 'Anonim' }}</p>
                                    <p class="text-[11px] text-slate-400 dark:text-slate-500">{{ \Carbon\Carbon::parse($review->created_at)->diffForHumans() }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-0.5 shrink-0">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-3.5 h-3.5 {{ $i <= $review->rating ? 'text-amber-400' : 'text-slate-200 dark:text-slate-700' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endfor
                            </div>
                        </div>
                        @if($review->comment)
                            <p class="mt-3 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">{{ $review->comment }}</p>
                        @endif
                        @if($review->image)
                            <div class="mt-3 shrink-0 w-20 h-20" x-data="{ imgOk: true }">
                                <button type="button"
                                        @click="imgOk && (reviewImage = '{{ asset('storage/' . $review->image) }}')"
                                        class="rounded-xl overflow-hidden w-full h-full border border-slate-100 dark:border-slate-800/60 hover:border-primary-200 dark:hover:border-primary-800 transition-all group"
                                        :class="imgOk ? 'cursor-zoom-in' : 'cursor-default'">
                                    <img x-show="imgOk"
                                         src="{{ asset('storage/' . $review->image) }}"
                                         alt="Foto ulasan"
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                         x-on:error="imgOk = false">
                                    <div x-show="!imgOk" class="w-full h-full flex items-center justify-center bg-slate-100 dark:bg-slate-800/60">
                                        <svg class="w-8 h-8 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                                        </svg>
                                    </div>
                                </button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-16 flex flex-col items-center text-center">
                <div class="w-16 h-16 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800/40 flex items-center justify-center mb-5">
                    <svg class="w-8 h-8 text-slate-200 dark:text-slate-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.499z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">Belum Ada Ulasan</h3>
                <p class="mt-1.5 text-sm text-slate-400 dark:text-slate-500 max-w-xs">Jadilah yang pertama mengulas <span class="font-semibold text-slate-600 dark:text-slate-300">{{ $product->name }}</span>!</p>
            </div>
        @endif
    </section>

    {{-- ═══ Related Products ═══ --}}
    @if($relatedProducts->count())
        <section class="mt-16 lg:mt-20">
            <h2 class="text-xl font-black text-slate-900 dark:text-slate-100 mb-6">Produk Serupa</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 lg:gap-5">
                @foreach($relatedProducts as $rp)
                    @include('components.product-card-grid', ['product' => $rp])
                @endforeach
            </div>
        </section>
    @endif

    {{-- Review Image Lightbox --}}
    <template x-teleport="body">
        <div x-show="reviewImage" 
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-110 flex items-center justify-center p-4 lg:p-12 overflow-hidden" style="display: none;" @keydown.escape.window="reviewImage = null">
            <div class="absolute inset-0 bg-slate-900/90 backdrop-blur-md" @click="reviewImage = null"></div>
            <button @click="reviewImage = null" class="absolute top-6 right-6 p-3 text-white/70 hover:text-white transition-colors z-120">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <div class="relative max-w-5xl max-h-full w-full flex items-center justify-center z-120">
                <img :src="reviewImage" class="max-w-full max-h-[90vh] object-contain rounded-xl shadow-2xl" @click.stop>
            </div>
        </div>
    </template>
</div>
@endsection
