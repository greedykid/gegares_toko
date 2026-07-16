<?php

namespace Database\Seeders;

use App\Models\StoreSetting;
use Illuminate\Database\Seeder;

/**
 * Seeds the single store_settings row that backs "Pengaturan Toko" and
 * "Lokasi Toko" in the admin.
 *
 * area_id and biteship_location_id are intentionally left out: they are issued
 * by Biteship, not chosen by us. The admin fills them by picking the area in
 * Lokasi Toko, which calls selectArea() with the real id. Inventing a value
 * here would silently break shipping-rate lookups.
 */
class StoreSettingSeeder extends Seeder
{
    public function run(): void
    {
        StoreSetting::updateOrCreate(
            ['id' => 1],
            [
                // ── Identitas & kontak ──
                'store_name' => 'Gegares',
                'contact_phone' => '+62 821-2894-64576',
                'contact_email' => 'admin@gegares.shop',
                'contact_whatsapp' => '62821289464576',

                // ── Lokasi toko (titik asal pengiriman) ──
                'address_line' => 'Jalan Haji Marjuki No.5, RT 10 RW 03, Kebon Jeruk, Jakarta Barat, Daerah Khusus Ibukota Jakarta, Jawa, 11530, Indonesia',
                'city' => 'Jakarta Barat',
                'province' => 'DKI Jakarta',
                'postal_code' => '11530',
                'latitude' => -6.1941978,
                'longitude' => 106.7736664,

                // ── Narasi kisah (halaman Tentang) ──
                'about_story_content' => 'Gegares adalah usaha kuliner rumahan yang didedikasikan untuk melestarikan dan menyajikan jajanan pasar tradisional khas Indonesia dengan standar kualitas terbaik. Kami memproduksi aneka kue basah dan gorengan legendaris seperti pastel renyah, onde-onde wijen gurih, soes mini lembut, molen pisang manis, risol ayam padat, hingga dadar gulung wangi pandan. Seluruh produk kami dibuat secara mandiri setiap dini hari untuk memastikan kesegaran maksimal saat dikirimkan ke para pedagang mitra di pasar tradisional. Selain menyuplai pedagang lokal melalui sistem titip jual (consignment), kami juga melayani pemesanan skala besar untuk berbagai kebutuhan acara seperti rapat kantor, arisan, pengajian, ulang tahun, hingga paket snack box eksklusif yang dapat dipesan secara mudah dan cepat.',
            ]
        );
    }
}
