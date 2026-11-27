<?php

namespace Database\Seeders;

use App\Domain\Catalog\Enums\RentalUnit;
use App\Domain\Catalog\Models\Facility;
use Database\Seeders\Concerns\GuardsSampleData;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    use GuardsSampleData;

    /**
     * Seed three example rentable facilities for local development. Safe to re-run: rows are matched by slug.
     */
    public function run(): void
    {
        if ($this->sampleDataBlocked()) {
            return;
        }

        Facility::updateOrCreate(['slug' => 'aula-desa'], [
            'name' => ['id' => 'Aula Desa', 'en' => 'Village Hall'],
            'description' => [
                'id' => 'Aula serbaguna untuk acara, pertemuan, dan kegiatan warga.',
                'en' => 'Multipurpose hall for events, meetings, and community activities.',
            ],
            'capacity' => 150,
            'unit' => RentalUnit::Day,
            'base_price' => 1500000,
            'rules' => [
                'id' => ['Dilarang merokok di dalam aula', 'Jaga kebersihan dan fasilitas', 'Selesai maksimal pukul 22.00 WIB'],
                'en' => ['No smoking inside the hall', 'Keep the venue and facilities clean', 'Events must end by 10 PM'],
            ],
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Facility::updateOrCreate(['slug' => 'saung-tepi-sungai'], [
            'name' => ['id' => 'Saung Tepi Sungai', 'en' => 'Riverside Gazebo'],
            'description' => [
                'id' => 'Saung santai di tepi Sungai Citarum, cocok untuk keluarga dan kelompok kecil.',
                'en' => 'Relaxed gazebo on the Citarum riverbank, suited to families and small groups.',
            ],
            'capacity' => 20,
            'unit' => RentalUnit::Day,
            'base_price' => 350000,
            'rules' => [
                'id' => ['Bawa kembali sampah Anda', 'Anak-anak harus didampingi di dekat sungai'],
                'en' => ['Take your trash with you', 'Children must be supervised near the river'],
            ],
            'is_active' => true,
            'sort_order' => 2,
        ]);

        Facility::updateOrCreate(['slug' => 'area-camping'], [
            'name' => ['id' => 'Area Camping', 'en' => 'Camping Ground'],
            'description' => [
                'id' => 'Lahan berkemah terbuka dengan pemandangan lembah, cocok untuk keluarga dan komunitas.',
                'en' => 'Open camping ground with a valley view, suited to families and communities.',
            ],
            'capacity' => 60,
            'unit' => RentalUnit::Day,
            'base_price' => 750000,
            'rules' => [
                'id' => ['Dilarang membuat api unggun di luar area yang ditentukan', 'Bawa kembali sampah Anda', 'Jaga ketenangan setelah pukul 22.00 WIB'],
                'en' => ['No campfires outside the designated area', 'Take your trash with you', 'Keep quiet after 10 PM'],
            ],
            'is_active' => true,
            'sort_order' => 3,
        ]);
    }
}
