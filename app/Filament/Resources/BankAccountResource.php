<?php

namespace App\Filament\Resources;

use App\Domain\Payment\Enums\PaymentMethodType;
use App\Domain\Payment\Models\BankAccount;
use App\Filament\Resources\BankAccountResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BankAccountResource extends Resource
{
    protected static ?string $model = BankAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Metode Pembayaran';

    protected static ?string $modelLabel = 'metode pembayaran';

    protected static ?string $pluralModelLabel = 'metode pembayaran';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('type')
                ->label('Jenis')
                ->options(['bank' => 'Rekening bank', 'qris' => 'QRIS'])
                ->default('bank')
                ->live()
                ->required(),
            Forms\Components\TextInput::make('bank_name')
                ->label(fn (Get $get): string => $get('type') === 'qris' ? 'Nama (mis. QRIS Desa)' : 'Nama bank')
                ->required()
                ->maxLength(100),
            Forms\Components\TextInput::make('account_number')
                ->label('Nomor rekening')
                ->visible(fn (Get $get): bool => $get('type') !== 'qris')
                ->required(fn (Get $get): bool => $get('type') !== 'qris')
                ->maxLength(50),
            Forms\Components\TextInput::make('account_holder')
                ->label('Atas nama')
                ->visible(fn (Get $get): bool => $get('type') !== 'qris')
                ->required(fn (Get $get): bool => $get('type') !== 'qris')
                ->maxLength(255),
            Forms\Components\SpatieMediaLibraryFileUpload::make('qris')
                ->label('Gambar QRIS')
                ->collection(BankAccount::QRIS_COLLECTION)
                ->image()
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                ->maxSize(5120)
                ->visible(fn (Get $get): bool => $get('type') === 'qris')
                ->required(fn (Get $get): bool => $get('type') === 'qris'),
            Forms\Components\TextInput::make('sort_order')->label('Urutan')->numeric()->default(0)->required(),
            Forms\Components\Toggle::make('is_active')->label('Aktif (ditampilkan ke pengunjung)')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('qris')->label('QRIS')->collection(BankAccount::QRIS_COLLECTION),
                Tables\Columns\TextColumn::make('bank_name')->label('Nama')->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (PaymentMethodType $state): string => $state === PaymentMethodType::Qris ? 'QRIS' : 'Rekening'),
                Tables\Columns\TextColumn::make('account_number')->label('Nomor')->placeholder('-'),
                Tables\Columns\TextColumn::make('account_holder')->label('Atas nama')->placeholder('-'),
                Tables\Columns\ToggleColumn::make('is_active')->label('Aktif'),
            ])
            ->defaultSort('sort_order')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->modalDescription('Pesanan lama tetap menampilkan tujuan transfer yang sudah disalin saat dibuat.'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageBankAccounts::route('/'),
        ];
    }
}
