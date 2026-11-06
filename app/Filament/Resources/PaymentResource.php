<?php

namespace App\Filament\Resources;

use App\Domain\Payment\Actions\RejectPayment;
use App\Domain\Payment\Actions\VerifyPayment;
use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Exceptions\InvalidOrderTransition;
use App\Domain\Payment\Models\Payment;
use App\Filament\Resources\PaymentResource\Pages;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Payments are never created, edited or deleted by hand: they only move through
 * the verify and reject actions so the financial history stays intact.
 */
class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Pembayaran';

    protected static ?string $modelLabel = 'pembayaran';

    protected static ?string $pluralModelLabel = 'pembayaran';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Payment::query()
            ->where('status', PaymentStatus::Pending)
            ->whereHas('order', fn (Builder $order) => $order->where('status', OrderStatus::AwaitingVerification))
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['order', 'proofs']))
            ->columns([
                Tables\Columns\TextColumn::make('order.code')->label('Pesanan')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('order.customer_name')->label('Pemesan')->searchable(),
                Tables\Columns\TextColumn::make('expected_amount')->label('Nominal')->money('IDR', locale: 'id'),
                Tables\Columns\TextColumn::make('proofs_count')->label('Bukti')->counts('proofs'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (PaymentStatus $state): string => match ($state) {
                        PaymentStatus::Pending => 'Menunggu',
                        PaymentStatus::Verified => 'Terverifikasi',
                        PaymentStatus::Rejected => 'Ditolak',
                    })
                    ->color(fn (PaymentStatus $state): string => match ($state) {
                        PaymentStatus::Pending => 'warning',
                        PaymentStatus::Verified => 'success',
                        PaymentStatus::Rejected => 'danger',
                    }),
                Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y H:i', config('siwapas.display_timezone'))->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(['pending' => 'Menunggu', 'verified' => 'Terverifikasi', 'rejected' => 'Ditolak']),
            ])
            ->actions([
                Tables\Actions\Action::make('proofs')
                    ->label('Lihat bukti')
                    ->icon('heroicon-o-photo')
                    ->modalHeading('Bukti transfer')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(fn (Payment $record) => view('filament.payment-proofs', ['payment' => $record])),
                Tables\Actions\Action::make('verify')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Payment $record): bool => self::awaitingDecision($record))
                    ->requiresConfirmation()
                    ->modalDescription('Pesanan akan dikonfirmasi dan slot tetap terkunci.')
                    ->action(function (Payment $record): void {
                        try {
                            app(VerifyPayment::class)->handle($record, auth()->user());
                        } catch (InvalidOrderTransition) {
                            Notification::make()->title('Pembayaran ini sudah diproses.')->danger()->send();

                            return;
                        }

                        Notification::make()->title('Pembayaran diverifikasi.')->success()->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Payment $record): bool => self::awaitingDecision($record))
                    ->form([
                        Forms\Components\TextInput::make('reason')->label('Alasan penolakan')->required()->maxLength(500),
                    ])
                    ->action(function (Payment $record, array $data): void {
                        try {
                            app(RejectPayment::class)->handle($record, auth()->user(), $data['reason']);
                        } catch (InvalidOrderTransition) {
                            Notification::make()->title('Pembayaran ini sudah diproses.')->danger()->send();

                            return;
                        }

                        Notification::make()->title('Pembayaran ditolak dan slot dilepas.')->success()->send();
                    }),
            ]);
    }

    private static function awaitingDecision(Payment $payment): bool
    {
        return $payment->status === PaymentStatus::Pending
            && $payment->order->status === OrderStatus::AwaitingVerification;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePayments::route('/'),
        ];
    }
}
