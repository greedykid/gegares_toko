# Gegares

Toko daring jajanan pasar tradisional: katalog produk, keranjang, checkout dengan
ongkir dan pembayaran daring, pelacakan pengiriman, panel admin, dan chatbot AI.

Dibangun dengan **Laravel 13**, **Livewire 4**, **Tailwind CSS 4**, dan **MySQL**.

---

## Daftar isi

- [Kebutuhan sistem](#kebutuhan-sistem)
- [Pemasangan untuk pengembangan](#pemasangan-untuk-pengembangan)
- [Konfigurasi `.env`](#konfigurasi-env)
- [Layanan pihak ketiga](#layanan-pihak-ketiga)
- [Menjalankan aplikasi](#menjalankan-aplikasi)
- [Dua proses latar yang wajib hidup](#dua-proses-latar-yang-wajib-hidup)
- [Pengujian](#pengujian)
- [Deploy ke VPS](#deploy-ke-vps)
- [Deploy ke cPanel](#deploy-ke-cpanel)
- [Perintah terjadwal](#perintah-terjadwal)
- [Mode demo kurir](#mode-demo-kurir)
- [Pemecahan masalah](#pemecahan-masalah)

---

## Kebutuhan sistem

| Kebutuhan | Versi |
| --- | --- |
| PHP | 8.3+ (ekstensi `gd` diperlukan untuk optimasi gambar) |
| Composer | 2.x |
| Node.js | 20+ |
| MySQL | 8.0+ (atau MariaDB 10.6+) |

---

## Pemasangan untuk pengembangan

```bash
git clone <url-repositori> gegares
cd gegares

composer setup
```

`composer setup` menjalankan seluruh langkah awal sekaligus: `composer install`,
menyalin `.env.example` menjadi `.env`, membuat `APP_KEY`, menjalankan migrasi,
`npm install`, dan `npm run build`.

Setelah itu isi kredensial di `.env` (lihat bagian berikutnya), lalu:

```bash
php artisan migrate --seed
php artisan storage:link
```

`--seed` mengisi akun admin, pengaturan toko, produk contoh, dan data pelanggan
demo.

**Akun admin bawaan** — `admin@gegares.shop` / `password123`.
Ganti kata sandinya sebelum dipakai di mana pun yang bisa diakses publik.

---

## Konfigurasi `.env`

Salin dari `.env.example`. Yang wajib diisi sebelum aplikasi berfungsi penuh:

```dotenv
APP_NAME=Gegares
APP_ENV=local                 # production saat sudah tayang
APP_DEBUG=true                # WAJIB false di produksi
APP_URL=http://localhost:8000
APP_LOCALE=id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gegares
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database     # butuh worker, lihat "Dua proses latar"
CACHE_STORE=database
SESSION_DRIVER=database
```

> **`APP_DEBUG=false` di produksi bukan sekadar anjuran.** Dengan `true`, setiap
> galat tak tertangani menampilkan halaman Laravel lengkap dengan jejak tumpukan
> **dan seluruh isi `.env`** — termasuk kredensial basis data serta kunci API
> Pakasir, Biteship, dan AI.

---

## Layanan pihak ketiga

Semuanya opsional saat pengembangan; aplikasi tetap berjalan tanpa kredensial,
hanya fiturnya tidak aktif.

### Pakasir — pembayaran

```dotenv
PAKASIR_PROJECT_SLUG=gegares
PAKASIR_API_KEY=
PAKASIR_TIMEZONE=UTC
```

Daftarkan URL callback di dasbor Pakasir: `https://domain-anda/webhook/pakasir`

### Biteship — ongkir dan pelacakan

```dotenv
BITESHIP_API_KEY=
BITESHIP_ORIGIN_AREA_ID=
BITESHIP_WEBHOOK_TOKEN=       # rahasia bersama, sangat dianjurkan
```

URL callback: `https://domain-anda/webhook/biteship`

Tanpa `BITESHIP_WEBHOOK_TOKEN`, siapa pun yang tahu URL tersebut bisa memalsukan
perubahan status pengiriman.

### Chatbot AI

```dotenv
AI_API_KEY=
AI_BASE_URL=https://lite.koboillm.com/v1
AI_MODEL=gemini-3-flash-preview
```

### Login Google

```dotenv
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URL=http://127.0.0.1:8000/auth/google/callback
```

---

## Menjalankan aplikasi

```bash
composer dev
```

Menyalakan server PHP, pemroses antrean, dan Vite sekaligus di satu terminal.

Atau jalankan terpisah:

```bash
php artisan serve
php artisan queue:listen --tries=1
npm run dev
```

> **Tailwind 4 membaca kelas dari berkas Blade saat proses build.** Kelas utilitas
> yang baru ditambahkan ke Blade tidak akan muncul sampai `npm run dev` berjalan
> atau `npm run build` dijalankan ulang — `php artisan view:clear` saja tidak cukup.

---

## Dua proses latar yang wajib hidup

Tanpa keduanya aplikasi tampak normal tetapi diam-diam berhenti bekerja.

**1. Pemroses antrean.** Webhook Pakasir sengaja menyerahkan verifikasi pembayaran
ke antrean supaya gateway langsung menerima 200. Tanpa worker, job menumpuk dan
**pembayaran tidak pernah dikonfirmasi** — pelanggan sudah membayar, status
pesanan tetap "menunggu pembayaran". Hal yang sama berlaku untuk pemesanan kurir
dan notifikasi.

**2. Penjadwal.** Menjalankan pembatalan otomatis, penyelesaian otomatis,
sinkronisasi Biteship, dan rekonsiliasi pembayaran (jaring pengaman untuk webhook
yang hilang).

Cara memasangnya ada di bagian deploy di bawah.

---

## Pengujian

```bash
composer test          # atau: php artisan test
```

Berjalan di atas SQLite in-memory, tidak menyentuh basis data pengembangan.

`phpunit.xml` mengunci beberapa variabel lingkungan (`DEMO_COURIER`,
`BITESHIP_WEBHOOK_TOKEN`, `APP_CONFIG_CACHE`) supaya hasil pengujian tidak berubah
mengikuti isi `.env` di mesin yang menjalankannya.

---

## Deploy ke VPS

Punya systemd, jadi worker bisa jadi layanan yang dipantau.

### 1. Berkas dan build

```bash
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

### 2. Worker sebagai layanan

`/etc/systemd/system/gegares-queue.service`:

```ini
[Unit]
Description=Gegares Laravel queue worker
After=network.target mysql.service
Requires=mysql.service

[Service]
User=www-data
Group=www-data
Restart=always
RestartSec=5
WorkingDirectory=/var/www/gegares
ExecStart=/usr/bin/php /var/www/gegares/artisan queue:work --sleep=3 --tries=3 --max-time=3600 --backoff=5
MemoryMax=256M

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now gegares-queue
```

### 3. Penjadwal

`/etc/cron.d/gegares-scheduler`:

```cron
* * * * * www-data cd /var/www/gegares && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

### 4. Setelah setiap deploy

```bash
sudo systemctl restart gegares-queue
```

> **Jangan lewatkan langkah ini.** `queue:work` memuat kode sekali lalu menyimpannya
> di memori — worker akan terus menjalankan versi lama sampai di-restart, betapa pun
> seringnya Anda `git pull`.

---

## Deploy ke cPanel

cPanel **tidak punya systemd**, jadi worker tidak bisa dijadikan layanan. Perannya
digantikan cron.

### 1. Unggah dan siapkan

Lewat Terminal cPanel (atau SSH):

```bash
cd ~/gegares
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
php artisan config:cache && php artisan view:clear
```

Aset hasil build (`public/build/`) ikut dalam repositori, jadi Node.js tidak perlu
ada di server. Jalankan `npm run build` di mesin lokal lalu commit hasilnya.

**Periksa versi PHP CLI-nya lebih dulu** — di cPanel sering berbeda dari yang
dipakai web:

```bash
which php && php -v
# umumnya /opt/cpanel/ea-php84/root/usr/bin/php
```

Pakai path itu secara penuh di kedua cron di bawah.

### 2. Dua Cron Job (cPanel → Cron Jobs), keduanya **tiap menit**

```bash
cd ~/gegares && /usr/local/bin/php artisan schedule:run >/dev/null 2>&1
```

```bash
cd ~/gegares && flock -n /tmp/gg-queue.lock /usr/local/bin/php artisan queue:work --stop-when-empty --tries=3 --max-time=55 >/dev/null 2>&1
```

`--stop-when-empty` membuat proses berhenti sendiri setelah antrean habis, dan
`flock` mencegah dua proses berjalan bersamaan saat ada job yang lambat.

### 3. Yang mudah terlupa saat pindah domain

- `APP_URL` di `.env` harus menunjuk domain baru.
- **URL webhook Pakasir dan Biteship harus diperbarui di dasbor masing-masing.**
  Selama masih menunjuk domain lama, pembayaran di domain baru tidak akan pernah
  terkonfirmasi.
- `APP_DEBUG=false`.
- Jalankan ulang `php artisan config:cache` setiap kali `.env` berubah — tanpa itu
  perubahan tidak terbaca.

---

## Perintah terjadwal

Dijalankan oleh penjadwal; bisa juga dipanggil manual.

| Perintah | Jadwal | Kegunaan |
| --- | --- | --- |
| `orders:reconcile-payments` | tiap 5 menit | Menanyakan ulang pesanan yang masih belum lunas ke Pakasir, supaya pembayaran yang webhook-nya hilang tidak ikut dibatalkan otomatis |
| `biteship:sync` | tiap 10 menit | Menarik status pengiriman untuk pesanan yang sedang berjalan |
| `orders:auto-cancel --hours=24` | tiap jam | Membatalkan pesanan yang tidak dibayar dan mengembalikan stoknya |
| `orders:auto-complete --hours=24` | tiap jam | Menyelesaikan pesanan yang sudah sampai dan tidak dikonfirmasi pelanggan |
| `images:optimize` | manual | Memperkecil dan memampatkan gambar yang terlalu besar di disk publik |

---

## Mode demo kurir

Panel "Informasi Kurir" hanya terisi jika Biteship benar-benar mengirim data
kurir. Untuk keperluan demo, `DEMO_COURIER=true` mengisinya dengan kurir pengganti.

```dotenv
DEMO_COURIER=false            # bawaan
```

Fotonya diambil dari `public/images/demo-courier.jpg` (`.png`, `.jpeg`, dan `.webp`
juga diterima). Bila berkasnya tidak ada, panel menampilkan avatar inisial.

> **Biarkan mati di mana pun pelanggan sungguhan bisa melihatnya.** Mode ini
> menampilkan pengiriman yang tidak sedang berlangsung, dan selama menyala panel
> admin tidak lagi bisa membedakan pemesanan kurir yang macet dari yang sehat.

---

## Pemecahan masalah

**Pembayaran sudah lunas di Pakasir tetapi status pesanan tidak berubah.**
Hampir selalu karena pemroses antrean mati. Periksa `systemctl status gegares-queue`
(VPS) atau riwayat cron (cPanel), lalu lihat apakah tabel `jobs` menumpuk. Sebagai
langkah darurat: `php artisan orders:reconcile-payments`.

**Perubahan kode tidak berpengaruh pada job latar.**
Restart worker — kode lama masih tersimpan di memorinya.

**Kelas Tailwind baru tidak muncul.**
Jalankan `npm run build`; `view:clear` saja tidak cukup.

**Perubahan `.env` tidak terbaca.**
Jalankan `php artisan config:cache`. Konfigurasi yang sudah di-cache mengalahkan
`.env`, dan ini juga berlaku saat pengujian — karena itulah `phpunit.xml`
mengarahkan `APP_CONFIG_CACHE` ke berkas yang tidak pernah ada.

**Berkas tidak bisa diunggah ke `public/` di VPS.**
Direktori tersebut milik `www-data`. Unggah ke direktori home dulu, lalu:

```bash
sudo install -o www-data -g www-data -m 644 ~/berkas.jpg /var/www/gegares/public/images/berkas.jpg
```

---

## Lisensi

Dikembangkan sebagai proyek skripsi. Kerangka Laravel berlisensi
[MIT](https://opensource.org/licenses/MIT).
