<?php

namespace App\Filament\Resources;

use App\Domain\Booking\Models\Reservation;
use App\Domain\Payment\Actions\CancelOrder;
use App\Domain\Payment\Actions\MarkOrderPaidInFull;
use App\Domain\Payment\Enums\CancellationParty;
use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Enums\PaymentType;
use App\Domain\Payment\Enums\SettlementMethod;
use App\Domain\Payment\Exceptions\InvalidOrderTransition;
use App\Domain\Payment\Models\Order;
use App\Filament\Resources\ReservationResource\Pages;
use App\Filament\Support\OrderStatusPresenter;
use App\Filament\Support\PaymentPresenter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Booking / Reservasi';

    protected static ?string $modelLabel = 'reservasi';

    protected static ?string $pluralModelLabel = 'reservasi';

    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make()
                ->relationship('order')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('customer_name')->label('Nama pemesan')->required()->maxLength(255),
                    Forms\Components\TextInput::make('customer_phone')
                        ->label('Nomor telepon')
                        ->required()
                        ->regex('/^(\+62|62|0)8[0-9]{7,12}$/')
                        ->validationMessages(['regex' => 'Gunakan format 08xxxxxxxxxx.']),
                    Forms\Components\TextInput::make('customer_email')->label('Email')->email()->maxLength(255),
                    Forms\Components\Textarea::make('notes')->label('Catatan')->maxLength(1000)->columnSpanFull(),
                ]),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Reservasi')->columns(3)->schema([
                Infolists\Components\TextEntry::make('order.code')->label('Kode'),
                Infolists\Components\TextEntry::make('order.status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (OrderStatus $state): string => OrderStatusPresenter::label($state))
                    ->color(fn (OrderStatus $state): string => OrderStatusPresenter::color($state)),
                Infolists\Components\TextEntry::make('facility.name')->label('Fasilitas'),
                Infolists\Components\TextEntry::make('check_in')->label('Check-in')->date('d M Y'),
                Infolists\Components\TextEntry::make('check_out')->label('Check-out')->date('d M Y'),
                Infolists\Components\TextEntry::make('guests')->label('Jumlah tamu'),
                Infolists\Components\TextEntry::make('order.confirmed_at')->label('Dikonfirmasi')->dateTime('d M Y H:i', config('siwapas.display_timezone'))->placeholder('-'),
                Infolists\Components\TextEntry::make('order.expires_at')->label('Batas bayar DP')->dateTime('d M Y H:i', config('siwapas.display_timezone'))->placeholder('-'),
            ]),
            Infolists\Components\Section::make('Pembayaran')->columns(3)->schema([
                Infolists\Components\TextEntry::make('order.subtotal')->label('Harga lunas')->money('IDR', locale: 'id'),
                Infolists\Components\TextEntry::make('order.dp_amount')
                    ->label(fn (Reservation $record): string => 'DP '.($record->order->dp_percentage ?? 100).'%')
                    ->money('IDR', locale: 'id')
                    ->placeholder('Bayar penuh'),
                Infolists\Components\TextEntry::make('order.remaining_amount')->label('Sisa pelunasan')->money('IDR', locale: 'id')->placeholder('-'),
                Infolists\Components\TextEntry::make('order.unique_code')->label('Kode unik (ditambahkan ke DP)'),
                Infolists\Components\TextEntry::make('settlement')
                    ->label('Status pelunasan')
                    ->state(fn (Reservation $record): string => self::settlementLabel($record->order))
                    ->badge()
                    ->color(fn (Reservation $record): string => $record->order->status === OrderStatus::PaidInFull ? 'success' : 'warning'),
                Infolists\Components\TextEntry::make('order.settled_at')->label('Dilunasi')->dateTime('d M Y H:i', config('siwapas.display_timezone'))->placeholder('-'),
            ]),
            Infolists\Components\Section::make('Pembatalan')
                ->columns(2)
                ->visible(fn (Reservation $record): bool => $record->order->status === OrderStatus::Cancelled)
                ->schema([
                    Infolists\Components\TextEntry::make('order.cancelled_by')
                        ->label('Dibatalkan oleh')
                        ->formatStateUsing(fn (?CancellationParty $state): string => self::cancellationLabel($state)),
                    Infolists\Components\TextEntry::make('order.resolution_note')->label('Catatan penyelesaian')->placeholder('-'),
                ]),
            Infolists\Components\Section::make('Pemesan')->columns(3)->schema([
                Infolists\Components\TextEntry::make('order.customer_name')->label('Nama'),
                Infolists\Components\TextEntry::make('order.customer_phone')->label('Telepon'),
                Infolists\Components\TextEntry::make('order.customer_email')->label('Email')->placeholder('-'),
                Infolists\Components\TextEntry::make('order.notes')->label('Catatan')->placeholder('-')->columnSpanFull(),
            ]),
            Infolists\Components\Section::make('Rincian harga per malam')->schema([
                Infolists\Components\RepeatableEntry::make('price_breakdown')->label('')->columns(3)->schema([
                    Infolists\Components\TextEntry::make('date')->label('Tanggal'),
                    Infolists\Components\TextEntry::make('price')->label('Harga')->money('IDR', locale: 'id'),
                    Infolists\Components\TextEntry::make('rule')->label('Aturan')->placeholder('Harga dasar'),
                ]),
            ]),
            Infolists\Components\Section::make('Riwayat pembayaran')->schema([
                Infolists\Components\RepeatableEntry::make('order.payments')->label('')->columns(5)->schema([
                    Infolists\Components\TextEntry::make('type')
                        ->label('Jenis')
                        ->badge()
                        ->formatStateUsing(fn (PaymentType $state): string => PaymentPresenter::typeLabel($state))
                        ->color(fn (PaymentType $state): string => PaymentPresenter::typeColor($state)),
                    Infolists\Components\TextEntry::make('expected_amount')->label('Nominal')->money('IDR', locale: 'id'),
                    Infolists\Components\TextEntry::make('method')->label('Metode')->formatStateUsing(fn (string $state): string => PaymentPresenter::methodLabel($state)),
                    Infolists\Components\TextEntry::make('status')
                        ->label('Status')
                        ->formatStateUsing(fn (PaymentStatus $state): string => match ($state) {
                            PaymentStatus::Pending => 'Menunggu',
                            PaymentStatus::Verified => 'Terverifikasi',
                            PaymentStatus::Rejected => 'Ditolak',
                        }),
                    Infolists\Components\TextEntry::make('rejection_reason')->label('Alasan ditolak')->placeholder('-'),
                ]),
            ]),
            Infolists\Components\Section::make('Riwayat status')->schema([
                Infolists\Components\RepeatableEntry::make('order.statusHistories')->label('')->columns(4)->schema([
                    Infolists\Components\TextEntry::make('created_at')->label('Waktu')->dateTime('d M Y H:i', config('siwapas.display_timezone')),
                    Infolists\Components\TextEntry::make('to_status')
                        ->label('Status')
                        ->formatStateUsing(fn (OrderStatus $state): string => OrderStatusPresenter::label($state)),
                    Infolists\Components\TextEntry::make('actor_type')->label('Oleh'),
                    Infolists\Components\TextEntry::make('note')->label('Catatan')->placeholder('-'),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['order', 'facility']))
            ->columns([
                Tables\Columns\TextColumn::make('order.code')->label('Kode')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('order.customer_name')->label('Pemesan')->searchable(),
                Tables\Columns\TextColumn::make('facility.name')->label('Fasilitas'),
                Tables\Columns\TextColumn::make('check_in')->label('Check-in')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('check_out')->label('Check-out')->date('d M Y'),
                Tables\Columns\TextColumn::make('order.subtotal')->label('Harga lunas')->money('IDR', locale: 'id'),
                Tables\Columns\TextColumn::make('order.dp_amount')->label('DP')->money('IDR', locale: 'id')->placeholder('Penuh'),
                Tables\Columns\TextColumn::make('order.remaining_amount')->label('Sisa')->money('IDR', locale: 'id')->placeholder('-'),
                Tables\Columns\TextColumn::make('order.status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (OrderStatus $state): string => OrderStatusPresenter::label($state))
                    ->color(fn (OrderStatus $state): string => OrderStatusPresenter::color($state)),
                Tables\Columns\TextColumn::make('settlement')
                    ->label('Pelunasan')
                    ->state(fn (Reservation $record): string => self::settlementLabel($record->order))
                    ->toggleable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(OrderStatusPresenter::options())
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'] ?? null,
                        fn (Builder $query, string $status) => $query->whereHas('order', fn (Builder $order) => $order->where('status', $status)),
                    )),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()->label('Ubah data pemesan'),
                Tables\Actions\Action::make('markPaidInFull')
                    ->label('Tandai Lunas')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (Reservation $record): bool => $record->order->requiresDownPayment()
                        && $record->order->status->canTransitionTo(OrderStatus::PaidInFull))
                    ->modalHeading('Tandai reservasi lunas')
                    ->modalDescription(fn (Reservation $record): string => 'Sisa pelunasan: Rp '.number_format((int) $record->order->remaining_amount, 0, ',', '.'))
                    ->form([
                        Forms\Components\Radio::make('method')
                            ->label('Metode pelunasan')
                            ->options([
                                SettlementMethod::Cash->value => 'Tunai di lokasi',
                                SettlementMethod::Transfer->value => 'Transfer (sudah dicek di rekening)',
                            ])
                            ->default(SettlementMethod::Cash->value)
                            ->required(),
                        Forms\Components\TextInput::make('note')->label('Catatan (opsional)')->maxLength(500),
                    ])
                    ->action(function (Reservation $record, array $data): void {
                        try {
                            app(MarkOrderPaidInFull::class)->handle(
                                $record->order,
                                auth()->user(),
                                SettlementMethod::from($data['method']),
                                $data['note'] ?? null,
                            );
                        } catch (InvalidOrderTransition) {
                            Notification::make()->title('Reservasi ini tidak dapat ditandai lunas.')->danger()->send();

                            return;
                        }

                        Notification::make()->title('Reservasi ditandai lunas.')->success()->send();
                    }),
                Tables\Actions\Action::make('cancel')
                    ->label('Batalkan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Reservation $record): bool => $record->order->status->canTransitionTo(OrderStatus::Cancelled))
                    ->form([
                        Forms\Components\Radio::make('party')
                            ->label('Dibatalkan oleh')
                            ->options([
                                CancellationParty::Tenant->value => 'Penyewa (DP hangus, tidak ada refund)',
                                CancellationParty::Manager->value => 'Pengelola',
                            ])
                            ->default(CancellationParty::Tenant->value)
                            ->required()
                            ->live(),
                        Forms\Components\Textarea::make('note')
                            ->label(fn (Get $get): string => $get('party') === CancellationParty::Manager->value
                                ? 'Catatan penyelesaian (wajib, misalnya pengembalian DP)'
                                : 'Catatan (opsional)')
                            ->required(fn (Get $get): bool => $get('party') === CancellationParty::Manager->value)
                            ->maxLength(1000),
                    ])
                    ->action(function (Reservation $record, array $data): void {
                        try {
                            app(CancelOrder::class)->handle(
                                $record->order,
                                auth()->user(),
                                filled($data['note'] ?? null) ? $data['note'] : null,
                                CancellationParty::from($data['party']),
                            );
                        } catch (InvalidOrderTransition) {
                            Notification::make()->title('Reservasi ini tidak dapat dibatalkan lagi.')->danger()->send();

                            return;
                        }

                        Notification::make()->title('Reservasi dibatalkan.')->success()->send();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Reservation $record): bool => static::canBeDeleted($record))
                    ->modalDescription('Hanya reservasi yang sudah dibatalkan atau kedaluwarsa dan tanpa bukti transfer yang dapat dihapus. Pesanan dan pembayaran terkait ikut terhapus.')
                    ->using(fn (Reservation $record) => $record->order->delete()),
            ]);
    }

    /**
     * Plain-language settlement state for the admin tables.
     */
    public static function settlementLabel(Order $order): string
    {
        if (! $order->requiresDownPayment()) {
            return $order->status->isBooked() ? 'Bayar penuh' : '-';
        }

        return match ($order->status) {
            OrderStatus::PaidInFull => 'Lunas ('.PaymentPresenter::settlementMethodLabel($order->settlement_method).')',
            OrderStatus::AwaitingSettlementVerification => 'Bukti pelunasan menunggu verifikasi',
            OrderStatus::DpPaid => 'Belum lunas',
            default => '-',
        };
    }

    private static function cancellationLabel(?CancellationParty $party): string
    {
        return match ($party) {
            CancellationParty::Tenant => 'Penyewa (DP hangus)',
            CancellationParty::Manager => 'Pengelola',
            null => '-',
        };
    }

    /**
     * Financial history must stay intact: only closed orders without any payment proof may be removed.
     */
    public static function canBeDeleted(Reservation $reservation): bool
    {
        $order = $reservation->order;

        return in_array($order->status, [OrderStatus::Cancelled, OrderStatus::Expired], true)
            && ! $order->payments()->whereHas('proofs')->exists();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReservations::route('/'),
            'view' => Pages\ViewReservation::route('/{record}'),
            'edit' => Pages\EditReservation::route('/{record}/edit'),
        ];
    }
}
