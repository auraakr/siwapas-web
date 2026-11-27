<?php

namespace App\Filament\Support;

use App\Domain\Payment\Enums\OrderStatus;

class OrderStatusPresenter
{
    public static function label(OrderStatus $status): string
    {
        return match ($status) {
            OrderStatus::PendingPayment => 'Menunggu pembayaran',
            OrderStatus::AwaitingVerification => 'Menunggu verifikasi',
            OrderStatus::Paid => 'Dibayar',
            OrderStatus::Confirmed => 'Terkonfirmasi',
            OrderStatus::DpPaid => 'DP lunas, terkonfirmasi',
            OrderStatus::AwaitingSettlementVerification => 'Menunggu verifikasi pelunasan',
            OrderStatus::PaidInFull => 'Lunas',
            OrderStatus::Rejected => 'Ditolak',
            OrderStatus::Expired => 'Kedaluwarsa',
            OrderStatus::Cancelled => 'Dibatalkan',
            OrderStatus::Refunded => 'Dikembalikan',
        };
    }

    public static function color(OrderStatus $status): string
    {
        return match ($status) {
            OrderStatus::PendingPayment => 'warning',
            OrderStatus::AwaitingVerification, OrderStatus::AwaitingSettlementVerification => 'info',
            OrderStatus::DpPaid => 'primary',
            OrderStatus::Paid, OrderStatus::Confirmed, OrderStatus::PaidInFull => 'success',
            OrderStatus::Rejected, OrderStatus::Cancelled => 'danger',
            OrderStatus::Expired, OrderStatus::Refunded => 'gray',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(OrderStatus::cases())
            ->mapWithKeys(fn (OrderStatus $status): array => [$status->value => self::label($status)])
            ->all();
    }
}
