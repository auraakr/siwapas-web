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
use Illuminate\Database\Eloquent\Builder;

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
                Forms\Components\TextInput::make('sort_order')
                    ->label('Urutan')
                    ->helperText('Angka kecil tampil lebih dulu. Urutan juga bisa diatur dengan menyeret baris di daftar poster.')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Forms\Components\Toggle::make('is_published')->label('Tampilkan di beranda')->default(true),
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
                Tables\Columns\ToggleColumn::make('is_published')->label('Tampil'),
                Tables\Columns\TextColumn::make('sort_order')->label('Urutan')->sortable(),
            ])
            // Same order as the carousel: manual order first, newest first among equals.
            ->modifyQueryUsing(fn (Builder $query) => $query->orderBy('sort_order')->orderByDesc('id'))
            ->reorderable('sort_order')
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
