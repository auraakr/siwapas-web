<?php

namespace Database\Factories;

use App\Domain\Payment\Models\Payment;
use App\Domain\Payment\Models\PaymentProof;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PaymentProof>
 */
class PaymentProofFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payment_id' => Payment::factory(),
            'disk' => 'local',
            'path' => 'payment-proofs/'.Str::random(20).'.jpg',
            'original_name' => 'bukti-transfer.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 204800,
            'sender_name' => fake()->name(),
            'transfer_date' => now()->toDateString(),
            'claimed_amount' => 500123,
            'uploaded_at' => now(),
        ];
    }
}
