<?php

namespace App\Filament\Widgets;

use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\Payment;
use App\Filament\Resources\PaymentResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class PaymentsAwaitingVerification extends BaseWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Pembayaran menunggu verifikasi';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Payment::query()
                    ->where('status', PaymentStatus::Pending)
                    ->whereHas('order', fn (Builder $order) => $order->where('status', OrderStatus::AwaitingVerification))
                    ->with('order')
                    ->oldest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('order.code')->label('Pesanan'),
                Tables\Columns\TextColumn::make('order.customer_name')->label('Pemesan'),
                Tables\Columns\TextColumn::make('expected_amount')->label('Nominal')->money('IDR', locale: 'id'),
                Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->since(),
            ])
            ->actions([
                Tables\Actions\Action::make('review')
                    ->label('Periksa')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (): string => PaymentResource::getUrl()),
            ])
            ->paginated([5])
            ->emptyStateHeading('Tidak ada pembayaran yang menunggu')
            ->emptyStateDescription('Semua bukti transfer sudah diperiksa.');
    }
}
