<?php

namespace Database\Seeders;

use App\Domain\Content\Models\LocalProduct;
use Illuminate\Database\Seeder;

class SampleProductSeeder extends Seeder
{
    /**
     * Seed example UMKM products for demos. Every maker is marked "Contoh" so the products are never
     * mistaken for real village businesses. No WhatsApp number is set, so the order button falls back
     * to the village contact number instead of messaging a made-up number that may belong to someone.
     * Safe to run repeatedly: products are matched on their maker name.
     */
    public function run(): void
    {
        $products = [
            [
                'maker' => 'Dapur Bu Imas (UMKM Contoh)',
                'name' => ['id' => 'Opak Ketan Bakar', 'en' => 'Grilled Sticky Rice Crackers'],
                'description' => [
                    'id' => "Opak renyah dari beras ketan pilihan, dipanggang di atas bara seperti cara nenek moyang.\nGurih, tanpa pengawet, cocok untuk oleh-oleh keluarga.",
                    'en' => "Crispy crackers made from selected sticky rice, grilled over embers the traditional way.\nSavoury, preservative free, and a perfect gift to take home.",
                ],
            ],
            [
                'maker' => 'Kopi Saung Geulis (UMKM Contoh)',
                'name' => ['id' => 'Kopi Robusta Bubuk', 'en' => 'Ground Robusta Coffee'],
                'description' => [
                    'id' => "Biji kopi robusta dari kebun warga, disangrai dalam jumlah kecil agar aromanya tetap terjaga.\nTersedia kemasan 100 g dan 250 g.",
                    'en' => "Robusta beans from villagers' gardens, roasted in small batches to keep the aroma.\nAvailable in 100 g and 250 g packs.",
                ],
            ],
            [
                'maker' => 'Bambu Rancage (UMKM Contoh)',
                'name' => ['id' => 'Anyaman Bambu', 'en' => 'Woven Bamboo Crafts'],
                'description' => [
                    'id' => "Tas, tampah, dan wadah serbaguna yang dianyam tangan oleh pengrajin desa.\nSetiap anyaman unik dan bisa dipesan sesuai ukuran.",
                    'en' => "Bags, trays and storage baskets hand-woven by village artisans.\nEvery piece is unique and can be made to order.",
                ],
            ],
            [
                'maker' => 'Rasa Pasirnanjung (UMKM Contoh)',
                'name' => ['id' => 'Dodol Peuyeum', 'en' => 'Fermented Cassava Dodol'],
                'description' => [
                    'id' => "Dodol legit berbahan peuyeum singkong khas Sunda, dimasak perlahan berjam-jam.\nManis pas dan tahan hingga dua minggu.",
                    'en' => "A chewy Sundanese sweet made from fermented cassava, slow-cooked for hours.\nPleasantly sweet and keeps for up to two weeks.",
                ],
            ],
            [
                'maker' => 'Sanggar Warna Desa (UMKM Contoh)',
                'name' => ['id' => 'Kain Batik Ciprat', 'en' => 'Ciprat Batik Cloth'],
                'description' => [
                    'id' => "Batik dengan teknik ciprat yang dikerjakan bersama warga, warnanya cerah dan motifnya tidak ada yang sama.\nUkuran 2 x 1,15 meter.",
                    'en' => "Batik made with the splash technique together with villagers, in bright colours with no two patterns alike.\nSize 2 x 1.15 metres.",
                ],
            ],
            [
                'maker' => 'Saung Camilan (UMKM Contoh)',
                'name' => ['id' => 'Keripik Pisang Aneka Rasa', 'en' => 'Flavoured Banana Chips'],
                'description' => [
                    'id' => "Keripik pisang tipis dari pisang kebun desa, tersedia rasa original, cokelat, dan balado.\nKemasan 200 g.",
                    'en' => "Thin banana chips from village-grown bananas, in original, chocolate and spicy balado flavours.\n200 g pack.",
                ],
            ],
        ];

        foreach ($products as $position => $product) {
            LocalProduct::query()->updateOrCreate(['maker_name' => $product['maker']], [
                'name' => $product['name'],
                'description' => $product['description'],
                'sort_order' => $position,
                'is_published' => true,
            ]);
        }
    }
}
