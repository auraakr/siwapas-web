<?php

namespace App\Filament\Resources;

use App\Domain\Booking\Models\Reservation;
use App\Domain\Payment\Actions\CancelOrder;
use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Exceptions\InvalidOrderTransition;
use App\Filament\Resources\ReservationResource\Pages;
use App\Filament\Support\OrderStatusPresenter;
use Filament\Forms;
use Filament\Forms\Form;
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
                Infolists\Components\TextEntry::make('order.total')->label('Total')->money('IDR', locale: 'id'),
                Infolists\Components\TextEntry::make('order.unique_code')->label('Kode unik'),
                Infolists\Components\TextEntry::make('order.confirmed_at')->label('Dikonfirmasi')->dateTime('d M Y H:i', config('siwapas.display_timezone'))->placeholder('-'),
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
                Tables\Columns\TextColumn::make('order.total')->label('Total')->money('IDR', locale: 'id'),
                Tables\Columns\TextColumn::make('order.status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (OrderStatus $state): string => OrderStatusPresenter::label($state))
                    ->color(fn (OrderStatus $state): string => OrderStatusPresenter::color($state)),
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
                Tables\Actions\Action::make('cancel')
                    ->label('Batalkan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Reservation $record): bool => $record->order->status->canTransitionTo(OrderStatus::Cancelled))
                    ->form([
                        Forms\Components\TextInput::make('reason')->label('Alasan pembatalan')->required()->maxLength(500),
                    ])
                    ->action(function (Reservation $record, array $data): void {
                        try {
                            app(CancelOrder::class)->handle($record->order, auth()->user(), $data['reason']);
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
