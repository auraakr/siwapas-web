<?php

namespace Database\Seeders;

use App\Domain\Catalog\Models\Facility;
use App\Domain\Content\Enums\ArticleCategory;
use App\Domain\Content\Enums\DestinationType;
use App\Domain\Content\Enums\PublishStatus;
use App\Domain\Content\Models\Article;
use App\Domain\Content\Models\Destination;
use App\Domain\Content\Models\Faq;
use App\Domain\Content\Models\LocalProduct;
use App\Domain\Event\Enums\EventStatus;
use App\Domain\Event\Models\Event;
use App\Domain\Payment\Enums\PaymentMethodType;
use App\Domain\Payment\Models\BankAccount;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    /**
     * Seed example facilities, bank accounts and content for local development.
     * Every sample title is marked "Contoh" so it is never mistaken for real village data.
     */
    public function run(): void
    {
        $this->seedLandingContent();

        Facility::updateOrCreate(['slug' => 'aula-desa'], [
            'name' => ['id' => 'Aula Desa', 'en' => 'Village Hall'],
            'description' => ['id' => 'Aula serbaguna untuk acara dan pertemuan.', 'en' => 'Multipurpose hall for events and meetings.'],
            'capacity' => 150,
            'unit' => 'day',
            'base_price' => 1500000,
            'is_active' => true,
        ]);

        Facility::updateOrCreate(['slug' => 'saung-tepi-sungai'], [
            'name' => ['id' => 'Saung Tepi Sungai', 'en' => 'Riverside Gazebo'],
            'description' => ['id' => 'Saung santai di tepi Sungai Citarum.', 'en' => 'Relaxed gazebo on the Citarum riverbank.'],
            'capacity' => 20,
            'unit' => 'day',
            'base_price' => 350000,
            'is_active' => true,
        ]);

        BankAccount::updateOrCreate(['account_number' => '1234567890'], [
            'type' => PaymentMethodType::Bank,
            'bank_name' => 'BCA',
            'account_holder' => 'Desa Wisata Pasirnanjung Geulis',
            'is_active' => true,
        ]);

        Destination::updateOrCreate(['slug' => 'pasirnanjung-geulis'], [
            'type' => DestinationType::Destination,
            'name' => ['id' => 'Pasirnanjung Geulis', 'en' => 'Pasirnanjung Geulis'],
            'summary' => ['id' => 'Desa wisata di tepi Sungai Citarum.', 'en' => 'A tourist village on the Citarum riverbank.'],
            'status' => PublishStatus::Published,
            'is_featured' => true,
        ]);
    }

    private function seedLandingContent(): void
    {
        foreach (['Wisata Alam', 'Ruang Budaya', 'Titik Pandang', 'Taman Desa'] as $position => $name) {
            Destination::updateOrCreate(['slug' => 'contoh-destinasi-'.($position + 1)], [
                'type' => DestinationType::Destination,
                'name' => ['id' => "{$name} (Contoh)", 'en' => "{$name} (Sample)"],
                'summary' => ['id' => 'Deskripsi singkat contoh. Ganti dengan data asli dari panel admin.'],
                'address' => 'Alamat contoh',
                'status' => PublishStatus::Published,
                'is_featured' => $position === 0,
                'sort_order' => $position,
            ]);
        }

        foreach (['Susur Desa', 'Workshop Kerajinan', 'Kuliner Warga'] as $position => $name) {
            Destination::updateOrCreate(['slug' => 'contoh-aktivitas-'.($position + 1)], [
                'type' => DestinationType::Activity,
                'name' => ['id' => "{$name} (Contoh)", 'en' => "{$name} (Sample)"],
                'summary' => ['id' => 'Kegiatan contoh bersama warga. Ganti dengan data asli dari panel admin.'],
                'address' => 'Lokasi contoh',
                'requires_reservation' => $position !== 2,
                'status' => PublishStatus::Published,
                'sort_order' => $position,
            ]);
        }

        foreach (['Kopi Desa', 'Kerajinan Bambu', 'Keripik Singkong'] as $position => $name) {
            LocalProduct::query()->updateOrCreate(['maker_name' => "UMKM Contoh {$position}"], [
                'name' => ['id' => "{$name} (Contoh)", 'en' => "{$name} (Sample)"],
                'description' => ['id' => 'Produk contoh. Ganti dengan produk asli dari panel admin.'],
                'sort_order' => $position,
                'is_published' => true,
            ]);
        }

        foreach ([7, 21, 35] as $position => $days) {
            Event::updateOrCreate(['slug' => 'contoh-acara-'.($position + 1)], [
                'title' => ['id' => 'Acara Desa (Contoh) '.($position + 1), 'en' => 'Village Event (Sample) '.($position + 1)],
                'description' => ['id' => 'Acara contoh. Ganti dengan agenda asli dari panel admin.'],
                'location_name' => 'Lokasi contoh',
                'starts_at' => now()->addDays($days)->setTime(9, 0),
                'ends_at' => now()->addDays($days)->setTime(13, 0),
                'fee' => $position === 0 ? 0 : 25000,
                'quota' => 50,
                'status' => EventStatus::Published,
            ]);
        }

        foreach (['Panen Bersama', 'Kisah Warga', 'Tradisi Desa'] as $position => $title) {
            Article::updateOrCreate(['slug' => 'contoh-cerita-'.($position + 1)], [
                'title' => ['id' => "{$title} (Contoh)", 'en' => "{$title} (Sample)"],
                'excerpt' => ['id' => 'Ringkasan cerita contoh. Ganti dengan cerita asli dari panel admin.'],
                'category' => [ArticleCategory::Community, ArticleCategory::VillagePotential, ArticleCategory::Culture][$position],
                'author_name' => 'Tim Desa',
                'published_at' => now()->subDays($position + 1),
                'status' => PublishStatus::Published,
            ]);
        }

        $questions = [
            'Apakah perlu membuat akun untuk memesan?' => 'Tidak perlu. Kamu cukup mengisi data pemesan, lalu menyimpan tautan pesanan yang diberikan.',
            'Bagaimana cara membayar?' => 'Transfer ke rekening atau QRIS desa, lalu unggah bukti transfer di halaman pesanan. Pengelola akan memverifikasinya.',
            'Berapa lama pesanan ditahan?' => 'Pesanan ditahan 24 jam. Jika belum ada pembayaran, pesanan otomatis kedaluwarsa.',
        ];

        $order = 0;

        foreach ($questions as $question => $answer) {
            Faq::query()->updateOrCreate(['sort_order' => $order], [
                'question' => ['id' => $question],
                'answer' => ['id' => $answer],
                'is_published' => true,
            ]);
            $order++;
        }
    }
}
