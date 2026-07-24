<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WebhookController;
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
Route::get('/tentang', fn () => view('pages.about'))->name('about');
Route::get('/kontak', fn () => view('pages.contact'))->name('contact');
Route::get('/info-pengiriman', fn () => view('pages.shipping'))->name('shipping');
Route::get('/kebijakan-privasi', fn () => view('pages.privacy'))->name('privacy');
Route::get('/syarat-ketentuan', fn () => view('pages.terms'))->name('terms');

// ─── Auth Routes (Guest) ───
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/masuk', [LoginController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/daftar', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/daftar', [RegisterController::class, 'register'])->middleware('throttle:5,1');

    // Forgot Password Routes
    Route::get('/lupa-kata-sandi', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/lupa-kata-sandi', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/atur-ulang-kata-sandi/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/atur-ulang-kata-sandi', [ForgotPasswordController::class, 'reset'])->name('password.update');
});

// ─── Admin Auth ───
// /admin is the admin login page itself, per request.
Route::get('/admin', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin', [AdminLoginController::class, 'login'])->middleware('throttle:5,1')->name('admin.login.submit');

// ─── Checkout (guests welcome up to the point of paying) ───
// A guest can browse the checkout, fill the address + shipping steps (held in
// the session), and only has to log in / register at the final step. The order
// itself is placed by store() (logged-in) or resume() (right after a guest logs
// in), both of which stay behind auth.
Route::get('/pemesanan', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/pemesanan/tamu', [CheckoutController::class, 'guestSubmit'])->name('checkout.guestSubmit');
// resume() runs right after a guest logs in. It is deliberately NOT behind
// check_phone: it guards the phone itself so it can re-arm the intended URL and
// come back here after the profile is completed (Google accounts have no phone).
Route::get('/pemesanan/lanjutkan', [CheckoutController::class, 'resume'])
    ->middleware('auth')->name('checkout.resume');

// ─── Authenticated User Routes ───
Route::middleware(['auth', 'check_phone'])->group(function () {
    Route::post('/keluar', [LoginController::class, 'logout'])->name('logout');

    Route::post('/pemesanan', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/pesanan', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/pesanan/{order}/lacak', [OrderController::class, 'getTracking'])->name('orders.tracking');
    Route::get('/pesanan/{order}/pembayaran', [OrderController::class, 'payment'])->name('orders.payment');
    Route::get('/pesanan/{order}/status', [OrderController::class, 'checkStatus'])->name('orders.status');
    Route::post('/pesanan/{order}/selesai', [OrderController::class, 'complete'])->name('orders.complete');

    Route::get('/favorit', fn () => redirect('/#wishlist'))->name('wishlist');

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
    Route::get('/cari', [\App\Http\Controllers\Admin\SearchController::class, 'index'])->name('search');
    Route::post('/keluar', [AdminLoginController::class, 'logout'])->name('logout');
    Route::get('/dasbor', [DashboardController::class, 'index'])->name('dashboard');

    Route::patch('/kategori/{category}/ubah-status', [AdminCategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
    Route::delete('/kategori-massal/hapus', [AdminCategoryController::class, 'bulkDestroy'])->name('categories.bulk-destroy');
    Route::resource('/kategori', AdminCategoryController::class)
        ->except(['create', 'edit'])
        ->names('categories')
        ->parameters(['kategori' => 'category']);

    Route::patch('/produk/{product}/ubah-unggulan', [AdminProductController::class, 'toggleFeatured'])->name('products.toggle-featured');
    Route::patch('/produk/{product}/ubah-ketersediaan', [AdminProductController::class, 'toggleAvailability'])->name('products.toggle-availability');
    Route::delete('/produk-massal/hapus', [AdminProductController::class, 'bulkDestroy'])->name('products.bulk-destroy');
    Route::get('/produk-massal/export', [AdminProductController::class, 'export'])->name('products.export');
    Route::post('/produk-massal/import', [AdminProductController::class, 'import'])->name('products.import');
    Route::resource('/produk', AdminProductController::class)
        ->except(['create', 'edit'])
        ->names('products')
        ->parameters(['produk' => 'product']);

    Route::get('/pengaturan/toko', [DashboardController::class, 'storeSettings'])->name('settings.store');
    Route::get('/pengaturan/konten', [DashboardController::class, 'contentSettings'])->name('settings.content');

    // Declared before the resource so /pesanan/{order} cannot swallow these.
    Route::post('/pesanan/{order}/proses-pengiriman', [AdminOrderController::class, 'processShipping'])->name('orders.process-shipping');
    Route::post('/pesanan/{order}/batalkan-pengiriman', [AdminOrderController::class, 'cancelShipping'])->name('orders.cancel-shipping');
    Route::patch('/pesanan/{order}/tandai-refund', [AdminOrderController::class, 'markRefunded'])->name('orders.mark-refunded');
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
    Route::delete('/ulasan-massal/hapus', [AdminReviewController::class, 'bulkDestroy'])->name('reviews.bulk-destroy');

    Route::delete('/pengguna-massal/hapus', [AdminUserController::class, 'bulkDestroy'])->name('users.bulk-destroy');
    Route::resource('/pengguna', AdminUserController::class)
        ->except(['create', 'edit'])
        ->names('users')
        ->parameters(['pengguna' => 'user']);

    // ─── Promo ───
    Route::delete('/kupon-massal/hapus', [CouponController::class, 'bulkDestroy'])->name('coupons.bulk-destroy');
    Route::resource('/kupon', CouponController::class)
        ->except(['create', 'edit', 'show'])
        ->names('coupons')
        ->parameters(['kupon' => 'coupon']);
});

/*
| ─── Legacy English URLs (301) ───
| These paths were renamed, but old links live outside our control and cannot
| be rewritten retroactively:
|   - every payment link already stored in Pakasir has the return URL baked in
|     (Order::pakasir_link, e.g. redirect=/orders/4/payment), so a customer who
|     finishes paying an older order lands here;
|   - password-reset and order emails already sitting in customers' inboxes.
| The query string is carried over — the chatbot flow depends on ?chatbot_open=1.
*/
$legacy = function (string $path) {
    $query = request()->getQueryString();

    return redirect()->to($query ? "{$path}?{$query}" : $path, 301);
};

Route::get('/products', fn () => $legacy('/produk'));
Route::get('/products/{product}', fn (string $product) => $legacy("/produk/{$product}"))->where('product', '[A-Za-z0-9._-]+');
Route::get('/about', fn () => $legacy('/tentang'));
Route::get('/contact', fn () => $legacy('/kontak'));
Route::get('/login', fn () => $legacy('/masuk'));
Route::get('/register', fn () => $legacy('/daftar'));
Route::get('/forgot-password', fn () => $legacy('/lupa-kata-sandi'));
Route::get('/reset-password/{token}', fn (string $token) => $legacy("/atur-ulang-kata-sandi/{$token}"))->where('token', '[A-Za-z0-9]+');
Route::get('/checkout', fn () => $legacy('/pemesanan'));
Route::get('/wishlist', fn () => $legacy('/favorit'));
Route::get('/settings', fn () => $legacy('/pengaturan'));

Route::get('/orders', fn () => $legacy('/pesanan'));
Route::get('/orders/{order}', fn (string $order) => $legacy("/pesanan/{$order}"))->where('order', '[0-9]+');
Route::get('/orders/{order}/payment', fn (string $order) => $legacy("/pesanan/{$order}/pembayaran"))->where('order', '[0-9]+');
Route::get('/orders/{order}/tracking', fn (string $order) => $legacy("/pesanan/{$order}/lacak"))->where('order', '[0-9]+');
Route::get('/orders/{order}/status', fn (string $order) => $legacy("/pesanan/{$order}/status"))->where('order', '[0-9]+');

Route::get('/admin/login', fn () => $legacy('/admin'));
Route::get('/admin/dashboard', fn () => $legacy('/admin/dasbor'));

// ─── Webhook (paths fixed by Pakasir & Biteship dashboards — do not translate) ───
Route::post('/webhook/pakasir', [WebhookController::class, 'pakasir'])->name('webhook.pakasir');
Route::post('/webhook/biteship', [WebhookController::class, 'biteship'])->name('webhook.biteship');
Route::get('/webhook/biteship', fn () => 'Biteship Webhook is active. Waiting for POST data from Biteship.');
