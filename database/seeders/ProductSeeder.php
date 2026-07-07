<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Kue Basah', 'slug' => 'kue-basah', 'description' => 'Kue tradisional dengan tekstur lembut dan basah'],
            ['name' => 'Kue Kering', 'slug' => 'kue-kering', 'description' => 'Kue renyah dan tahan lama'],
            ['name' => 'Gorengan', 'slug' => 'gorengan', 'description' => 'Jajanan goreng yang renyah dan gurih'],
            ['name' => 'Jajanan Kukus', 'slug' => 'jajanan-kukus', 'description' => 'Jajanan sehat yang dikukus sempurna'],
            ['name' => 'Minuman Tradisional', 'slug' => 'minuman-tradisional', 'description' => 'Minuman hangat dan menyegarkan khas nusantara'],
            ['name' => 'Bubur Tradisional', 'slug' => 'bubur-tradisional', 'description' => 'Bubur manis lembut dengan kuah santan dan gula merah'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['slug' => $cat['slug']], $cat);
        }

        $products = [
            // Kue Basah
            [
                'category' => 'kue-basah',
                'name' => 'Klepon',
                'description' => 'Bola-bola ketan hijau berisi gula merah cair, dibalut kelapa parut segar. Sensasi ledakan manis di setiap gigitan.',
                'price' => 15000,
                'stock' => 50,
                'is_featured' => true,
            ],
            [
                'category' => 'kue-basah',
                'name' => 'Kue Lapis',
                'description' => 'Kue berlapis warna-warni dengan cita rasa manis legit. Dibuat dari tepung beras dan santan pilihan.',
                'price' => 25000,
                'stock' => 30,
                'is_featured' => true,
            ],
            [
                'category' => 'kue-basah',
                'name' => 'Onde-Onde',
                'description' => 'Bola ketan isi kacang hijau manis, dibalut wijen dan digoreng hingga keemasan. Renyah di luar, lembut di dalam.',
                'price' => 18000,
                'stock' => 40,
                'is_featured' => false,
            ],
            [
                'category' => 'kue-basah',
                'name' => 'Getuk Lindri',
                'description' => 'Singkong kukus yang dihaluskan dengan gula dan kelapa parut, dicetak cantik berwarna-warni.',
                'price' => 12000,
                'stock' => 35,
                'is_featured' => false,
            ],
            [
                'category' => 'kue-basah',
                'name' => 'Kue Lumpur',
                'description' => 'Kue lumpur kentang yang lembut, gurih santan dengan topping kismis manis di atasnya.',
                'price' => 14000,
                'stock' => 30,
                'is_featured' => false,
            ],
            [
                'category' => 'kue-basah',
                'name' => 'Bika Ambon',
                'description' => 'Kue tradisional berongga khas Medan dengan aroma pandan dan daun jeruk yang kuat serta manis legit.',
                'price' => 30000,
                'stock' => 15,
                'is_featured' => false,
            ],
            // Kue Kering
            [
                'category' => 'kue-kering',
                'name' => 'Kue Semprit',
                'description' => 'Kue kering klasik berbentuk bunga dengan tekstur renyah yang lumer di mulut. Cocok untuk camilan teman teh.',
                'price' => 35000,
                'stock' => 25,
                'is_featured' => false,
            ],
[
                'category' => 'kue-kering',
                'name' => 'Nastar Premium',
                'description' => 'Kue kering isi selai nanas madu buatan sendiri dengan mentega Wijsman yang wangi dan lumer di lidah.',
                'price' => 55000,
                'stock' => 20,
                'is_featured' => true,
            ],
            [
                'category' => 'kue-kering',
                'name' => 'Putri Salju',
                'description' => 'Kue kering berbentuk bulan sabit dibalur gula halus dingin yang manis lembut dan gurih kacang.',
                'price' => 40000,
                'stock' => 25,
                'is_featured' => false,
            ],
            // Gorengan
            [
                'category' => 'gorengan',
                'name' => 'Risoles Mayo',
                'description' => 'Kulit crepe renyah berisi ayam, mayones, dan sayuran segar. Digoreng dengan tepung panir hingga keemasan.',
                'price' => 20000,
                'stock' => 45,
                'is_featured' => true,
            ],
            [
                'category' => 'gorengan',
                'name' => 'Pastel Isi Ragout',
                'description' => 'Kulit pastri renyah berlapis-lapis dengan isian ragout ayam wortel yang creamy dan gurih.',
                'price' => 22000,
                'stock' => 35,
                'is_featured' => false,
            ],
            [
                'category' => 'gorengan',
                'name' => 'Lumpia Semarang',
                'description' => 'Lumpia goreng khas Semarang dengan isian rebung dan udang. Renyah dan beraroma harum.',
                'price' => 25000,
                'stock' => 30,
                'is_featured' => false,
            ],
            [
                'category' => 'gorengan',
                'name' => 'Combro',
                'description' => 'Jajanan Sunda dari singkong parut berisi oncom pedas. Digoreng hingga kecokelatan dan renyah.',
                'price' => 10000,
                'stock' => 0,
                'is_featured' => false,
            ],
[
                'category' => 'gorengan',
                'name' => 'Bakwan Jagung',
                'description' => 'Bakwan renyah dengan jagung manis pipil segar dan bumbu ketumbar daun bawang.',
                'price' => 12000,
                'stock' => 45,
                'is_featured' => false,
            ],
            // Jajanan Kukus
            [
                'category' => 'jajanan-kukus',
                'name' => 'Nagasari',
                'description' => 'Kue kukus dari tepung beras dan santan, dibungkus daun pisang dengan potongan pisang raja di dalamnya.',
                'price' => 15000,
                'stock' => 40,
                'is_featured' => true,
            ],
            [
                'category' => 'jajanan-kukus',
                'name' => 'Putu Bambu',
                'description' => 'Kue putu kukus di dalam bambu, berisi gula merah dan ditaburi kelapa parut. Aroma daun pandan yang harum.',
                'price' => 12000,
                'stock' => 3,
                'is_featured' => false,
            ],
            [
                'category' => 'jajanan-kukus',
                'name' => 'Dadar Gulung',
                'description' => 'Crepe hijau pandan lembut yang digulung berisi kelapa parut dan gula merah. Manis dan harum.',
                'price' => 18000,
                'stock' => 30,
                'is_featured' => false,
            ],
            [
                'category' => 'jajanan-kukus',
                'name' => 'Serabi Solo',
                'description' => 'Kue tradisional Solo dari tepung beras dan santan, disajikan dengan kuah kinca gula merah yang kental.',
                'price' => 16000,
                'stock' => 25,
                'is_featured' => true,
            ],
            [
                'category' => 'jajanan-kukus',
                'name' => 'Lemper Ayam',
                'description' => 'Ketan pulen berisi ayam suwir berbumbu, dibungkus daun pisang dan dikukus hingga harum.',
                'price' => 20000,
                'stock' => 35,
                'is_featured' => false,
            ],
            [
                'category' => 'jajanan-kukus',
                'name' => 'Apem Kukus',
                'description' => 'Kue mangkok apem mekar lembut beraroma tapai singkong dengan taburan kelapa parut gurih.',
                'price' => 13000,
                'stock' => 30,
                'is_featured' => false,
            ],
            // Minuman Tradisional
            [
                'category' => 'minuman-tradisional',
                'name' => 'Wedang Ronde',
                'description' => 'Bola ketan berisi kacang tanah disajikan dalam kuah jahe hangat yang manis dan pedas wangi.',
                'price' => 15000,
                'stock' => 30,
                'is_featured' => true,
            ],
            [
                'category' => 'minuman-tradisional',
                'name' => 'Es Dawet Ayu',
                'description' => 'Dawet kenyal beraroma pandan dengan santan gurih, gula kelapa kental, dan es batu segar.',
                'price' => 12000,
                'stock' => 40,
                'is_featured' => false,
            ],
            // Bubur Tradisional
            [
                'category' => 'bubur-tradisional',
                'name' => 'Bubur Sumsum',
                'description' => 'Bubur lembut dari tepung beras dan santan encer disiram kinca gula merah kental yang manis legit.',
                'price' => 15000,
                'stock' => 25,
                'is_featured' => true,
            ],
// ── Tambahan: Kue Basah ──
            [
                'category' => 'kue-basah',
                'name' => 'Kue Cubit',
                'description' => 'Kue mungil bertekstur lembut dengan topping meises cokelat yang manis. Disukai anak-anak maupun dewasa.',
                'price' => 13000,
                'stock' => 38,
                'is_featured' => false,
            ],
            [
                'category' => 'kue-basah',
                'name' => 'Kue Pukis',
                'description' => 'Kue berbentuk perahu yang empuk dan harum santan, dipanggang hingga kecokelatan dengan aroma vanila.',
                'price' => 14000,
                'stock' => 42,
                'is_featured' => false,
            ],
            [
                'category' => 'kue-basah',
                'name' => 'Carabikang',
                'description' => 'Kue beras mekar tiga warna khas Jawa dengan tekstur kenyal dan manis legit yang menggugah selera.',
                'price' => 15000,
                'stock' => 28,
                'is_featured' => false,
            ],
            [
                'category' => 'kue-basah',
                'name' => 'Kue Talam Ubi',
                'description' => 'Kue dua lapis dari ubi ungu manis dan santan gurih di atasnya. Lembut dengan warna cantik alami.',
                'price' => 16000,
                'stock' => 26,
                'is_featured' => true,
            ],
            [
                'category' => 'kue-basah',
                'name' => 'Wajik Ketan',
                'description' => 'Ketan pulen yang dimasak dengan gula merah dan santan hingga legit pekat. Manis khas jajanan hajatan.',
                'price' => 17000,
                'stock' => 24,
                'is_featured' => false,
            ],

            // ── Tambahan: Kue Kering ──
            [
                'category' => 'kue-kering',
                'name' => 'Lidah Kucing',
                'description' => 'Kue kering tipis renyah berbentuk lidah dengan rasa mentega yang gurih manis dan lumer.',
                'price' => 38000,
                'stock' => 22,
                'is_featured' => false,
            ],
            [
                'category' => 'kue-kering',
                'name' => 'Kue Sagu Keju',
                'description' => 'Kue kering dari tepung sagu yang lumer di mulut dengan rasa keju gurih dan aroma daun pandan.',
                'price' => 42000,
                'stock' => 18,
                'is_featured' => false,
            ],
            [
                'category' => 'kue-kering',
                'name' => 'Kue Kacang',
                'description' => 'Kue kering klasik dari kacang tanah sangrai yang gurih, renyah, dan meleleh saat digigit.',
                'price' => 36000,
                'stock' => 30,
                'is_featured' => false,
            ],

            // ── Tambahan: Gorengan ──
            [
                'category' => 'gorengan',
                'name' => 'Cireng Bumbu Rujak',
                'description' => 'Cireng kenyal renyah khas Sunda disajikan dengan sambal rujak pedas manis yang nagih.',
                'price' => 13000,
                'stock' => 48,
                'is_featured' => true,
            ],
            [
                'category' => 'gorengan',
                'name' => 'Tempe Mendoan',
                'description' => 'Tempe tipis berbalut adonan tepung berbumbu, digoreng setengah matang hingga lembut gurih.',
                'price' => 11000,
                'stock' => 50,
                'is_featured' => false,
            ],
            [
                'category' => 'gorengan',
                'name' => 'Pisang Goreng Crispy',
                'description' => 'Pisang kepok matang dibalut tepung renyah ekstra crispy. Manis alami dengan tekstur garing.',
                'price' => 12000,
                'stock' => 44,
                'is_featured' => false,
            ],
            [
                'category' => 'gorengan',
                'name' => 'Cakwe Original',
                'description' => 'Cakwe empuk berongga yang gurih, cocok dicocol saus asam manis atau dinikmati dengan bubur.',
                'price' => 14000,
                'stock' => 32,
                'is_featured' => false,
            ],

            // ── Tambahan: Jajanan Kukus ──
            [
                'category' => 'jajanan-kukus',
                'name' => 'Kue Mangkok',
                'description' => 'Kue beras mekar berbentuk mangkok dengan warna-warni lembut, manis dan kenyal di setiap gigitan.',
                'price' => 12000,
                'stock' => 36,
                'is_featured' => false,
            ],
// ── Tambahan: Minuman Tradisional ──
            [
                'category' => 'minuman-tradisional',
                'name' => 'Wedang Jahe Susu',
                'description' => 'Jahe segar yang dimasak dengan gula aren dan susu hangat. Menghangatkan badan dan menenangkan.',
                'price' => 14000,
                'stock' => 35,
                'is_featured' => false,
            ],
[
                'category' => 'minuman-tradisional',
                'name' => 'Bajigur',
                'description' => 'Minuman hangat khas Sunda dari santan, gula aren, dan jahe dengan potongan kolang-kaling.',
                'price' => 13000,
                'stock' => 32,
                'is_featured' => false,
            ],

            // ── Tambahan: Bubur Tradisional ──
            [
                'category' => 'bubur-tradisional',
                'name' => 'Bubur Ketan Hitam',
                'description' => 'Ketan hitam yang dimasak pulen dengan gula merah, disiram santan kental gurih yang creamy.',
                'price' => 16000,
                'stock' => 24,
                'is_featured' => false,
            ],
            [
                'category' => 'bubur-tradisional',
                'name' => 'Bubur Kacang Hijau',
                'description' => 'Kacang hijau empuk dimasak dengan jahe, gula merah, dan santan. Hangat, manis, dan mengenyangkan.',
                'price' => 15000,
                'stock' => 30,
                'is_featured' => false,
            ],
        ];

        foreach ($products as $p) {
            $category = Category::where('slug', $p['category'])->first();

            Product::updateOrCreate(
                ['slug' => Str::slug($p['name'])],
                [
                    'category_id' => $category->id,
                    'name' => $p['name'],
                    'slug' => Str::slug($p['name']),
                    'description' => $p['description'],
                    'price' => $p['price'],
                    'stock' => $p['stock'],
                    'is_featured' => $p['is_featured'],
                    'image' => 'products/' . Str::slug($p['name']) . '.png',
                    'rating_avg' => rand(35, 50) / 10,
                    'rating_count' => rand(5, 120),
                ]
            );
        }
    }
}
