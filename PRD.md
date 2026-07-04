# Product Requirement Document (PRD)
## Gegares — Platform E-Commerce Jajanan Pasar Tradisional (Web App)

> Dokumen ini menggambarkan produk **sebagaimana yang benar-benar terimplementasi di dalam codebase** (Laravel 13 + Livewire 4), untuk keperluan skripsi. Fakta di sini diverifikasi langsung dari kode, migration, route, dan service yang ada.

---

## 1. Ringkasan Produk

**Gegares** adalah platform e-commerce berbasis web untuk kurasi dan penjualan **jajanan pasar tradisional Indonesia**. Aplikasi menyediakan alur belanja lengkap end-to-end (katalog → keranjang → checkout → pembayaran → pengiriman → ulasan), panel admin operasional penuh, serta **asisten AI (chatbot)** yang menjawab berbasis data toko yang valid.

### Masalah yang Diselesaikan
- Pembeli sulit menemukan jajanan pasar yang higienis, terkurasi, dan bisa dikirim cepat (instant/same-day) secara online.
- Penjual/UMKM belum punya kanal digital rapi untuk mengelola katalog, stok, promo, pesanan, dan pengiriman dalam satu dashboard.
- Tanya-jawab produk, rekomendasi, dan pelacakan pesanan masih manual — belum ada asisten otomatis berbasis data toko (bukan jawaban karangan/halusinasi AI).

### Tujuan Utama
1. Pengalaman belanja jajanan pasar yang cepat, aman, dan responsif (mobile-first).
2. Otomatisasi checkout, pembayaran (Pakasir/QRIS), dan pengiriman (Biteship).
3. Chatbot AI yang membantu mencari produk, memesan, mengecek status pesanan — dengan grounding ke katalog toko.
4. Kontrol penuh admin atas produk, kategori, kupon, pesanan, ulasan, pengguna, dan konten halaman.

---

## 2. Target Pengguna

| Peran | Kebutuhan Utama |
|---|---|
| **Pelanggan (Customer)** | Belanja cepat, pembayaran fleksibel (QRIS), kejelasan status & pelacakan pengiriman, bantuan chatbot. |
| **Admin / Pemilik Toko** | Kelola katalog, proses pesanan & pengiriman, kupon promo, moderasi ulasan, laporan penjualan, dan konten halaman publik. |

---

## 3. Tech Stack

| Layer | Teknologi |
|---|---|
| Backend Framework | Laravel 13 (PHP ^8.3) |
| Interaktivitas Frontend | Livewire 4 (server-driven UI, tanpa SPA terpisah) + Alpine.js |
| Build Tool / CSS | Vite 8, Tailwind CSS 4 (dukungan dark/light mode) |
| Autentikasi | Laravel Auth bawaan + Laravel Socialite (Google Sign-In) |
| AI / Chatbot | Model Gemini (`gemini-3-flash-preview`) via endpoint kompatibel OpenAI, dibungkus `GeminiService` (chat multi-turn + analisis gambar) |
| Logistik | Biteship API (`BiteshipService`) — tarif, booking kurir, tracking, webhook status |
| Pembayaran | **Pakasir** (`PakasirService`) — hosted payment page kanal **QRIS**; konfirmasi via webhook + verifikasi ulang ke API |
| Database | MySQL/MariaDB (dump referensi: `gegares_v2.sql`) |
| Testing | PHPUnit (35 test, feature + unit), Mockery, Faker |
| Keamanan | Middleware `IsAdmin`, `CheckProfileCompletion`, `SecurityHeaders` (CSP ketat); trait `ValidatesRecaptcha`; honeypot; HMAC session integrity; rate limiting; IP banning via Cache |

> Catatan: integrasi **Midtrans telah dihapus** — jalur pembayaran aktif hanya Pakasir (QRIS). Tidak ada tabel DB khusus untuk log keamanan; banning IP & rate limit memakai **Cache**, event mencurigakan dicatat ke **Log**.

---

## 4. Fitur Utama (Terverifikasi di Codebase)

### A. Autentikasi & Profil
- Login/register email-password dengan **rate limit 5 req/menit** (`throttle:5,1`).
- **Login Google** via Socialite (`GoogleController`).
- Login admin terpisah (`AdminLoginController`) + middleware `is_admin`.
- Lupa & reset password (`ForgotPasswordController`).
- Wajib melengkapi nomor telepon sebelum transaksi (middleware `check_phone` / `CheckProfileCompletion`).
- Pengaturan profil: data diri, notifikasi, ganti password, hapus avatar (`ProfileController`).

### B. Katalog Produk & Kategori
- Listing & detail produk (`ProductController`), slug sebagai route key.
- Kategori aktif/nonaktif (`Category.is_active`).
- Varian produk (`ProductVariant`) & galeri multi-gambar terurut (`ProductImage.sort_order`) dengan slideshow modal.
- Produk unggulan (`scopeFeatured`), status stok (`isOutOfStock`, `isLowStock` — ambang <5).
- Rating & jumlah ulasan dari review yang **disetujui admin**.
- **Kartu produk** desain baru (`components.product-card-grid`) — konsisten di halaman Produk & "Produk Unggulan" homepage: gambar rasio 1:1, badge ★ Favorit, harga `/porsi`, CTA "Tambah ke Keranjang", responsif mobile (2 kolom).

### C. Keranjang & Wishlist (Livewire real-time)
- `CartDrawer`, `CartIcon`, `CartService` — keranjang interaktif tanpa reload.
- `ToggleWishlist`, `WishlistDrawer`, `WishlistIcon` — wishlist per user.

### D. Checkout, Pengiriman & Pembayaran
- Alur checkout (`CheckoutController`) — validasi stok, pemilihan alamat (`Address` dengan lat/long & `biteship_location_id`), pembuatan order **dalam DB transaction** (atomic).
- Pemilihan kurir & tarif real-time via Biteship (`SelectShipping`).
- **Auto-booking Biteship** otomatis saat status order menjadi `paid` (event `booted()` di model `Order`), lengkap dengan retry limit & logging.
- Pelacakan real-time via `courier_tracking_id`/`tracking_number` (`getTrackingUrlAttribute`, dukungan mode sandbox Biteship).
- Pembayaran via **Pakasir** (kanal QRIS): `createPaymentUrl` menghasilkan hosted payment link; pelunasan dikonfirmasi lewat webhook.
- **Keamanan pembayaran (hardened):** webhook Pakasir tidak mempercayai payload — **mengkonfirmasi ulang transaksi ke API Pakasir** sebelum menandai lunas; pelunasan + pengurangan stok dibungkus **DB transaction + row lock** (idempoten, anti oversell). Webhook Biteship diautentikasi dengan **shared-secret token**.
- Kupon promo (`Coupon`) — tipe fixed/percent, batas pemakaian, tanggal berlaku, `isValid()`.

### E. Pesanan & Status
- Riwayat & detail pesanan (`OrderController`) — index, show, tracking, payment, cek status, konfirmasi diterima ("complete").
- Status: `pending`, `awaiting_payment`, `paid`, `processing`, `shipped`, `completed`, `cancelled` — dengan label & warna siap pakai.
- **Soft delete** pada order (riwayat tidak hilang permanen).

### F. Ulasan Produk
- `SubmitReview` — rating 1–5 + komentar + foto ulasan.
- **Moderasi admin** (`is_approved`) sebelum tampil publik & memengaruhi rating produk.

### G. AI Chatbot (Gemini)
- `Chatbot` Livewire + `GeminiService` (multi-turn, temperature terkontrol, safety settings).
- **Grounding data toko**: prompt sistem membangun konteks dari `Product` (whitelist anti-halusinasi), `Order`, `OrderItem`, kupon aktif, info toko, **isi keranjang**, dan **konteks waktu** (sapaan & rekomendasi sesuai jam, status buka/tutup).
- **Aksi cerdas**: rekomendasi kontekstual (budget/acara/selera), pemesanan langsung dari chat (tag `---BUY---`, mendukung **banyak produk sekaligus**), checkout langsung via chatbot, notifikasi status pembayaran.
- Memori percakapan hingga **12 giliran**.
- **Snap & Buy**: upload foto jajanan → identifikasi via `analyzeImage` (Gemini Vision) & dicocokkan ke katalog.
- **Keamanan chatbot**: honeypot anti-bot, deteksi IP diblokir (`checkBanStatus`), integritas riwayat via **HMAC session hash** (anti-tamper), rate limit 15/menit, sanitasi XSS & masking PII (`SecurityService`), auto-ban IP setelah pelanggaran berulang (via Cache).

### H. Pencarian Global (typo-tolerant)
- `GlobalSearch` Livewire — pencarian produk & kategori lintas halaman.
- **Toleransi typo**: bila hasil exact minim, fallback **fuzzy (Levenshtein)** dengan toleransi adaptif per panjang kata + pencocokan per-kata/prefix; menampilkan petunjuk "hasil terdekat".

### I. Notifikasi
- `NotificationDropdown` + kolom `notification_settings` (JSON) di tabel users.

### J. Panel Admin
- **Dashboard** (`DashboardController`) — statistik & ringkasan.
- **Kategori** — CRUD + toggle status aktif.
- **Produk** — CRUD + toggle featured.
- **Pesanan** — proses pengiriman manual ke Biteship, lihat tracking, **export CSV**, cetak **laporan**, filter periode (flatpickr range picker, default 1 bulan terakhir).
- **Ulasan** — approve/reject, hapus, filter periode konsisten dengan halaman pesanan.
- **Pengguna** (`AdminUserController`).
- **Kupon/Promo** (`CouponController`).
- **Pengaturan Toko** (`ManageStoreAddress`) & **Konten** (`ManageStoreContent`) — konten dinamis: hero (badge/judul/subtitle), CTA, FAQ, halaman About (judul, kisah, visi, misi, **galeri + heading galeri**), kontak WhatsApp & jam operasional — semua tersimpan di `store_settings`.

### K. Keamanan Sistem
- `SecurityHeaders` — header keamanan (X-Frame-Options, nosniff, Referrer-Policy, Permissions-Policy) + **CSP ketat** + anti-cache untuk halaman dinamis.
- Trait `ValidatesRecaptcha`.
- Rate limiting pada auth & chatbot; IP banning & penghitungan pelanggaran via **Cache**; event keamanan dicatat via **Log**.

### L. UI/UX
- **Homepage**: hero minimalis dengan **carousel** (tombol prev/next muncul saat hover di desktop, **swipe** di mobile, dots, autoplay), background **selang-seling adaptif** dark/light, kategori, produk unggulan, FAQ, CTA.
- **Halaman About**: galeri "Proses Produksi" berbentuk **carousel** dengan **fallback ikon** bila gambar rusak/tidak ada; heading galeri dapat diedit dari pengaturan konten.
- **Drawer filter** produk di mobile dengan **micro-animation** slide + swipe.
- Dukungan **dark/light mode** menyeluruh.

---

## 5. Skema Data Utama

| Tabel | Fungsi |
|---|---|
| `users` | Akun pelanggan & admin (role `admin`/`user`), Google OAuth, `notification_settings`, soft delete |
| `categories` | Kategori produk, status aktif |
| `products` | Produk: harga, stok, gambar utama, featured, rating |
| `product_variants` | Varian produk (harga, stok per varian) |
| `product_images` | Galeri gambar produk (multi-image, terurut) |
| `addresses` | Alamat pengiriman user + koordinat & `biteship_location_id` (soft delete) |
| `coupons` | Kupon promo (fixed/percent, kuota, masa berlaku) |
| `orders` | Pesanan: status, pembayaran (Pakasir), pengiriman (Biteship), total, soft delete |
| `order_items` | Rincian item (snapshot nama & harga saat transaksi) |
| `reviews` | Ulasan (rating, komentar, foto, approval), terhubung ke order |
| `wishlists` | Produk favorit user |
| `store_settings` | Konfigurasi toko: profil, lokasi, konten hero/about/FAQ/CTA/galeri/kontak |
| `sessions`, `cache`, `jobs`, `job_batches`, `failed_jobs` | Infrastruktur bawaan Laravel |

> Rate limit, IP banning, dan penghitungan pelanggaran keamanan menggunakan **cache store**, bukan tabel khusus.

---

## 6. Arsitektur Integrasi Pihak Ketiga

```
┌──────────────────────────────┐
│   Gegares Web (Laravel +     │
│   Livewire + Alpine + Tailwind)│
└───────────────┬──────────────┘
                │
   ┌────────────┼──────────────┬───────────────────┐
   ▼            ▼              ▼                    ▼
Biteship      Pakasir      Google OAuth        Gemini AI
(tarif,       (QRIS,       (Socialite)         (chat multi-turn
booking,      hosted pay,                      + Vision, via
tracking,     webhook +                        endpoint kompatibel
webhook)      verifikasi API)                  OpenAI)
```

- API key pihak ketiga di `.env`, diakses lewat `config/services.php`, `config/biteship.php`, `config/pakasir.php`.
- **Webhook masuk**: `/webhook/pakasir` (verifikasi ulang ke API sebelum melunasi) & `/webhook/biteship` (autentikasi shared-secret token) — keduanya dikecualikan dari CSRF.
- Variabel lingkungan integrasi: `PAKASIR_PROJECT_SLUG`, `PAKASIR_API_KEY`, `BITESHIP_API_KEY`, `BITESHIP_ORIGIN_AREA_ID`, `BITESHIP_WEBHOOK_TOKEN`, `AI_API_KEY`/`AI_BASE_URL`/`AI_MODEL`, `GOOGLE_CLIENT_ID`/`SECRET`/`REDIRECT_URL`.

---

## 7. Non-Functional Requirements

- **Keamanan**: verifikasi pembayaran otoritatif (tidak percaya payload webhook), transaksi stok atomik + lock (idempoten), CSP & security headers, rate limiting, sanitasi input chatbot.
- **Kinerja**: aset di-build via Vite; gambar `loading="lazy"`; disarankan menambah index DB pada kolom yang sering difilter (`orders.status`, `created_at`, dll) untuk skala data besar.
- **Aksesibilitas & Responsif**: mobile-first, dark/light mode, `aria-label` pada kontrol interaktif.
- **Keandalan**: soft delete pada order & alamat; auto-retry & fallback pada integrasi Biteship/Pakasir.

---

## 8. Scope Saat Ini vs Rencana ke Depan

### Sudah Terimplementasi
- E-commerce web end-to-end: katalog → keranjang → checkout → pembayaran (QRIS) → pengiriman → ulasan.
- Panel admin lengkap (produk, kategori, pesanan, ulasan, pengguna, kupon, konten).
- Chatbot AI berbasis data toko (grounding, aksi pesan/checkout, Snap & Buy).
- Pencarian toleran typo (fuzzy).
- Lapisan keamanan: verifikasi webhook, transaksi stok terkunci, honeypot, IP banning, session integrity, security headers, reCAPTCHA.
- Auto-booking pengiriman Biteship saat lunas.
- UI: hero carousel, background adaptif, kartu produk baru, animasi drawer, galeri About carousel + fallback.

### Rencana / Pekerjaan Lanjutan
- **Kelengkapan konfigurasi**: dokumentasikan seluruh env integrasi di `.env.example` (AI, Biteship, webhook token).
- **Optimasi DB**: tambah index pada kolom filter utama (orders/reviews).
- **Skalabilitas pencarian**: pertimbangkan MySQL `FULLTEXT`/`MATCH…AGAINST` jika katalog membesar (fuzzy PHP saat ini optimal untuk katalog kecil–menengah).
- **Roadmap Fase 2 (opsional, di luar web)**: aplikasi Android native (Snap & Buy kamera, review summarizer AI) sebagaimana draft PRD lama — dipisah sebagai pengembangan lanjutan pasca-sidang.

### Rekomendasi Fokus Berikutnya
1. Jalankan `php artisan migrate:fresh --seed` pada database baru untuk memastikan skema (termasuk kolom konten galeri terbaru) & data seeder (~47 produk) konsisten.
2. Perluas test coverage alur pembayaran & auto-booking Biteship.
3. Dokumentasikan alur AI Chatbot & keamanannya (grounding, honeypot, HMAC session) untuk bab metodologi/keamanan skripsi.

---

## 9. Referensi
- Skema database: `gegares_v2.sql`
- Konfigurasi integrasi: `config/services.php`, `config/biteship.php`, `config/pakasir.php`
- Service inti: `PakasirService`, `BiteshipService`, `GeminiService`, `CartService`, `SecurityService`
- Test: `tests/Feature/*` (PakasirPayment, BiteshipWebhook, Chatbot, ForgotPassword, ManageAddresses, AdminUserManagement, OrderTrackingUrl)
