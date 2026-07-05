<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CustomerDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Dapatkan atau buat Pelanggan
        $user = User::firstOrCreate(
            ['email' => 'client@example.com'],
            [
                'name' => 'Rian Kurniawan',
                'password' => bcrypt('password123'),
                'role' => 'user',
                'phone' => '081234567890',
                'email_verified_at' => now(),
            ]
        );

        // 2. Buat Alamat Pengiriman Utama jika belum ada
        $address = Address::firstOrCreate(
            [
                'user_id' => $user->id,
                'is_primary' => true
            ],
            [
                'label' => 'Rumah Rian',
                'recipient_name' => 'Rian Kurniawan',
                'phone' => '081234567890',
                'address_line' => 'Jl. Ketapang Indah No. 45, Kebayoran Baru',
                'city' => 'Jakarta Selatan',
                'province' => 'DKI Jakarta',
                'postal_code' => '12130',
                'latitude' => -6.2444,
                'longitude' => 106.8006,
                'biteship_location_id' => 'loc-1234567890'
            ]
        );

        // 3. Dapatkan daftar produk yang ada
        $products = Product::all();
        if ($products->isEmpty()) {
            $this->command->warn('Harap jalankan ProductSeeder terlebih dahulu sebelum menjalankan CustomerDemoSeeder.');
            return;
        }

        // Kumpulan ulasan Bahasa Indonesia realistis
        $comments = [
            5 => [
                'Rasanya mantap banget! Ketagihan beli di sini.',
                'Teksturnya empuk dan pas manis/asinnya. Masih hangat pas sampai!',
                'Jajanan tradisional paling enak yang pernah saya beli online. Sangat direkomendasikan.',
                'Pelayanan sangat cepat, porsi pas, rasa otentik banget mirip resep rumahan.',
                'Sangat cocok buat cemilan sore bersama keluarga. Mantap gegares!',
                'Kemasan rapi dan bersih. Kualitas bahan makanannya terasa premium.',
                'Enak banget parah, ga nyesel beli di gegares!'
            ],
            4 => [
                'Rasanya enak, porsi pas. Pengiriman agak sedikit telat tapi ga masalah.',
                'Jajanannya enak sekali, cuma kurang pedas sedikit bagi selera saya.',
                'Manisnya pas ga bikin enek. Next time bakal order varian lainnya.',
                'Kualitas makanan sangat baik, bersih, dan segar. Terima kasih.',
                'Secara keseluruhan puas, rasanya pas di lidah Indonesia.'
            ]
        ];

        // 4. Buat 3 pesanan selesai (completed) untuk user tersebut
        for ($o = 1; $o <= 3; $o++) {
            $orderProducts = $products->random(rand(2, 3));
            
            $subtotal = 0;
            $itemsData = [];

            foreach ($orderProducts as $product) {
                $qty = rand(1, 2);
                $price = $product->price;
                $itemSubtotal = $price * $qty;
                $subtotal += $itemSubtotal;

                $itemsData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_price' => $price,
                    'quantity' => $qty,
                    'subtotal' => $itemSubtotal,
                ];
            }

            $shippingCost = 10000;
            $total = $subtotal + $shippingCost;

            // Buat Pesanan
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'GGR-DEMO-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4)),
                'address_id' => $address->id,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total' => $total,
                'status' => 'completed',
                'payment_status' => 'paid',
                'payment_method' => 'qris',
                'paid_at' => now()->subDays(rand(1, 10)),
            ]);

            // Buat Detail Item Pesanan & Ulasan
            foreach ($itemsData as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'product_price' => $item['product_price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal'],
                ]);

                // Buat Ulasan (Review) untuk produk ini
                $rating = rand(4, 5);
                $commentList = $comments[$rating];
                $comment = $commentList[array_rand($commentList)];

                Review::create([
                    'user_id' => $user->id,
                    'product_id' => $item['product_id'],
                    'order_id' => $order->id,
                    'rating' => $rating,
                    'comment' => $comment,
                    'is_approved' => true, // Langsung disetujui agar langsung tampil
                ]);
            }
        }

        // 5. Update rating_avg dan rating_count di semua produk agar sinkron dengan ulasan asli
        foreach ($products as $product) {
            $product->updateRating();
        }

        $this->command->info('CustomerDemoSeeder berhasil dijalankan: Akun Rian (client@example.com) beserta 3 riwayat pesanan dan ulasan telah ditambahkan.');
    }
}
