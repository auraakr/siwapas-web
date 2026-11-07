<?php

namespace App\Filament\Widgets;

use App\Domain\Booking\Models\Reservation;
use App\Domain\Event\Enums\EventStatus;
use App\Domain\Event\Models\EventRegistration;
use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\Order;
use App\Domain\Payment\Models\Payment;
use App\Filament\Resources\PaymentResource;
use App\Filament\Resources\ReservationResource;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class OperationsOverview extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = now(config('siwapas.display_timezone'))->toDateString();

        $awaitingVerification = Payment::query()
            ->where('status', PaymentStatus::Pending)
            ->whereHas('order', fn (Builder $order) => $order->where('status', OrderStatus::AwaitingVerification))
            ->count();

        $unpaid = Order::query()->where('status', OrderStatus::PendingPayment)->count();

        $upcomingReservations = Reservation::query()
            ->where('is_active', true)
            ->whereDate('check_out', '>', $today)
            ->whereHas('order', fn (Builder $order) => $order->where('status', OrderStatus::Confirmed))
            ->count();

        $eventParticipants = (int) EventRegistration::query()
            ->where('is_active', true)
            ->whereHas('event', fn (Builder $event) => $event
                ->where('status', EventStatus::Published)
                ->where('starts_at', '>=', now()))
            ->sum('participant_count');

        return [
            Stat::make('Menunggu verifikasi', $awaitingVerification)
                ->description('Bukti transfer perlu dicek')
                ->color($awaitingVerification > 0 ? 'warning' : 'success')
                ->url(PaymentResource::getUrl()),
            Stat::make('Menunggu pembayaran', $unpaid)
                ->description('Belum ada bukti transfer')
                ->color('gray'),
            Stat::make('Reservasi mendatang', $upcomingReservations)
                ->description('Terkonfirmasi, belum selesai')
                ->color('success')
                ->url(ReservationResource::getUrl()),
            Stat::make('Peserta acara mendatang', $eventParticipants)
                ->description('Pendaftar aktif pada acara terbit')
                ->color('info'),
        ];
    }
}
