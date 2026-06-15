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

// ─── Public Routes ───
Route::get('/', [HomeController::class, 'index'])->name('home');

// Google Authentication
Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/about', fn() => view('pages.about'))->name('about');
Route::get('/contact', fn() => view('pages.contact'))->name('contact');

// ─── Auth Routes (Guest) ───
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:5,1');
});

// ─── Admin Auth ───
Route::get('/admin/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login'])->middleware('throttle:5,1')->name('admin.login.submit');

// ─── Authenticated User Routes ───
Route::middleware(['auth', 'check_phone'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/tracking', [OrderController::class, 'getTracking'])->name('orders.tracking');
    Route::get('/orders/{order}/payment', [OrderController::class, 'payment'])->name('orders.payment');
    Route::get('/orders/{order}/status', [OrderController::class, 'checkStatus'])->name('orders.status');
    Route::post('/orders/{order}/complete', [OrderController::class, 'complete'])->name('orders.complete');

    Route::get('/wishlist', fn() => redirect('/#wishlist'))->name('wishlist');

    // Profile Settings
    Route::get('/settings', [ProfileController::class, 'index'])->name('settings.index');
    Route::get('/settings/complete-profile', [ProfileController::class, 'showCompleteProfile'])->name('settings.complete-profile');
    Route::post('/settings/complete-profile', [ProfileController::class, 'updateCompleteProfile'])->name('settings.update-complete-profile');
    Route::put('/settings', [ProfileController::class, 'update'])->name('settings.update');
    Route::put('/settings/notifications', [ProfileController::class, 'updateNotifications'])->name('settings.notifications');
    Route::put('/settings/password', [ProfileController::class, 'updatePassword'])->name('settings.password');
    Route::delete('/settings/avatar', [ProfileController::class, 'deleteAvatar'])->name('settings.delete-avatar');
});

// ─── Admin Routes ───
Route::prefix('admin')->middleware(['auth', 'is_admin'])->name('admin.')->group(function () {
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::patch('/categories/{category}/toggle-status', [AdminCategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
    Route::resource('/categories', AdminCategoryController::class)->except(['create', 'edit']);
    Route::patch('/products/{product}/toggle-featured', [AdminProductController::class, 'toggleFeatured'])->name('products.toggle-featured');
    Route::resource('/products', AdminProductController::class)->except(['create', 'edit']);
    
    Route::get('/settings/store', [DashboardController::class, 'storeSettings'])->name('settings.store');

    Route::post('/orders/{order}/process-shipping', [AdminOrderController::class, 'processShipping'])->name('orders.process-shipping');
    Route::get('/orders/{order}/tracking', [AdminOrderController::class, 'getTracking'])->name('orders.tracking');
    Route::get('/orders/export/csv', [AdminOrderController::class, 'exportCsv'])->name('orders.export.csv');
    Route::get('/orders/report/print', [AdminOrderController::class, 'report'])->name('orders.report');
    Route::resource('/orders', AdminOrderController::class)->only(['index', 'show', 'update']);
    Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::patch('/reviews/{review}', [AdminReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::resource('/users', AdminUserController::class)->except(['create', 'edit']);


    // ─── Promo ───
    Route::resource('/coupons', \App\Http\Controllers\Admin\CouponController::class)->except(['create', 'edit', 'show']);
});

// ─── Webhook ───
Route::post('/webhook/midtrans', [WebhookController::class, 'midtrans'])->name('webhook.midtrans');
Route::post('/webhook/pakasir', [WebhookController::class, 'pakasir'])->name('webhook.pakasir');
Route::post('/webhook/biteship', [WebhookController::class, 'biteship'])->name('webhook.biteship');
Route::get('/webhook/biteship', fn() => 'Biteship Webhook is active. Waiting for POST data from Biteship.');
