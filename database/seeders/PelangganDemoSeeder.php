<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Dua puluh lima pelanggan beserta riwayat belanjanya.
 *
 * Isinya sengaja tidak seragam. Tidak semua pesanan berakhir selesai, tidak
 * semua yang selesai diulas, dan yang mengulas tidak semuanya memberi bintang
 * lima — toko yang setiap produknya 5,0 justru terbaca palsu.
 *
 * Ulasan hanya menempel pada pesanan berstatus `completed`, mengikuti aturan
 * yang sama dengan komponen SubmitReview: seseorang hanya boleh berkomentar
 * tentang barang yang benar-benar sudah ia terima.
 */
class PelangganDemoSeeder extends Seeder
{
    /** Berapa banyak pembeli yang benar-benar repot menulis ulasan. */
    private const PELUANG_MENGULAS = 65; // persen

    public function run(): void
    {
        $produk = Product::all();

        if ($produk->isEmpty()) {
            $this->command->warn('Katalog masih kosong — jalankan ProductSeeder dulu.');

            return;
        }

        foreach ($this->pelanggan() as $data) {
            $user = $this->simpanPelanggan($data);
            $alamat = $this->simpanAlamat($user, $data);

            foreach ($this->rencanaPesanan() as $rencana) {
                $this->buatPesanan($user, $alamat, $produk, $rencana);
            }
        }

        // Bintang di kartu produk diturunkan dari ulasan yang baru masuk,
        // bukan diisi sendiri.
        Product::query()->each(fn (Product $p) => $p->updateRating());

        $this->command->info('25 pelanggan beserta pesanan dan ulasannya siap.');
    }

    private function simpanPelanggan(array $d): User
    {
        return User::firstOrCreate(
            ['email' => $d['email']],
            [
                'name' => $d['nama'],
                'password' => Hash::make('pelanggan123'),
                'role' => 'user',
                'phone' => $d['telepon'],
                'email_verified_at' => now(),
            ],
        );
    }

    private function simpanAlamat(User $user, array $d): Address
    {
        return Address::firstOrCreate(
            ['user_id' => $user->id, 'is_primary' => true],
            [
                'label' => $d['label'],
                'recipient_name' => $d['nama'],
                'phone' => $d['telepon'],
                'address_line' => $d['alamat'],
                'city' => $d['kota'],
                'province' => $d['provinsi'],
                'postal_code' => $d['pos'],
            ],
        );
    }

    /**
     * Berapa pesanan yang dimiliki seorang pelanggan, dan bagaimana akhirnya.
     *
     * @return list<array{status: string, umur: int}>
     */
    private function rencanaPesanan(): array
    {
        $jumlah = random_int(1, 3);
        $rencana = [];

        for ($i = 0; $i < $jumlah; $i++) {
            // Sebagian besar pesanan berakhir selesai — itu yang wajar untuk
            // toko yang sudah jalan. Sisanya menggambarkan keadaan yang masih
            // berjalan atau batal, supaya dasbor admin tidak terlihat mustahil.
            $status = match (random_int(1, 10)) {
                1, 2, 3, 4, 5, 6 => 'completed',
                7 => 'shipped',
                8 => 'processing',
                9 => 'pending',
                default => 'cancelled',
            };

            // Pesanan yang masih menunggu bayar harus muda. `orders:auto-cancel`
            // membatalkan yang lewat 24 jam, jadi pending berumur berbulan-bulan
            // adalah keadaan yang tidak mungkin ada di sistem ini.
            $umur = $status === 'pending' ? random_int(0, 20) : random_int(2, 150);

            $rencana[] = ['status' => $status, 'umur' => $umur];
        }

        return $rencana;
    }

    private function buatPesanan(User $user, Address $alamat, $produk, array $rencana): void
    {
        $status = $rencana['status'];
        $dibuat = $status === 'pending'
            ? Carbon::now()->subHours($rencana['umur'])
            : Carbon::now()->subDays($rencana['umur'])->setTime(random_int(7, 20), random_int(0, 59));

        $dipilih = $produk->random(random_int(1, 4));
        $baris = [];
        $subtotal = 0;

        foreach ($dipilih as $p) {
            // Barang murah dibeli banyak, barang mahal satu dua — orang tidak
            // memesan tiga toples nastar dan satu biji klepon dalam jumlah sama.
            $qty = $p->price <= 3500 ? random_int(3, 12) : random_int(1, 3);
            $harga = (int) $p->price;

            $baris[] = [
                'produk' => $p,
                'qty' => $qty,
                'harga' => $harga,
                'subtotal' => $harga * $qty,
            ];

            $subtotal += $harga * $qty;
        }

        $ongkir = [8000, 10000, 12000, 15000, 18000][random_int(0, 4)];
        $dibayar = in_array($status, ['completed', 'shipped', 'processing'], true);

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'GGR-'.$dibuat->format('ymd').'-'.strtoupper(Str::random(5)),
            'address_id' => $alamat->id,
            'subtotal' => $subtotal,
            'shipping_cost' => $ongkir,
            'discount_amount' => 0,
            'total' => $subtotal + $ongkir,
            'status' => $status,
            // Hanya tiga nilai ini yang pernah ditulis aplikasi (lihat
            // OrderService dan PakasirService). Memakai nilai di luar itu —
            // 'pending', misalnya — membuat pesanan tak tersentuh
            // `orders:auto-cancel`, yang hanya memburu yang `unpaid`, sehingga
            // ia menggantung selamanya.
            'payment_status' => $dibayar ? 'paid' : ($status === 'cancelled' ? 'expired' : 'unpaid'),
            'payment_method' => 'qris',
            'shipping_courier' => 'jne',
            'shipping_service' => 'reg',
            // Pesanan yang sudah berangkat selalu punya resi; tanpa ini halaman
            // lacak milik pembeli menampilkan "belum ada informasi".
            'tracking_number' => in_array($status, ['shipped', 'completed'], true)
                ? 'JP'.strtoupper(Str::random(10))
                : null,
            'notes' => $this->catatan(),
            'paid_at' => $dibayar ? $dibuat->copy()->addMinutes(random_int(3, 90)) : null,
            'delivered_at' => $status === 'completed' ? $dibuat->copy()->addDays(random_int(1, 3)) : null,
            'created_at' => $dibuat,
            'updated_at' => $dibuat,
        ]);

        foreach ($baris as $b) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $b['produk']->id,
                'product_name' => $b['produk']->name,
                'product_price' => $b['harga'],
                'quantity' => $b['qty'],
                'subtotal' => $b['subtotal'],
                'created_at' => $dibuat,
                'updated_at' => $dibuat,
            ]);

            // Hanya pesanan yang sudah sampai yang boleh diulas, dan tidak
            // setiap pembeli menyempatkan diri menulis.
            if ($status === 'completed' && random_int(1, 100) <= self::PELUANG_MENGULAS) {
                $this->buatUlasan($user, $order, $b['produk'], $dibuat);
            }
        }
    }

    private function buatUlasan(User $user, Order $order, Product $produk, Carbon $dibuat): void
    {
        $bintang = $this->bintang();
        $daftar = $this->komentar()[$bintang];

        Review::firstOrCreate(
            ['order_id' => $order->id, 'product_id' => $produk->id, 'user_id' => $user->id],
            [
                'rating' => $bintang,
                'comment' => $daftar[array_rand($daftar)],
                'is_approved' => true,
                // Orang menulis ulasan beberapa hari setelah barangnya sampai,
                // bukan pada detik yang sama.
                'created_at' => $dibuat->copy()->addDays(random_int(2, 9)),
                'updated_at' => $dibuat->copy()->addDays(random_int(2, 9)),
            ],
        );
    }

    /** Sebaran bintang yang condong ke atas, tetapi tidak sempurna. */
    private function bintang(): int
    {
        return match (random_int(1, 20)) {
            1 => 3,
            2, 3, 4, 5, 6 => 4,
            default => 5,
        };
    }

    /** @return array<int, list<string>> */
    private function komentar(): array
    {
        return [
            5 => [
                'Baru dibuka udah wangi. Anak-anak langsung ngabisin, besok pesen lagi.',
                'Sampai masih anget dan utuh, nggak ada yang penyok sama sekali. Rasanya juga pas.',
                'Ini yang saya cari, rasanya kayak buatan almarhumah ibu saya. Terima kasih ya.',
                'Dipesan buat arisan, komplek pada nanya beli di mana. Recommended.',
                'Manisnya nggak bikin eneg, jadi bisa makan lebih dari satu. Mantap.',
                'Packingnya rapi, dikasih bubble wrap juga. Kelihatan telaten ngemasnya.',
                'Pesan pagi, siang udah sampai. Cepat banget dan rasanya nggak mengecewakan.',
                'Enak banget. Sudah tiga kali pesan di sini dan rasanya konsisten.',
                'Buat suguhan pengajian, alhamdulillah pada suka semua. Habis tak bersisa.',
                'Teksturnya lembut, kelapanya masih segar. Beda sama yang dijual di pasar.',
            ],
            4 => [
                'Rasanya enak, cuma pas sampai udah agak dingin. Dihangatin bentar udah oke lagi.',
                'Enak. Kalau boleh saran isiannya ditambah dikit lagi biar makin mantap.',
                'Sesuai gambar dan rasanya oke. Pengiriman agak lama tapi masih wajar.',
                'Buat harga segini udah bagus banget. Cuma saya kurang suka yang terlalu manis.',
                'Puas sih, cuma kemarin ada satu yang agak gosong pinggirnya. Selebihnya enak.',
                'Anak saya suka. Saya pribadi lebih suka yang agak gurih, tapi ini tetap enak.',
                'Overall bagus, pasti pesan lagi. Semoga next time ada varian lain.',
            ],
            3 => [
                'Rasanya biasa aja menurut saya, tapi masih bisa dimakan. Mungkin selera.',
                'Datangnya agak telat jadi teksturnya udah berubah. Rasa aslinya kayaknya enak.',
                'Porsinya lebih kecil dari yang saya bayangin. Rasanya sendiri lumayan.',
            ],
        ];
    }

    /** Catatan pembeli — sebagian besar pesanan tidak punya catatan apa pun. */
    private function catatan(): ?string
    {
        $pilihan = [
            null, null, null, null, null,
            'Tolong jangan terlalu manis ya bu',
            'Titip di pos satpam kalau rumah kosong',
            'Buat acara jam 10 pagi, mohon jangan telat',
            'Sendoknya tolong dilebihin, terima kasih',
            'Sambalnya dipisah ya',
            'Rumah pagar hijau, sebelah warung',
        ];

        return $pilihan[array_rand($pilihan)];
    }

    /**
     * Dua puluh lima pembeli.
     *
     * @return list<array<string, string>>
     */
    private function pelanggan(): array
    {
        return [
            ['nama' => 'Siti Nurhaliza', 'email' => 'siti.nurhaliza@gmail.com', 'telepon' => '081234501201', 'label' => 'Rumah', 'alamat' => 'Jl. Melati Raya No. 12, RT 03/RW 05, Cilandak', 'kota' => 'Jakarta Selatan', 'provinsi' => 'DKI Jakarta', 'pos' => '12430'],
            ['nama' => 'Budi Setiawan', 'email' => 'budi.setiawan88@gmail.com', 'telepon' => '081234501202', 'label' => 'Rumah', 'alamat' => 'Perum Griya Asri Blok C2 No. 8, Depok Sawangan', 'kota' => 'Depok', 'provinsi' => 'Jawa Barat', 'pos' => '16511'],
            ['nama' => 'Dewi Lestari', 'email' => 'dewi.lestari@yahoo.com', 'telepon' => '081234501203', 'label' => 'Kantor', 'alamat' => 'Gedung Menara Sudirman Lt. 7, Jl. Jend. Sudirman Kav. 60', 'kota' => 'Jakarta Selatan', 'provinsi' => 'DKI Jakarta', 'pos' => '12190'],
            ['nama' => 'Agus Priyanto', 'email' => 'agus.priyanto@gmail.com', 'telepon' => '081234501204', 'label' => 'Rumah', 'alamat' => 'Jl. Kaliurang KM 7, Gang Mawar No. 3, Ngaglik', 'kota' => 'Sleman', 'provinsi' => 'DI Yogyakarta', 'pos' => '55581'],
            ['nama' => 'Rina Wulandari', 'email' => 'rina.wulandari@gmail.com', 'telepon' => '081234501205', 'label' => 'Rumah', 'alamat' => 'Jl. Cihampelas No. 145, Coblong', 'kota' => 'Bandung', 'provinsi' => 'Jawa Barat', 'pos' => '40131'],
            ['nama' => 'Hendra Gunawan', 'email' => 'hendra.gunawan@gmail.com', 'telepon' => '081234501206', 'label' => 'Rumah', 'alamat' => 'Jl. Pemuda No. 88, RT 01/RW 09, Semarang Tengah', 'kota' => 'Semarang', 'provinsi' => 'Jawa Tengah', 'pos' => '50132'],
            ['nama' => 'Maya Sari', 'email' => 'maya.sari21@gmail.com', 'telepon' => '081234501207', 'label' => 'Kos', 'alamat' => 'Kos Putri Melati, Jl. Margonda Raya No. 220 Kamar 12', 'kota' => 'Depok', 'provinsi' => 'Jawa Barat', 'pos' => '16424'],
            ['nama' => 'Bambang Susilo', 'email' => 'bambang.susilo@gmail.com', 'telepon' => '081234501208', 'label' => 'Rumah', 'alamat' => 'Jl. Diponegoro No. 55, Klojen', 'kota' => 'Malang', 'provinsi' => 'Jawa Timur', 'pos' => '65111'],
            ['nama' => 'Fitri Handayani', 'email' => 'fitri.handayani@gmail.com', 'telepon' => '081234501209', 'label' => 'Rumah', 'alamat' => 'Perum Bumi Serpong Damai Sektor 2 Blok D No. 21', 'kota' => 'Tangerang Selatan', 'provinsi' => 'Banten', 'pos' => '15310'],
            ['nama' => 'Andi Kurniawan', 'email' => 'andi.kurniawan@gmail.com', 'telepon' => '081234501210', 'label' => 'Rumah', 'alamat' => 'Jl. Ahmad Yani No. 76, Panakkukang', 'kota' => 'Makassar', 'provinsi' => 'Sulawesi Selatan', 'pos' => '90231'],
            ['nama' => 'Nur Aisyah', 'email' => 'nur.aisyah@yahoo.com', 'telepon' => '081234501211', 'label' => 'Rumah', 'alamat' => 'Jl. Gajah Mada No. 19, RT 04/RW 02, Gubeng', 'kota' => 'Surabaya', 'provinsi' => 'Jawa Timur', 'pos' => '60281'],
            ['nama' => 'Rizky Ramadhan', 'email' => 'rizky.ramadhan@gmail.com', 'telepon' => '081234501212', 'label' => 'Rumah', 'alamat' => 'Jl. Setiabudi No. 102, Bandung Utara', 'kota' => 'Bandung', 'provinsi' => 'Jawa Barat', 'pos' => '40141'],
            ['nama' => 'Sri Wahyuni', 'email' => 'sri.wahyuni@gmail.com', 'telepon' => '081234501213', 'label' => 'Rumah', 'alamat' => 'Jl. Solo Baru Blok AA No. 14, Grogol', 'kota' => 'Sukoharjo', 'provinsi' => 'Jawa Tengah', 'pos' => '57552'],
            ['nama' => 'Dimas Anggara', 'email' => 'dimas.anggara@gmail.com', 'telepon' => '081234501214', 'label' => 'Kantor', 'alamat' => 'Ruko Golden Boulevard Blok J No. 5, BSD', 'kota' => 'Tangerang Selatan', 'provinsi' => 'Banten', 'pos' => '15322'],
            ['nama' => 'Lia Amelia', 'email' => 'lia.amelia@gmail.com', 'telepon' => '081234501215', 'label' => 'Rumah', 'alamat' => 'Jl. Kebon Jeruk Raya No. 33, RT 07/RW 01', 'kota' => 'Jakarta Barat', 'provinsi' => 'DKI Jakarta', 'pos' => '11530'],
            ['nama' => 'Teguh Santoso', 'email' => 'teguh.santoso@gmail.com', 'telepon' => '081234501216', 'label' => 'Rumah', 'alamat' => 'Jl. Imam Bonjol No. 210, Denpasar Barat', 'kota' => 'Denpasar', 'provinsi' => 'Bali', 'pos' => '80119'],
            ['nama' => 'Indah Permatasari', 'email' => 'indah.permata@gmail.com', 'telepon' => '081234501217', 'label' => 'Rumah', 'alamat' => 'Jl. Sisingamangaraja No. 47, Medan Kota', 'kota' => 'Medan', 'provinsi' => 'Sumatera Utara', 'pos' => '20212'],
            ['nama' => 'Yusuf Maulana', 'email' => 'yusuf.maulana@gmail.com', 'telepon' => '081234501218', 'label' => 'Rumah', 'alamat' => 'Jl. Veteran No. 8, RT 02/RW 06, Bogor Tengah', 'kota' => 'Bogor', 'provinsi' => 'Jawa Barat', 'pos' => '16123'],
            ['nama' => 'Ratna Dewi', 'email' => 'ratna.dewi@yahoo.com', 'telepon' => '081234501219', 'label' => 'Rumah', 'alamat' => 'Perum Pondok Indah Blok F No. 17, Bekasi Timur', 'kota' => 'Bekasi', 'provinsi' => 'Jawa Barat', 'pos' => '17111'],
            ['nama' => 'Fajar Nugroho', 'email' => 'fajar.nugroho@gmail.com', 'telepon' => '081234501220', 'label' => 'Kos', 'alamat' => 'Kos Pak Har, Jl. Babarsari No. 44 Kamar 7', 'kota' => 'Sleman', 'provinsi' => 'DI Yogyakarta', 'pos' => '55281'],
            ['nama' => 'Wulan Puspita', 'email' => 'wulan.puspita@gmail.com', 'telepon' => '081234501221', 'label' => 'Rumah', 'alamat' => 'Jl. Basuki Rahmat No. 91, Genteng', 'kota' => 'Surabaya', 'provinsi' => 'Jawa Timur', 'pos' => '60271'],
            ['nama' => 'Iwan Setiadi', 'email' => 'iwan.setiadi@gmail.com', 'telepon' => '081234501222', 'label' => 'Rumah', 'alamat' => 'Jl. Cendrawasih No. 5, RT 03/RW 04, Cimahi Tengah', 'kota' => 'Cimahi', 'provinsi' => 'Jawa Barat', 'pos' => '40521'],
            ['nama' => 'Novita Sari', 'email' => 'novita.sari@gmail.com', 'telepon' => '081234501223', 'label' => 'Rumah', 'alamat' => 'Jl. Sudirman No. 128, Ilir Barat I', 'kota' => 'Palembang', 'provinsi' => 'Sumatera Selatan', 'pos' => '30129'],
            ['nama' => 'Arif Hidayat', 'email' => 'arif.hidayat@gmail.com', 'telepon' => '081234501224', 'label' => 'Rumah', 'alamat' => 'Jl. Pahlawan No. 62, RT 05/RW 02, Kartasura', 'kota' => 'Sukoharjo', 'provinsi' => 'Jawa Tengah', 'pos' => '57169'],
            ['nama' => 'Puput Melati', 'email' => 'puput.melati@gmail.com', 'telepon' => '081234501225', 'label' => 'Rumah', 'alamat' => 'Jl. Raya Ciputat No. 301, Pondok Aren', 'kota' => 'Tangerang Selatan', 'provinsi' => 'Banten', 'pos' => '15224'],
        ];
    }
}
