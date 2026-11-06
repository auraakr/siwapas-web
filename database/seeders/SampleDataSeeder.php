<?php

namespace Database\Seeders;

use App\Domain\Catalog\Models\Facility;
use App\Domain\Content\Enums\DestinationType;
use App\Domain\Content\Enums\PublishStatus;
use App\Domain\Content\Models\Destination;
use App\Domain\Payment\Models\BankAccount;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    /**
     * Seed example facilities, bank accounts and destinations for local development.
     */
    public function run(): void
    {
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
}
