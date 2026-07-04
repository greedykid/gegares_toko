<?php

namespace App\Livewire\Admin;

use App\Models\StoreSetting;
use Livewire\Component;
use Livewire\WithFileUploads;

class ManageStoreContent extends Component
{
    use WithFileUploads;

    // Active tab
    public $activeTab = 'hero-cta';

    // Hero Fields
    public $hero_badge;
    public $hero_title;
    public $hero_subtitle;

    // CTA Fields
    public $cta_title;
    public $cta_subtitle;

    // FAQ Fields
    public $faq_items = [];

    // About Fields
    public $about_title;
    public $about_subtitle;
    public $about_story_title;
    public $about_story_content;
    public $about_vision;
    public $about_mission = [];
    public $about_gallery = [];
    public $about_gallery_badge;
    public $about_gallery_title;
    public $about_gallery_subtitle;
    public $new_gallery_images = [];

    // Contact Fields
    public $contact_whatsapp;
    public $contact_hours;
    public $contact_phone;
    public $contact_email;

    // Footer payment logos
    public $payment_logos = [];
    public $new_payment_logos = [];

    public function mount()
    {
        $setting = StoreSetting::first();

        // Default FAQ Items
        $defaultFaqs = [
            ['q' => 'Berapa lama waktu pengiriman?', 'a' => 'Kami menggunakan layanan Instan dan Sameday dari Biteship (Gojek/Grab) untuk memastikan jajanan pasar tetap segar saat sampai di tangan Anda. Estimasi sampai adalah 1-4 jam setelah kurir menjemput paket.'],
            ['q' => 'Apakah produk dibuat setiap hari?', 'a' => 'Tentu saja! Seluruh produk Gegares dibuat segar (freshly baked/made) setiap pagi hari sebelum pengiriman dimulai untuk menjamin kualitas dan rasa autentik.'],
            ['q' => 'Bagaimana cara melacak pesanan saya?', 'a' => 'Setelah pesanan Anda diproses oleh admin, Anda akan menerima nomor resi pelacakan. Anda dapat memantau posisi kurir secara real-time langsung melalui halaman "Pesanan Saya" di akun Anda.'],
            ['q' => 'Apakah bisa memesan untuk acara besar (katering)?', 'a' => 'Bisa! Kami melayani pemesanan untuk acara kantor, arisan, atau pesta. Untuk jumlah besar, kami menyarankan pemesanan minimal H-2 melalui WhatsApp agar kami dapat menyiapkan bahan baku terbaik.'],
            ['q' => 'Metode pembayaran apa saja yang tersedia?', 'a' => 'Kami mendukung berbagai metode pembayaran instan melalui Pakasir, termasuk QRIS, E-Wallet (GoPay, OVO, dll), dan Transfer Bank (Virtual Account).']
        ];

        // Default Mission Items
        $defaultMission = [
            'Menggunakan bahan baku segar berkualitas tinggi tanpa pengawet buatan.',
            'Menjaga konsistensi resep tradisional warisan keluarga.',
            'Mendukung ekosistem ekonomi pedagang pasar kecil lokal melalui skema titip jual yang adil.'
        ];

        // Populate fields with database or fallback defaults
        $this->hero_badge = $setting->hero_badge ?? 'Jajanan Pasar Tradisional';
        $this->hero_title = $setting->hero_title ?? 'Rasa Autentik, Langsung ke Rumah';
        $this->hero_subtitle = $setting->hero_subtitle ?? 'Nikmati kelezatan jajanan pasar pilihan yang dibuat segar setiap hari selagi hangat. Dari klepon manis hingga risoles yang gurih renyah.';

        $this->cta_title = $setting->cta_title ?? 'Pesan Sekarang, Nikmati Hari Ini';
        $this->cta_subtitle = $setting->cta_subtitle ?? 'Dibuat fresh, dikirim cepat. Nikmati jajanan pasar favorit Anda tanpa keluar rumah.';

        $this->faq_items = $setting->faq_items ?? $defaultFaqs;

        $this->about_title = $setting->about_title ?? 'Tentang Gegares';
        $this->about_subtitle = $setting->about_subtitle ?? 'Menghadirkan kelezatan jajanan pasar tradisional dengan kualitas premium dan resep autentik rumahan.';
        $this->about_story_title = $setting->about_story_title ?? 'Cita Rasa Warisan';
        $this->about_story_content = $setting->about_story_content ?? "Gegares adalah usaha kuliner rumahan yang didedikasikan untuk melestarikan dan menyajikan jajanan pasar tradisional khas Indonesia dengan standar kualitas terbaik.\n\nKami memproduksi aneka kue basah dan gorengan legendaris seperti pastel renyah, onde-onde wijen gurih, soes mini lembut, molen pisang manis, risol ayam padat, hingga dadar gulung wangi pandan. Seluruh produk kami dibuat secara mandiri setiap dini hari untuk memastikan kesegaran maksimal saat dikirimkan ke para pedagang mitra di pasar tradisional.\n\nSelain menyuplai pedagang lokal melalui sistem titip jual (consignment), kami juga melayani pemesanan skala besar untuk berbagai kebutuhan acara seperti rapat kantor, arisan, pengajian, ulang tahun, hingga paket snack box eksklusif yang dapat dipesan secara mudah dan cepat.";
        $this->about_vision = $setting->about_vision ?? 'Menjadi produsen jajanan tradisional pilihan utama keluarga yang mampu melestarikan cita rasa Nusantara dengan kualitas premium, higienis, dan dapat diakses dengan mudah oleh semua kalangan.';
        $this->about_mission = $setting->about_mission ?? $defaultMission;
        $this->about_gallery = $setting->about_gallery ?? [];
        $this->about_gallery_badge = $setting->about_gallery_badge ?? 'Galeri Kegiatan';
        $this->about_gallery_title = $setting->about_gallery_title ?? 'Proses Produksi Kami';
        $this->about_gallery_subtitle = $setting->about_gallery_subtitle ?? 'Melihat langsung bagaimana jajanan pasar legendaris kami dibuat secara higienis setiap dini hari.';

        $this->contact_whatsapp = $setting->contact_whatsapp ?? '6281234567890';
        $this->contact_hours = $setting->contact_hours ?? "Setiap Hari: 06:00 - 17:00 WIB\nPemesanan WhatsApp: 24 Jam";
        $this->contact_phone = $setting->contact_phone ?? '+62 812-3456-7890';
        $this->contact_email = $setting->contact_email ?? 'hello@gegares.com';

        $this->payment_logos = $setting->payment_logos ?? [];
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    // FAQ dynamic list operations
    public function addFaq()
    {
        $this->faq_items[] = ['q' => '', 'a' => ''];
    }

    public function removeFaq($index)
    {
        unset($this->faq_items[$index]);
        $this->faq_items = array_values($this->faq_items);
    }

    // Mission dynamic list operations
    public function addMission()
    {
        $this->about_mission[] = '';
    }

    public function removeMission($index)
    {
        unset($this->about_mission[$index]);
        $this->about_mission = array_values($this->about_mission);
    }

    // Gallery operations
    public function removeGalleryImage($index)
    {
        if (isset($this->about_gallery[$index])) {
            $path = $this->about_gallery[$index];
            // Optionally delete file from storage if it exists
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
            }
            unset($this->about_gallery[$index]);
            $this->about_gallery = array_values($this->about_gallery);
        }
    }

    public function removePaymentLogo($index)
    {
        if (isset($this->payment_logos[$index])) {
            $path = $this->payment_logos[$index];
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
            }
            unset($this->payment_logos[$index]);
            $this->payment_logos = array_values($this->payment_logos);
        }
    }

    public function save()
    {
        // Validation rules
        $this->validate([
            'hero_badge' => 'required|string|max:255',
            'hero_title' => 'required|string|max:255',
            'hero_subtitle' => 'required|string',
            'cta_title' => 'required|string|max:255',
            'cta_subtitle' => 'required|string',
            'faq_items.*.q' => 'required|string|max:255',
            'faq_items.*.a' => 'required|string',
            'about_title' => 'required|string|max:255',
            'about_subtitle' => 'required|string',
            'about_story_title' => 'required|string|max:255',
            'about_story_content' => 'required|string',
            'about_vision' => 'required|string',
            'about_mission.*' => 'required|string',
            'about_gallery_badge' => 'required|string|max:255',
            'about_gallery_title' => 'required|string|max:255',
            'about_gallery_subtitle' => 'required|string',
            'contact_whatsapp' => 'required|string|max:20',
            'contact_hours' => 'required|string',
            'contact_phone' => 'required|string|max:20',
            'contact_email' => 'required|email|max:255',
            'new_gallery_images.*' => 'nullable|image|max:2048', // 2MB Max
            'new_payment_logos.*' => 'nullable|image|max:1024', // 1MB Max
        ], [
            'faq_items.*.q.required' => 'Pertanyaan tidak boleh kosong.',
            'faq_items.*.a.required' => 'Jawaban tidak boleh kosong.',
            'about_mission.*.required' => 'Butir misi tidak boleh kosong.',
            'new_gallery_images.*.image' => 'File harus berupa gambar.',
            'new_gallery_images.*.max' => 'Ukuran gambar maksimal adalah 2MB.',
            'new_payment_logos.*.image' => 'File harus berupa gambar.',
            'new_payment_logos.*.max' => 'Ukuran logo maksimal adalah 1MB.',
        ]);

        // Process new gallery images
        if (!empty($this->new_gallery_images)) {
            foreach ($this->new_gallery_images as $image) {
                $path = $image->store('settings/gallery', 'public');
                $this->about_gallery[] = $path;
            }
            // Clear upload temp state
            $this->new_gallery_images = [];
        }

        // Process new payment method logos
        if (!empty($this->new_payment_logos)) {
            foreach ($this->new_payment_logos as $logo) {
                $path = $logo->store('settings/payment_logos', 'public');
                $this->payment_logos[] = $path;
            }
            $this->new_payment_logos = [];
        }

        // Persist setting record
        $setting = StoreSetting::firstOrNew([]);
        $setting->hero_badge = $this->hero_badge;
        $setting->hero_title = $this->hero_title;
        $setting->hero_subtitle = $this->hero_subtitle;
        $setting->cta_title = $this->cta_title;
        $setting->cta_subtitle = $this->cta_subtitle;
        $setting->faq_items = $this->faq_items;
        $setting->about_title = $this->about_title;
        $setting->about_subtitle = $this->about_subtitle;
        $setting->about_story_title = $this->about_story_title;
        $setting->about_story_content = $this->about_story_content;
        $setting->about_vision = $this->about_vision;
        $setting->about_mission = $this->about_mission;
        $setting->about_gallery = $this->about_gallery;
        $setting->about_gallery_badge = $this->about_gallery_badge;
        $setting->about_gallery_title = $this->about_gallery_title;
        $setting->about_gallery_subtitle = $this->about_gallery_subtitle;
        $setting->contact_whatsapp = $this->contact_whatsapp;
        $setting->contact_hours = $this->contact_hours;
        $setting->contact_phone = $this->contact_phone;
        $setting->contact_email = $this->contact_email;
        $setting->payment_logos = $this->payment_logos;
        $setting->save();

        $this->dispatch('toast', message: 'Pengaturan konten berhasil disimpan.', type: 'success');
    }

    public function render()
    {
        return view('livewire.admin.manage-store-content');
    }
}
