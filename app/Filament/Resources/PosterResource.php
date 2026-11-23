<?php

namespace App\Filament\Resources;

use App\Domain\Content\Models\Poster;
use App\Filament\Resources\PosterResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PosterResource extends Resource
{
    use Translatable;

    protected static ?string $model = Poster::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Konten';

    protected static ?string $navigationLabel = 'Poster';

    protected static ?string $modelLabel = 'poster';

    protected static ?string $pluralModelLabel = 'poster';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Poster')->schema([
                Forms\Components\SpatieMediaLibraryFileUpload::make('image')
                    ->label('Gambar')
                    ->collection('default')
                    ->image()
                    ->maxSize(5120)
                    ->required(),
                Forms\Components\TextInput::make('title')->label('Judul gambar')->required()->maxLength(255),
                Forms\Components\TextInput::make('alt')
                    ->label('Teks alternatif (alt)')
                    ->maxLength(255)
                    ->helperText('Deskripsi singkat gambar untuk pembaca layar dan SEO. Jika kosong, judul dipakai.'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('image')->label('Gambar')->collection('default')->limit(1),
                Tables\Columns\TextColumn::make('title')->label('Judul')->searchable()->limit(50),
                Tables\Columns\TextColumn::make('alt')->label('Alt')->limit(50)->placeholder('—'),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosters::route('/'),
            'create' => Pages\CreatePoster::route('/create'),
            'edit' => Pages\EditPoster::route('/{record}/edit'),
        ];
    }
}
