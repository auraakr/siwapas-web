<?php

namespace App\Filament\Resources;

use App\Domain\Content\Models\LocalProduct;
use App\Filament\Resources\LocalProductResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LocalProductResource extends Resource
{
    use Translatable;

    protected static ?string $model = LocalProduct::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Konten';

    protected static ?string $navigationLabel = 'Produk Lokal';

    protected static ?string $modelLabel = 'produk lokal';

    protected static ?string $pluralModelLabel = 'produk lokal';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nama produk')->required()->maxLength(255),
            Forms\Components\TextInput::make('maker_name')->label('Pembuat / UMKM')->maxLength(255),
            Forms\Components\Textarea::make('description')->label('Deskripsi')->columnSpanFull(),
            Forms\Components\TextInput::make('whatsapp_number')
                ->label('Nomor WhatsApp')
                ->tel()
                ->regex('/^(\+62|62|0)8[0-9]{7,12}$/')
                ->validationMessages(['regex' => 'Gunakan format 08xxxxxxxxxx.'])
                ->maxLength(30),
            Forms\Components\TextInput::make('sort_order')->label('Urutan')->numeric()->default(0)->required(),
            Forms\Components\SpatieMediaLibraryFileUpload::make('images')
                ->label('Foto')
                ->collection('default')
                ->image()
                ->multiple()
                ->maxSize(5120)
                ->columnSpanFull(),
            Forms\Components\Toggle::make('is_published')->label('Tampilkan di situs')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('images')->label('Foto')->collection('default')->limit(1)->circular(),
                Tables\Columns\TextColumn::make('name')->label('Produk')->searchable(),
                Tables\Columns\TextColumn::make('maker_name')->label('Pembuat')->searchable(),
                Tables\Columns\IconColumn::make('is_published')->label('Tampil')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label('Urutan')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLocalProducts::route('/'),
            'create' => Pages\CreateLocalProduct::route('/create'),
            'edit' => Pages\EditLocalProduct::route('/{record}/edit'),
        ];
    }
}
