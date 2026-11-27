<?php

namespace App\Domain\Payment\Services;

use App\Domain\Content\Models\SiteSetting;

/**
 * Payment settings the managers can change in the admin panel, falling back to config/siwapas.php.
 */
class PaymentSettings
{
    public const DP_PERCENTAGE_KEY = 'reservation_dp_percentage';

    public function downPaymentPercentage(): int
    {
        $stored = SiteSetting::query()->where('key', self::DP_PERCENTAGE_KEY)->value('value');

        $percentage = is_numeric($stored) ? (int) $stored : (int) config('siwapas.payment.dp_percentage');

        return max(1, min(100, $percentage));
    }

    public function setDownPaymentPercentage(int $percentage): void
    {
        SiteSetting::query()->updateOrCreate(
            ['key' => self::DP_PERCENTAGE_KEY],
            ['value' => max(1, min(100, $percentage))],
        );
    }
}
