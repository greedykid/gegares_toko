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
                'name' => 'Kastengel',
                'description' => 'Kue keju premium dengan rasa gurih yang kaya. Menggunakan keju Edam asli untuk cita rasa terbaik.',
                'price' => 45000,
                'stock' => 20,
                'is_featured' => true,
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
                'name' => 'Tahu Isi Sayur',
                'description' => 'Tahu goreng renyah dengan isian sayur kol, wortel, dan tauge yang gurih pedas.',
                'price' => 10000,
                'stock' => 50,
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
            [
                'category' => 'bubur-tradisional',
                'name' => 'Bubur Candil',
                'description' => 'Bola-bola ketan kenyal (intil) dengan kuah gula merah manis legit disiram santan kental yang gurih.',
                'price' => 16000,
                'stock' => 20,
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
                    'rating_avg' => rand(35, 50) / 10,
                    'rating_count' => rand(5, 120),
                ]
            );
        }
    }
}
