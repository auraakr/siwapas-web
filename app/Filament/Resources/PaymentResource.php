<?php

namespace App\Filament\Resources;

use App\Domain\Payment\Actions\RejectPayment;
use App\Domain\Payment\Actions\VerifyPayment;
use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Enums\PaymentType;
use App\Domain\Payment\Exceptions\InvalidOrderTransition;
use App\Domain\Payment\Models\Payment;
use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Support\PaymentPresenter;
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
            ->whereHas('order', fn (Builder $order) => $order->whereIn('status', OrderStatus::awaitingDecision()))
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
                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (PaymentType $state): string => PaymentPresenter::typeLabel($state))
                    ->color(fn (PaymentType $state): string => PaymentPresenter::typeColor($state)),
                Tables\Columns\TextColumn::make('expected_amount')->label('Nominal')->money('IDR', locale: 'id'),
                Tables\Columns\TextColumn::make('method')
                    ->label('Metode')
                    ->formatStateUsing(fn (string $state): string => PaymentPresenter::methodLabel($state)),
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
                Tables\Columns\TextColumn::make('rejection_reason')->label('Alasan ditolak')->placeholder('-')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y H:i', config('siwapas.display_timezone'))->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(['pending' => 'Menunggu', 'verified' => 'Terverifikasi', 'rejected' => 'Ditolak']),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis')
                    ->options(PaymentPresenter::typeOptions()),
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
                    ->modalDescription(fn (Payment $record): string => match ($record->type) {
                        PaymentType::DownPayment => 'DP diterima: booking terkonfirmasi dan slot tetap terkunci. Sisa pelunasan dibayar kemudian.',
                        PaymentType::Settlement => 'Pelunasan diterima: reservasi ditandai lunas.',
                        PaymentType::Full => 'Pesanan akan dikonfirmasi dan slot tetap terkunci.',
                    })
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
                    ->modalDescription(fn (Payment $record): string => $record->type === PaymentType::Settlement
                        ? 'Booking tetap berlaku. Pengunjung dapat mengunggah bukti pelunasan lagi.'
                        : 'Pengunjung dapat mengunggah bukti baru selama batas waktu pembayaran belum lewat. Jika sudah lewat, pesanan kedaluwarsa dan slot dilepas.')
                    ->form(fn (Payment $record): array => [
                        Forms\Components\TextInput::make('reason')
                            ->label('Alasan penolakan (ditampilkan ke pengunjung)')
                            ->required()
                            ->maxLength(500),
                        Forms\Components\Toggle::make('permanent')
                            ->label('Tolak permanen (misalnya bukti palsu): pesanan ditolak dan slot dilepas')
                            ->default(false)
                            ->visible($record->type !== PaymentType::Settlement),
                    ])
                    ->action(function (Payment $record, array $data): void {
                        try {
                            app(RejectPayment::class)->handle($record, auth()->user(), $data['reason'], (bool) ($data['permanent'] ?? false));
                        } catch (InvalidOrderTransition) {
                            Notification::make()->title('Pembayaran ini sudah diproses.')->danger()->send();

                            return;
                        }

                        $message = match ($record->order->refresh()->status) {
                            OrderStatus::PendingPayment => 'Bukti ditolak. Pengunjung dapat mengunggah ulang.',
                            OrderStatus::DpPaid => 'Bukti pelunasan ditolak. Booking tetap berlaku.',
                            OrderStatus::Expired => 'Bukti ditolak. Batas waktu sudah lewat, pesanan kedaluwarsa dan slot dilepas.',
                            default => 'Pembayaran ditolak permanen dan slot dilepas.',
                        };

                        Notification::make()->title($message)->success()->send();
                    }),
            ]);
    }

    /**
     * A down payment or full payment is decided while the order awaits verification; a settlement
     * while it awaits settlement verification.
     */
    private static function awaitingDecision(Payment $payment): bool
    {
        $expected = $payment->type === PaymentType::Settlement
            ? OrderStatus::AwaitingSettlementVerification
            : OrderStatus::AwaitingVerification;

        return $payment->status === PaymentStatus::Pending && $payment->order->status === $expected;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePayments::route('/'),
        ];
    }
}
