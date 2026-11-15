<?php

namespace App\Filament\Resources;

use App\Domain\Content\Enums\GalleryItemType;
use App\Domain\Content\Models\GalleryItem;
use App\Domain\Content\Services\YoutubeUrl;
use App\Filament\Resources\GalleryItemResource\Pages;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GalleryItemResource extends Resource
{
    use Translatable;

    protected static ?string $model = GalleryItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Konten';

    protected static ?string $navigationLabel = 'Galeri';

    protected static ?string $modelLabel = 'item galeri';

    protected static ?string $pluralModelLabel = 'galeri';

    protected static ?int $navigationSort = 3;

    /**
     * The public gallery decides photo or video from the YouTube link, so the admin never picks a
     * type: it is derived here from whichever enum case is the video one.
     */
    public static function resolveType(?string $youtubeUrl): string
    {
        $cases = collect(GalleryItemType::cases());
        $video = $cases->first(fn (GalleryItemType $case): bool => str_contains(strtolower($case->value.$case->name), 'video'));
        $other = $cases->first(fn (GalleryItemType $case): bool => $case !== $video) ?? $cases->first();

        return ($youtubeUrl && $video ? $video : $other)->value;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Isi galeri')->columns(2)->schema([
                Forms\Components\SpatieMediaLibraryFileUpload::make('photo')
                    ->label('Foto')
                    ->collection('default')
                    ->image()
                    ->maxSize(5120)
                    ->required(fn (Forms\Get $get): bool => blank($get('youtube_url')))
                    ->helperText('Wajib untuk foto. Untuk video, foto sampul bersifat opsional: jika kosong, dipakai gambar dari YouTube.')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('youtube_url')
                    ->label('Tautan YouTube (khusus video)')
                    ->url()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->rules([
                        fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                            if (filled($value) && YoutubeUrl::idFrom($value) === null) {
                                $fail('Tautan YouTube tidak dikenali. Gunakan tautan dari youtube.com atau youtu.be.');
                            }
                        },
                    ])
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('title')->label('Judul / keterangan')->maxLength(255),
                Forms\Components\TextInput::make('category')
                    ->label('Kategori')
                    ->maxLength(100)
                    ->datalist(fn (): array => GalleryItem::query()
                        ->whereNotNull('category')
                        ->distinct()
                        ->orderBy('category')
                        ->pluck('category')
                        ->all())
                    ->helperText('Contoh: Alam, Budaya, Kegiatan. Kategori yang sama otomatis menjadi tombol saring di halaman galeri.'),
            ]),
            Forms\Components\Section::make('Pengaturan')->columns(2)->schema([
                Forms\Components\TextInput::make('sort_order')->label('Urutan')->numeric()->default(0)->required(),
                Forms\Components\Toggle::make('is_published')->label('Tampilkan di situs')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('photo')->label('Foto')->collection('default')->limit(1)->square(),
                Tables\Columns\TextColumn::make('title')->label('Judul')->searchable()->limit(40)->placeholder('—'),
                Tables\Columns\TextColumn::make('category')->label('Kategori')->badge()->placeholder('—'),
                Tables\Columns\IconColumn::make('youtube_url')
                    ->label('Video')
                    ->boolean()
                    ->state(fn (GalleryItem $record): bool => filled($record->youtube_url)),
                Tables\Columns\ToggleColumn::make('is_published')->label('Tampil'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')->label('Status')->placeholder('Semua')->trueLabel('Tampil')->falseLabel('Disembunyikan'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGalleryItems::route('/'),
            'create' => Pages\CreateGalleryItem::route('/create'),
            'edit' => Pages\EditGalleryItem::route('/{record}/edit'),
        ];
    }
}
