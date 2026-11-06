<?php

namespace App\Filament\Resources;

use App\Domain\Catalog\Models\Facility;
use App\Filament\Resources\FacilityResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FacilityResource extends Resource
{
    use Translatable;

    protected static ?string $model = Facility::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static ?string $navigationGroup = 'Layanan';

    protected static ?string $navigationLabel = 'Fasilitas Sewa';

    protected static ?string $modelLabel = 'fasilitas';

    protected static ?string $pluralModelLabel = 'fasilitas sewa';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi')->columns(2)->schema([
                Forms\Components\TextInput::make('name')->label('Nama')->required()->maxLength(255),
                Forms\Components\TextInput::make('slug')->label('Slug URL')->required()->alphaDash()->unique(ignoreRecord: true),
                Forms\Components\Textarea::make('description')->label('Deskripsi')->columnSpanFull(),
                Forms\Components\TagsInput::make('rules')->label('Aturan penggunaan')->columnSpanFull(),
                Forms\Components\SpatieMediaLibraryFileUpload::make('images')
                    ->label('Foto')
                    ->collection('default')
                    ->image()
                    ->multiple()
                    ->maxSize(5120)
                    ->columnSpanFull(),
            ]),
            Forms\Components\Section::make('Harga & kapasitas')->columns(4)->schema([
                Forms\Components\TextInput::make('base_price')
                    ->label('Harga dasar per hari')
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(0)
                    ->required(),
                Forms\Components\TextInput::make('capacity')->label('Kapasitas (orang)')->numeric()->minValue(1),
                Forms\Components\Select::make('unit')->label('Satuan')->options(['day' => 'Hari'])->default('day')->required(),
                Forms\Components\TextInput::make('sort_order')->label('Urutan')->numeric()->default(0)->required(),
                Forms\Components\Toggle::make('is_active')->label('Aktif (bisa dipesan)')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('images')->label('Foto')->collection('default')->limit(1)->circular(),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable(),
                Tables\Columns\TextColumn::make('capacity')->label('Kapasitas'),
                Tables\Columns\TextColumn::make('base_price')->label('Harga dasar')->money('IDR', locale: 'id'),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn (Facility $record): bool => $record->reservations()->exists())
                    ->modalDescription('Aturan harga dan tanggal diblokir milik fasilitas ini ikut terhapus.'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFacilities::route('/'),
            'create' => Pages\CreateFacility::route('/create'),
            'edit' => Pages\EditFacility::route('/{record}/edit'),
        ];
    }
}
