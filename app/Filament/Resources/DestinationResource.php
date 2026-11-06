<?php

namespace App\Filament\Resources;

use App\Domain\Content\Models\Destination;
use App\Filament\Resources\DestinationResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DestinationResource extends Resource
{
    use Translatable;

    protected static ?string $model = Destination::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'Konten';

    protected static ?string $navigationLabel = 'Destinasi & Aktivitas';

    protected static ?string $modelLabel = 'destinasi / aktivitas';

    protected static ?string $pluralModelLabel = 'destinasi & aktivitas';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi')->columns(2)->schema([
                Forms\Components\TextInput::make('name')->label('Nama')->required()->maxLength(255),
                Forms\Components\Select::make('type')
                    ->label('Jenis')
                    ->options(['destination' => 'Destinasi', 'activity' => 'Aktivitas'])
                    ->default('destination')
                    ->required(),
                Forms\Components\Textarea::make('summary')->label('Ringkasan')->columnSpanFull(),
                Forms\Components\RichEditor::make('description')->label('Deskripsi')->columnSpanFull(),
                Forms\Components\SpatieMediaLibraryFileUpload::make('images')
                    ->label('Foto')
                    ->collection('default')
                    ->image()
                    ->multiple()
                    ->reorderable()
                    ->maxSize(5120)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('video_url')->label('Tautan video')->url()->maxLength(255),
            ]),
            Forms\Components\Section::make('Lokasi & operasional')->columns(2)->schema([
                Forms\Components\TextInput::make('address')->label('Alamat')->maxLength(255)->columnSpanFull(),
                Forms\Components\TextInput::make('latitude')->label('Latitude')->numeric()->minValue(-90)->maxValue(90),
                Forms\Components\TextInput::make('longitude')->label('Longitude')->numeric()->minValue(-180)->maxValue(180),
                Forms\Components\KeyValue::make('opening_hours')
                    ->label('Jam buka')
                    ->keyLabel('Hari')
                    ->valueLabel('Jam')
                    ->columnSpanFull(),
                Forms\Components\TagsInput::make('facilities')->label('Fasilitas'),
                Forms\Components\TagsInput::make('rules')->label('Aturan'),
            ]),
            Forms\Components\Section::make('Pengaturan')->columns(4)->schema([
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options(['draft' => 'Draf', 'published' => 'Terbit'])
                    ->default('draft')
                    ->required(),
                Forms\Components\TextInput::make('slug')->label('Slug URL')->required()->alphaDash()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('sort_order')->label('Urutan')->numeric()->default(0)->required(),
                Forms\Components\Group::make([
                    Forms\Components\Toggle::make('requires_reservation')->label('Perlu reservasi'),
                    Forms\Components\Toggle::make('is_featured')->label('Unggulan'),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('images')->label('Foto')->collection('default')->limit(1)->circular(),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state->value === 'activity' ? 'Aktivitas' : 'Destinasi'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state->value === 'published' ? 'Terbit' : 'Draf')
                    ->color(fn ($state): string => $state->value === 'published' ? 'success' : 'gray'),
                Tables\Columns\IconColumn::make('is_featured')->label('Unggulan')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label('Urutan')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis')
                    ->options(['destination' => 'Destinasi', 'activity' => 'Aktivitas']),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(['draft' => 'Draf', 'published' => 'Terbit']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDestinations::route('/'),
            'create' => Pages\CreateDestination::route('/create'),
            'edit' => Pages\EditDestination::route('/{record}/edit'),
        ];
    }
}
