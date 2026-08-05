<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Paket borongan yang ditawarkan, beserta diskonnya dalam persen.
     *
     * Isi 10 dijual tanpa potongan — itu paket terkecil, dan memberinya diskon
     * membuat harga satuan kehilangan artinya. Potongannya baru bertambah pada
     * jumlah yang benar-benar meringankan produksi.
     */
    private const PAKET = [10 => 0, 15 => 5, 20 => 8, 30 => 12];

    /**
     * Kategori yang barangnya dihitung per biji, jadi paket "isi sekian" masuk
     * akal di sana.
     *
     * Kue kering sengaja di luar daftar: satuannya toples, dan "isi 30" berarti
     * tiga puluh toples — bukan yang dimaksud siapa pun. Minuman dan bubur juga
     * di luar, karena dijual per cup.
     */
    private const KATEGORI_PAKET = ['kue-basah', 'gorengan', 'jajanan-kukus'];

    public function run(): void
    {
        $categories = [
            ['name' => 'Kue Basah', 'slug' => 'kue-basah', 'description' => 'Kue tradisional dengan tekstur lembut dan basah', 'image' => 'categories/kue-basah.webp'],
            ['name' => 'Kue Kering', 'slug' => 'kue-kering', 'description' => 'Kue renyah dan tahan lama', 'image' => 'categories/kue-kering.webp'],
            ['name' => 'Gorengan', 'slug' => 'gorengan', 'description' => 'Jajanan goreng yang renyah dan gurih', 'image' => 'categories/gorengan.webp'],
            ['name' => 'Jajanan Kukus', 'slug' => 'jajanan-kukus', 'description' => 'Jajanan sehat yang dikukus sempurna', 'image' => 'categories/jajanan-kukus.webp'],
            ['name' => 'Minuman Tradisional', 'slug' => 'minuman-tradisional', 'description' => 'Minuman hangat dan menyegarkan khas nusantara', 'image' => 'categories/minuman-tradisional.webp'],
            ['name' => 'Bubur Tradisional', 'slug' => 'bubur-tradisional', 'description' => 'Bubur manis lembut dengan kuah santan dan gula merah', 'image' => 'categories/bubur-tradisional.webp'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['slug' => $cat['slug']], $cat);
        }

        foreach ($this->produk() as $p) {
            $this->simpanPaket($this->simpanProduk($p), $p);
        }

        // Penilaian selalu diturunkan dari ulasan yang benar-benar ada. Versi
        // sebelumnya mengarang `rating_avg`/`rating_count` dengan rand(),
        // sehingga kartu produk memamerkan "4.4 (102)" untuk barang yang
        // halaman detailnya kosong melompong — angka yang tidak bisa
        // dipertanggungjawabkan ke pembeli. Dihitung ulang di sini supaya
        // sisa angka karangan itu ikut bersih.
        Product::query()->each(fn (Product $p) => $p->updateRating());
    }

    /** Simpan satu produk tanpa menyentuh penilaiannya kalau sudah ada. */
    private function simpanProduk(array $p): Product
    {
        $slug = $p['slug'] ?? Str::slug($p['name']);
        $category = Category::where('slug', $p['category'])->firstOrFail();

        $atribut = [
            'category_id' => $category->id,
            'name' => $p['name'],
            'slug' => $slug,
            'description' => $p['description'],
            'price' => $p['price'],
            'stock' => $p['stock'],
            'is_featured' => $p['is_featured'] ?? false,
            'image' => $p['image'] ?? null,
        ];

        $product = Product::withTrashed()->where('slug', $slug)->first();

        if ($product) {
            $product->fill($atribut)->save();

            return $product;
        }

        // Produk baru lahir tanpa penilaian. Bintangnya muncul sendiri begitu
        // ada pembeli yang benar-benar mengulas.
        return Product::create($atribut + ['rating_avg' => 0, 'rating_count' => 0]);
    }

    /**
     * Susun varian paket untuk produk yang dijual per biji.
     *
     * Stok tiap paket diturunkan dari stok satuannya, karena di aplikasi ini
     * stok varian adalah penghitung tersendiri: tanpa penurunan itu, paket isi
     * 30 bisa terlihat tersedia untuk barang yang satuannya tinggal tiga.
     */
    private function simpanPaket(Product $product, array $p): void
    {
        if (! in_array($p['category'], self::KATEGORI_PAKET, true)) {
            return;
        }

        $satuan = (int) $p['price'];
        $dipakai = [];

        foreach (self::PAKET as $isi => $diskon) {
            $nama = "Paket isi {$isi}";
            $dipakai[] = $nama;

            ProductVariant::updateOrCreate(
                ['product_id' => $product->id, 'name' => $nama],
                [
                    'price' => $this->hargaPaket($satuan, $isi, $diskon),
                    'stock' => intdiv((int) $p['stock'], $isi),
                    'is_active' => true,
                ],
            );
        }

        // Seeder ini yang memegang daftar paket resmi, jadi varian lama yang
        // tidak lagi ada di daftar dibuang — termasuk sisa penamaan versi
        // sebelumnya. Soft delete, supaya masih bisa ditelusuri kalau ternyata
        // ada yang mencarinya.
        $product->variants()->whereNotIn('name', $dipakai)->delete();
    }

    /** Harga satu paket, dibulatkan ke lima ratus terdekat agar enak disebut. */
    private function hargaPaket(int $satuan, int $isi, int $diskon): int
    {
        $kotor = $satuan * $isi * (100 - $diskon) / 100;

        return (int) (round($kotor / 500) * 500);
    }

    /**
     * Katalog jajanan.
     *
     * Harga di sini adalah harga **satuan** — per biji untuk jajanan, per
     * toples untuk kue kering, per cup untuk minuman dan bubur.
     *
     * @return list<array<string, mixed>>
     */
    private function produk(): array
    {
        return [
            // ── Kue Basah ────────────────────────────────────────────────────
            [
                'category' => 'kue-basah',
                'name' => 'Klepon',
                'description' => 'Bola ketan hijau yang di dalamnya ada gula merah cair — begitu digigit langsung meleleh, jadi hati-hati kalau masih hangat. Dibalut kelapa parut yang diparut pagi itu juga. Dihitung per biji.',
                'price' => 2000,
                'stock' => 120,
                'is_featured' => true,
                'image' => 'products/klepon.webp',
            ],
            [
                'category' => 'kue-basah',
                'name' => 'Kue Lapis',
                'description' => 'Lapisan warna-warninya dibuat satu per satu, dikukus bergantian sampai belasan lapis. Manis legit dari santan dan tepung beras pilihan. Satu potong ukuran sedang, pas untuk sekali makan.',
                'price' => 2500,
                'stock' => 90,
                'is_featured' => true,
                'image' => 'products/kue-lapis.webp',
            ],
            [
                'category' => 'kue-basah',
                'name' => 'Onde-Onde',
                'description' => 'Bola ketan isi kacang hijau manis, dibalut wijen lalu digoreng sampai keemasan. Renyah di luar, lembut di dalam. Dijual per biji, ukurannya sekepalan tangan anak.',
                'price' => 2500,
                'stock' => 100,
                'is_featured' => false,
                'image' => 'products/onde-onde.webp',
            ],
            [
                'category' => 'kue-basah',
                'name' => 'Getuk Lindri',
                'description' => 'Singkong kukus yang ditumbuk halus dengan gula dan kelapa parut, lalu dicetak bergelombang. Warnanya lembut karena pakai pewarna makanan seadanya. Per potong.',
                'price' => 2000,
                'stock' => 80,
                'is_featured' => false,
                'image' => 'products/getuk-lindri.webp',
            ],
            [
                'category' => 'kue-basah',
                'name' => 'Kue Lumpur',
                'description' => 'Adonan kentang dan santan yang dipanggang pelan sampai permukaannya berkulit tipis, sementara dalamnya tetap basah lembut. Ada kismis satu di atasnya. Dihitung per biji.',
                'price' => 3000,
                'stock' => 70,
                'is_featured' => false,
                'image' => 'products/kue-lumpur.webp',
            ],
            [
                'category' => 'kue-basah',
                'name' => 'Bika Ambon',
                'description' => 'Berongga sampai ke dasar karena difermentasi semalaman — itu yang bikin teksturnya kenyal khas. Aroma pandan dan daun jeruknya kuat. Dijual per potong, tebal sekitar tiga jari.',
                'price' => 5000,
                'stock' => 40,
                'is_featured' => false,
                'image' => 'products/bika-ambon.webp',
            ],
            [
                'category' => 'kue-basah',
                'name' => 'Kue Cubit',
                'description' => 'Kue mungil setengah matang dengan meises cokelat yang masih lumer di tengah. Anak-anak biasanya minta tambah. Per biji, jadi enak dibeli banyak untuk rame-rame.',
                'price' => 2000,
                'stock' => 110,
                'is_featured' => false,
                'image' => 'products/kue-cubit.webp',
            ],
            [
                'category' => 'kue-basah',
                'name' => 'Kue Pukis',
                'description' => 'Dipanggang di cetakan perahu sampai pinggirannya kecokelatan dan garing, tengahnya tetap empuk. Wangi santan dan vanila. Satu biji ukuran standar.',
                'price' => 2500,
                'stock' => 100,
                'is_featured' => false,
                'image' => 'products/kue-pukis.webp',
            ],
            [
                'category' => 'kue-basah',
                'name' => 'Carabikang',
                'description' => 'Kue beras yang mekar merekah di bagian bawahnya — tanda adonannya pas. Tiga warna lembut, teksturnya kenyal dan manis legit. Dihitung per biji.',
                'price' => 2500,
                'stock' => 75,
                'is_featured' => false,
                'image' => 'products/carabikang.webp',
            ],
            [
                'category' => 'kue-basah',
                'name' => 'Kue Talam Ubi',
                'description' => 'Dua lapis: ubi ungu manis di bawah, santan gurih putih di atasnya. Warnanya ungu alami dari ubinya sendiri, bukan pewarna. Per potong ukuran sedang.',
                'price' => 3000,
                'stock' => 65,
                'is_featured' => true,
                'image' => 'products/kue-talam-ubi.webp',
            ],
            [
                'category' => 'kue-basah',
                'name' => 'Wajik Ketan',
                'description' => 'Ketan yang dimasak berjam-jam dengan gula merah dan santan sampai mengkilat dan legit pekat. Yang biasa muncul di hajatan. Dipotong wajik, dihitung per potong.',
                'price' => 2500,
                'stock' => 60,
                'is_featured' => false,
                'image' => 'products/wajik-ketan.webp',
            ],

            // ── Kue Kering (dijual per toples) ───────────────────────────────
            [
                'category' => 'kue-kering',
                'name' => 'Kue Semprit',
                'description' => 'Kue kering klasik bentuk bunga yang renyahnya langsung lumer di mulut. Teman minum teh sore. Satu toples isi sekitar 250 gram, kurang lebih 50 keping.',
                'price' => 35000,
                'stock' => 25,
                'is_featured' => false,
                'image' => 'products/kue-semprit.webp',
            ],
            [
                'category' => 'kue-kering',
                'name' => 'Nastar Premium',
                'description' => 'Selai nanasnya dimasak sendiri dari nanas madu, bukan selai jadi — itu yang bikin rasanya tidak terlalu manis. Mentega Wijsman-nya kerasa wangi. Toples 300 gram, isi sekitar 40 butir.',
                'price' => 55000,
                'stock' => 20,
                'is_featured' => true,
                'image' => 'products/nastar-premium.webp',
            ],
            [
                'category' => 'kue-kering',
                'name' => 'Putri Salju',
                'description' => 'Bulan sabit yang dibalur gula halus tebal sampai putih seperti tertutup salju. Gurih kacangnya berimbang dengan manisnya. Satu toples 250 gram, isi kurang lebih 45 keping.',
                'price' => 40000,
                'stock' => 25,
                'is_featured' => false,
                'image' => 'products/putri-salju.webp',
            ],
            [
                'category' => 'kue-kering',
                'name' => 'Lidah Kucing',
                'description' => 'Tipis, panjang, dan renyah dengan rasa mentega yang gurih manis. Karena kepingnya tipis, satu toples 200 gram isinya banyak — sekitar 70 keping.',
                'price' => 38000,
                'stock' => 22,
                'is_featured' => false,
                'image' => 'products/lidah-kucing.webp',
            ],
            [
                'category' => 'kue-kering',
                'name' => 'Kue Sagu Keju',
                'description' => 'Dari tepung sagu, jadi begitu masuk mulut langsung lumer tanpa perlu dikunyah. Kejunya gurih, aroma pandannya menyusul belakangan. Toples 250 gram, isi sekitar 55 keping.',
                'price' => 42000,
                'stock' => 18,
                'is_featured' => false,
                'image' => 'products/kue-sagu-keju.webp',
            ],
            [
                'category' => 'kue-kering',
                'name' => 'Kue Kacang',
                'description' => 'Kacang tanahnya disangrai dan digiling sendiri, jadi wanginya beda dengan yang pakai tepung kacang instan. Renyah dan meleleh. Toples 250 gram, isi sekitar 45 keping.',
                'price' => 36000,
                'stock' => 30,
                'is_featured' => false,
                'image' => 'products/kue-kacang.webp',
            ],

            // ── Gorengan ─────────────────────────────────────────────────────
            [
                'category' => 'gorengan',
                'name' => 'Risoles Mayo',
                'description' => 'Kulit crepe tipis berisi ayam, mayones, dan sayuran, dibalut tepung panir lalu digoreng sampai keemasan. Paling enak dimakan selagi hangat. Dihitung per biji.',
                'price' => 2500,
                'stock' => 120,
                'is_featured' => true,
                'image' => 'products/risoles-mayo.webp',
            ],
            [
                'category' => 'gorengan',
                'name' => 'Risol Sayur',
                'description' => 'Isinya wortel, kentang, dan buncis yang ditumis berbumbu dulu — bukan sekadar sayur rebus. Gurihnya ringan, cocok untuk yang tidak mau terlalu berat. Per biji.',
                'price' => 2000,
                'stock' => 100,
                'is_featured' => false,
                'image' => 'products/risol-sayur.webp',
            ],
            [
                'category' => 'gorengan',
                'name' => 'Risol Ayam Pedas',
                'description' => 'Ayam suwir yang dimasak dengan cabai sampai benar-benar meresap, jadi pedasnya bukan cuma di permukaan. Dibalut panir renyah. Per biji, dan yang tidak kuat pedas sebaiknya pesan yang mayo.',
                'price' => 2500,
                'stock' => 90,
                'is_featured' => true,
                'image' => 'products/risol-ayam-pedas.webp',
            ],
            [
                'category' => 'gorengan',
                'name' => 'Pastel Sayur Bihun',
                'description' => 'Bihun, wortel, dan telur berbumbu dijejalkan padat ke dalam kulit tipis yang dipilin di pinggirnya. Isinya penuh, bukan cuma kulit. Dihitung per biji.',
                'price' => 2500,
                'stock' => 110,
                'is_featured' => true,
                'image' => 'products/pastel-sayur-bihun.webp',
            ],
            [
                'category' => 'gorengan',
                'name' => 'Pastel Isi Ragout',
                'description' => 'Kulit pastri berlapis-lapis yang renyah, isinya ragout ayam wortel yang creamy dan gurih. Sedikit lebih besar dari pastel biasa. Per biji.',
                'price' => 3000,
                'stock' => 70,
                'is_featured' => false,
                'image' => 'products/pastel-isi-ragout.webp',
            ],
            [
                'category' => 'gorengan',
                'name' => 'Sosis Solo',
                'description' => 'Dadar tipis yang menggulung ayam cincang berbumbu, digoreng sebentar supaya luarnya garing tapi dalamnya tetap lembut. Khas Solo. Dihitung per biji.',
                'price' => 2500,
                'stock' => 90,
                'is_featured' => false,
                'image' => 'products/sosis-solo.webp',
            ],
            [
                'category' => 'gorengan',
                'name' => 'Gabin',
                'description' => 'Biskuit gabin yang diisi fla vanila, dibalut adonan tipis lalu digoreng sekilas. Renyah di luar, lembut manis di dalam. Per biji.',
                'price' => 2500,
                'stock' => 80,
                'is_featured' => false,
                'image' => 'products/gabin.webp',
            ],
            [
                'category' => 'gorengan',
                'name' => 'Molen',
                'description' => 'Pisang manis dililit adonan pastri lalu digoreng sampai keemasan. Yang klasik dan tidak pernah salah. Satu biji seukuran jari telunjuk orang dewasa.',
                'price' => 2000,
                'stock' => 100,
                'is_featured' => false,
                'image' => 'products/molen.webp',
            ],
            [
                'category' => 'gorengan',
                'name' => 'Martabak Tahu Kulit Lumpia',
                'description' => 'Tahu berbumbu dicampur telur lalu dibungkus kulit lumpia tipis dan digoreng garing. Gurih sederhana yang bikin tangan susah berhenti. Dihitung per biji.',
                'price' => 2000,
                'stock' => 95,
                'is_featured' => false,
                'image' => 'products/martabak-tahu-kulit-lumpia.webp',
            ],
            [
                'category' => 'gorengan',
                'name' => 'Lumpia Semarang',
                'description' => 'Isian rebung dan udang yang ditumis lama sampai wangi, dibungkus kulit lumpia lalu digoreng. Ukurannya lebih besar dari lumpia biasa, jadi satu biji sudah lumayan mengenyangkan.',
                'price' => 3500,
                'stock' => 60,
                'is_featured' => false,
                'image' => 'products/lumpia-semarang.webp',
            ],
            [
                'category' => 'gorengan',
                'name' => 'Combro',
                'description' => 'Singkong parut berisi oncom pedas, digoreng sampai kecokelatan. Nama aslinya oncom di jero — oncom di dalam. Dihitung per biji.',
                'price' => 2000,
                'stock' => 0,
                'is_featured' => false,
                'image' => 'products/combro.webp',
            ],
            [
                'category' => 'gorengan',
                'name' => 'Bakwan Jagung',
                'description' => 'Jagung manis pipil segar yang dipipil sendiri, dibumbui ketumbar dan daun bawang. Renyah di pinggir, padat di tengah. Per biji.',
                'price' => 2000,
                'stock' => 130,
                'is_featured' => false,
                'image' => 'products/bakwan-jagung.webp',
            ],
            [
                'category' => 'gorengan',
                'name' => 'Cireng Bumbu Rujak',
                'description' => 'Cireng yang kenyal di dalam dan renyah di luar, disajikan dengan sambal rujak pedas manis yang bikin nagih. Sambalnya dipisah, jadi bisa diatur sendiri. Per biji.',
                'price' => 2000,
                'stock' => 140,
                'is_featured' => true,
                'image' => 'products/cireng-bumbu-rujak.webp',
            ],
            [
                'category' => 'gorengan',
                'name' => 'Tempe Mendoan',
                'description' => 'Tempe diiris tipis lebar, dibalut adonan tepung berbumbu, digoreng setengah matang supaya tetap lembut — memang begitu seharusnya mendoan. Dihitung per lembar.',
                'price' => 2000,
                'stock' => 150,
                'is_featured' => false,
                'image' => 'products/tempe-mendoan.webp',
            ],
            [
                'category' => 'gorengan',
                'name' => 'Pisang Goreng Crispy',
                'description' => 'Pisang kepok matang dibalut tepung yang bikin luarnya garing sampai berbunyi. Manisnya alami dari pisangnya, tidak ditambah gula. Per biji.',
                'price' => 2500,
                'stock' => 120,
                'is_featured' => false,
                'image' => 'products/pisang-goreng-crispy.webp',
            ],
            [
                'category' => 'gorengan',
                'name' => 'Cakwe Original',
                'description' => 'Empuk berongga dan gurih, enak dicocol saus asam manis atau dipotong-potong ke dalam bubur. Satu batang ukuran standar, dihitung per biji.',
                'price' => 3000,
                'stock' => 70,
                'is_featured' => false,
                'image' => 'products/cakwe-original.webp',
            ],

            // ── Jajanan Kukus ────────────────────────────────────────────────
            [
                'category' => 'jajanan-kukus',
                'name' => 'Nagasari',
                'description' => 'Tepung beras dan santan membungkus potongan pisang raja, lalu dikukus dalam daun pisang — daunnya yang bikin aromanya khas. Per biji.',
                'price' => 2500,
                'stock' => 100,
                'is_featured' => true,
                'image' => 'products/nagasari.webp',
            ],
            [
                'category' => 'jajanan-kukus',
                'name' => 'Putu Bambu',
                'description' => 'Dikukus di dalam batang bambu betulan, berisi gula merah yang meleleh saat dibuka, lalu ditaburi kelapa parut. Aroma pandannya kerasa dari jauh. Dihitung per batang.',
                'price' => 2000,
                'stock' => 3,
                'is_featured' => false,
                'image' => 'products/putu-bambu.webp',
            ],
            [
                'category' => 'jajanan-kukus',
                'name' => 'Putu Ayu',
                'description' => 'Kue kukus pandan dengan kelapa parut menempel rapi di permukaannya, dicetak bentuk bunga. Lembut dan wangi. Per biji.',
                'price' => 2500,
                'stock' => 90,
                'is_featured' => true,
                'image' => 'products/putu-ayu.webp',
            ],
            [
                'category' => 'jajanan-kukus',
                'name' => 'Kue Bugis',
                'description' => 'Ketan hitam kukus berisi unti kelapa gula merah, dibungkus daun pisang. Kenyal legit yang khas jajanan pasar. Dihitung per biji.',
                'price' => 2500,
                'stock' => 80,
                'is_featured' => false,
                'image' => 'products/kue-bugis.webp',
            ],
            [
                'category' => 'jajanan-kukus',
                'name' => 'Semar Mendem',
                'description' => 'Ketan gurih berisi ayam suwir berbumbu, lalu dibalut dadar telur tipis. Lebih mengenyangkan dari lemper, sering dipesan untuk suguhan hajatan. Per biji.',
                'price' => 3500,
                'stock' => 70,
                'is_featured' => true,
                'image' => 'products/semar-mendem.webp',
            ],
            [
                'category' => 'jajanan-kukus',
                'name' => 'Dadar Gulung',
                'description' => 'Crepe hijau pandan yang lembut, digulung berisi kelapa parut dan gula merah yang masih basah. Manis dan harum. Dihitung per gulung.',
                'price' => 2500,
                'stock' => 95,
                'is_featured' => false,
                'image' => 'products/dadar-gulung.webp',
            ],
            [
                'category' => 'jajanan-kukus',
                'name' => 'Serabi Solo',
                'description' => 'Dimasak satu per satu di wajan kecil sampai pinggirannya tipis renyah dan tengahnya tebal lembut. Disajikan dengan kuah kinca gula merah yang kental. Per biji.',
                'price' => 3000,
                'stock' => 70,
                'is_featured' => true,
                'image' => 'products/serabi-solo.webp',
            ],
            [
                'category' => 'jajanan-kukus',
                'name' => 'Lemper Ayam',
                'description' => 'Ketan pulen berisi ayam suwir berbumbu, dibungkus daun pisang lalu dikukus sampai daunnya meresap. Ukurannya cukup mengenyangkan untuk pengganjal siang. Per biji.',
                'price' => 3500,
                'stock' => 85,
                'is_featured' => false,
                'image' => 'products/lemper-ayam.webp',
            ],
            [
                'category' => 'jajanan-kukus',
                'name' => 'Apem Kukus',
                'description' => 'Mekar lembut dengan aroma tapai singkong yang bikin rasanya sedikit asam manis, ditaburi kelapa parut gurih. Dihitung per biji.',
                'price' => 2000,
                'stock' => 90,
                'is_featured' => false,
                'image' => 'products/apem-kukus.webp',
            ],
            [
                'category' => 'jajanan-kukus',
                'name' => 'Kue Mangkok',
                'description' => 'Kue beras yang merekah di atasnya seperti bunga — tanda dikukus dengan api yang benar. Warna-warni lembut, manis dan kenyal. Per biji.',
                'price' => 2000,
                'stock' => 100,
                'is_featured' => false,
                'image' => 'products/kue-mangkok.webp',
            ],

            // ── Minuman Tradisional (dijual per cup) ─────────────────────────
            [
                'category' => 'minuman-tradisional',
                'name' => 'Wedang Ronde',
                'description' => 'Bola ketan berisi kacang tanah tumbuk, direndam kuah jahe hangat yang manis sekaligus pedas wangi. Paling pas diminum malam. Satu cup 300 ml, isi 4 bola ronde.',
                'price' => 10000,
                'stock' => 40,
                'is_featured' => true,
                'image' => 'products/wedang-ronde.webp',
            ],
            [
                'category' => 'minuman-tradisional',
                'name' => 'Es Dawet Ayu',
                'description' => 'Dawet kenyal beraroma pandan dengan santan gurih dan gula kelapa kental, disajikan dingin dengan es batu. Satu cup 300 ml.',
                'price' => 8000,
                'stock' => 50,
                'is_featured' => false,
                'image' => 'products/es-dawet-ayu.webp',
            ],
            [
                'category' => 'minuman-tradisional',
                'name' => 'Wedang Jahe Susu',
                'description' => 'Jahe segar yang digeprek dan direbus dengan gula aren, lalu dicampur susu hangat. Menghangatkan tanpa terlalu pedas di tenggorokan. Cup 300 ml.',
                'price' => 8000,
                'stock' => 45,
                'is_featured' => false,
                'image' => 'products/wedang-jahe-susu.webp',
            ],
            [
                'category' => 'minuman-tradisional',
                'name' => 'Bajigur',
                'description' => 'Santan, gula aren, dan jahe yang direbus bersama, ditambah potongan kolang-kaling. Minuman hangat khas Sunda. Satu cup 300 ml.',
                'price' => 8000,
                'stock' => 40,
                'is_featured' => false,
                'image' => 'products/bajigur.webp',
            ],

            // ── Bubur & Kolak (dijual per cup) ───────────────────────────────
            [
                'category' => 'bubur-tradisional',
                'name' => 'Bubur Sumsum',
                'description' => 'Tepung beras dan santan encer yang diaduk terus sampai selembut sumsum, disiram kinca gula merah kental. Satu cup 300 ml, cukup untuk sarapan ringan.',
                'price' => 8000,
                'stock' => 40,
                'is_featured' => true,
                'image' => 'products/bubur-sumsum.webp',
            ],
            [
                'category' => 'bubur-tradisional',
                'name' => 'Biji Salak',
                'description' => 'Bola ubi kenyal dalam kuah gula merah kental, disiram santan gurih di atasnya. Manis hangat yang lumayan mengenyangkan. Satu cup 300 ml.',
                'price' => 10000,
                'stock' => 40,
                'is_featured' => true,
                'image' => 'products/biji-salak.webp',
            ],
            [
                'category' => 'bubur-tradisional',
                'name' => 'Kolek',
                'description' => 'Pisang dan ubi direbus pelan dalam santan gula aren dengan daun pandan sampai kuahnya meresap. Favorit sepanjang tahun, apalagi pas puasa. Cup 300 ml.',
                'price' => 8000,
                'stock' => 45,
                'is_featured' => false,
                'image' => 'products/kolek.webp',
            ],
            [
                'category' => 'bubur-tradisional',
                'name' => 'Bubur Ketan Hitam',
                'description' => 'Ketan hitam yang direbus lama sampai pulen dan pecah sendiri, dimaniskan gula merah lalu disiram santan kental. Satu cup 300 ml.',
                'price' => 10000,
                'stock' => 35,
                'is_featured' => false,
                'image' => 'products/bubur-ketan-hitam.webp',
            ],
            [
                'category' => 'bubur-tradisional',
                'name' => 'Bubur Kacang Hijau',
                'description' => 'Kacang hijaunya direbus sampai benar-benar empuk bersama jahe dan gula merah, disiram santan. Hangat, manis, dan mengenyangkan. Cup 300 ml.',
                'price' => 8000,
                'stock' => 45,
                'is_featured' => false,
                'image' => 'products/bubur-kacang-hijau.webp',
            ],
        ];
    }
}
