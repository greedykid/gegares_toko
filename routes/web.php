<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
| URLs are Indonesian; route NAMES stay English so every route()/redirect()
| call in the app keeps working. Two paths are deliberately left in English
| because they are registered in external dashboards and renaming them would
| break the integration silently:
|   - auth/google*  → redirect URI configured in Google Cloud Console
|   - webhook/*     → callback URLs configured in Pakasir & Biteship
*/

// ─── Public Routes ───
Route::get('/', [HomeController::class, 'index'])->name('home');

// Google Authentication (path fixed by Google Cloud Console — do not translate)
Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

Route::get('/produk', [ProductController::class, 'index'])->name('products.index');
Route::get('/produk/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/tentang', fn() => view('pages.about'))->name('about');
Route::get('/kontak', fn() => view('pages.contact'))->name('contact');

// ─── Auth Routes (Guest) ───
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/masuk', [LoginController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/daftar', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/daftar', [RegisterController::class, 'register'])->middleware('throttle:5,1');

    // Forgot Password Routes
    Route::get('/lupa-kata-sandi', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/lupa-kata-sandi', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/atur-ulang-kata-sandi/{token}', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/atur-ulang-kata-sandi', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'reset'])->name('password.update');
});

// ─── Admin Auth ───
// /admin is the admin login page itself, per request.
Route::get('/admin', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin', [AdminLoginController::class, 'login'])->middleware('throttle:5,1')->name('admin.login.submit');

// ─── Authenticated User Routes ───
Route::middleware(['auth', 'check_phone'])->group(function () {
    Route::post('/keluar', [LoginController::class, 'logout'])->name('logout');

    Route::get('/pemesanan', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/pemesanan', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/pesanan', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/pesanan/{order}/lacak', [OrderController::class, 'getTracking'])->name('orders.tracking');
    Route::get('/pesanan/{order}/pembayaran', [OrderController::class, 'payment'])->name('orders.payment');
    Route::get('/pesanan/{order}/status', [OrderController::class, 'checkStatus'])->name('orders.status');
    Route::post('/pesanan/{order}/selesai', [OrderController::class, 'complete'])->name('orders.complete');

    Route::get('/favorit', fn() => redirect('/#wishlist'))->name('wishlist');

    // Profile Settings
    Route::get('/pengaturan', [ProfileController::class, 'index'])->name('settings.index');
    Route::get('/pengaturan/lengkapi-profil', [ProfileController::class, 'showCompleteProfile'])->name('settings.complete-profile');
    Route::post('/pengaturan/lengkapi-profil', [ProfileController::class, 'updateCompleteProfile'])->name('settings.update-complete-profile');
    Route::put('/pengaturan', [ProfileController::class, 'update'])->name('settings.update');
    Route::put('/pengaturan/notifikasi', [ProfileController::class, 'updateNotifications'])->name('settings.notifications');
    Route::put('/pengaturan/kata-sandi', [ProfileController::class, 'updatePassword'])->name('settings.password');
    Route::delete('/pengaturan/foto-profil', [ProfileController::class, 'deleteAvatar'])->name('settings.delete-avatar');
});

// ─── Admin Routes ───
// Resource URIs are Indonesian; ->names() keeps the English route names and
// ->parameters() keeps the {product}/{order}/... bindings the controllers expect.
Route::prefix('admin')->middleware(['auth', 'is_admin'])->name('admin.')->group(function () {
    Route::post('/keluar', [AdminLoginController::class, 'logout'])->name('logout');
    Route::get('/dasbor', [DashboardController::class, 'index'])->name('dashboard');

    Route::patch('/kategori/{category}/ubah-status', [AdminCategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
    Route::resource('/kategori', AdminCategoryController::class)
        ->except(['create', 'edit'])
        ->names('categories')
        ->parameters(['kategori' => 'category']);

    Route::patch('/produk/{product}/ubah-unggulan', [AdminProductController::class, 'toggleFeatured'])->name('products.toggle-featured');
    Route::resource('/produk', AdminProductController::class)
        ->except(['create', 'edit'])
        ->names('products')
        ->parameters(['produk' => 'product']);

    Route::get('/pengaturan/toko', [DashboardController::class, 'storeSettings'])->name('settings.store');
    Route::get('/pengaturan/konten', [DashboardController::class, 'contentSettings'])->name('settings.content');

    // Declared before the resource so /pesanan/{order} cannot swallow these.
    Route::post('/pesanan/{order}/proses-pengiriman', [AdminOrderController::class, 'processShipping'])->name('orders.process-shipping');
    Route::get('/pesanan/{order}/lacak', [AdminOrderController::class, 'getTracking'])->name('orders.tracking');
    Route::get('/pesanan/ekspor/csv', [AdminOrderController::class, 'exportCsv'])->name('orders.export.csv');
    Route::get('/pesanan/laporan/cetak', [AdminOrderController::class, 'report'])->name('orders.report');
    Route::resource('/pesanan', AdminOrderController::class)
        ->only(['index', 'show', 'update'])
        ->names('orders')
        ->parameters(['pesanan' => 'order']);

    Route::get('/ulasan', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::patch('/ulasan/{review}', [AdminReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/ulasan/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');

    Route::resource('/pengguna', AdminUserController::class)
        ->except(['create', 'edit'])
        ->names('users')
        ->parameters(['pengguna' => 'user']);

    // ─── Promo ───
    Route::resource('/kupon', \App\Http\Controllers\Admin\CouponController::class)
        ->except(['create', 'edit', 'show'])
        ->names('coupons')
        ->parameters(['kupon' => 'coupon']);
});

// ─── Webhook (paths fixed by Pakasir & Biteship dashboards — do not translate) ───
Route::post('/webhook/midtrans', [WebhookController::class, 'midtrans'])->name('webhook.midtrans');
Route::post('/webhook/pakasir', [WebhookController::class, 'pakasir'])->name('webhook.pakasir');
Route::post('/webhook/biteship', [WebhookController::class, 'biteship'])->name('webhook.biteship');
Route::get('/webhook/biteship', fn() => 'Biteship Webhook is active. Waiting for POST data from Biteship.');
