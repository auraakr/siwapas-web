<?php

namespace App\Filament\Support;

use App\Domain\Payment\Enums\PaymentType;
use App\Domain\Payment\Enums\SettlementMethod;
use App\Domain\Payment\Models\Payment;

class PaymentPresenter
{
    public static function typeLabel(PaymentType $type): string
    {
        return match ($type) {
            PaymentType::Full => 'Bayar penuh',
            PaymentType::DownPayment => 'DP',
            PaymentType::Settlement => 'Pelunasan',
        };
    }

    public static function typeColor(PaymentType $type): string
    {
        return match ($type) {
            PaymentType::Full => 'gray',
            PaymentType::DownPayment => 'info',
            PaymentType::Settlement => 'primary',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function typeOptions(): array
    {
        return collect(PaymentType::cases())
            ->mapWithKeys(fn (PaymentType $type): array => [$type->value => self::typeLabel($type)])
            ->all();
    }

    public static function methodLabel(string $method): string
    {
        return $method === Payment::METHOD_CASH ? 'Tunai di lokasi' : 'Transfer';
    }

    public static function settlementMethodLabel(?SettlementMethod $method): string
    {
        return match ($method) {
            SettlementMethod::Cash => 'Tunai di lokasi',
            SettlementMethod::Transfer => 'Transfer',
            null => '-',
        };
    }
}
