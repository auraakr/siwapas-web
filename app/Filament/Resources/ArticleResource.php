<?php

namespace App\Filament\Resources;

use App\Domain\Content\Models\Article;
use App\Filament\Resources\ArticleResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ArticleResource extends Resource
{
    use Translatable;

    protected static ?string $model = Article::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationGroup = 'Konten';

    protected static ?string $navigationLabel = 'Cerita Desa';

    protected static ?string $modelLabel = 'cerita';

    protected static ?string $pluralModelLabel = 'cerita desa';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        $timezone = config('siwapas.display_timezone');

        return $form->schema([
            Forms\Components\Section::make('Isi cerita')->columns(2)->schema([
                Forms\Components\TextInput::make('title')->label('Judul')->required()->maxLength(255)->columnSpanFull(),
                Forms\Components\Textarea::make('excerpt')->label('Ringkasan (tampil di beranda)')->columnSpanFull(),
                Forms\Components\RichEditor::make('body')->label('Isi')->columnSpanFull(),
                Forms\Components\SpatieMediaLibraryFileUpload::make('images')
                    ->label('Foto')
                    ->collection('default')
                    ->image()
                    ->multiple()
                    ->maxSize(5120)
                    ->columnSpanFull(),
            ]),
            Forms\Components\Section::make('Pengaturan')->columns(2)->schema([
                Forms\Components\TextInput::make('slug')->label('Slug URL')->required()->alphaDash()->unique(ignoreRecord: true),
                Forms\Components\Select::make('category')
                    ->label('Kategori')
                    ->options(['culture' => 'Budaya', 'village_potential' => 'Potensi Desa', 'community' => 'Masyarakat'])
                    ->required(),
                Forms\Components\TextInput::make('author_name')->label('Penulis')->maxLength(255),
                Forms\Components\DateTimePicker::make('published_at')
                    ->label('Tanggal terbit (WIB)')
                    ->helperText('Cerita baru tampil setelah tanggal ini dan berstatus Terbit.')
                    ->timezone($timezone)
                    ->seconds(false),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options(['draft' => 'Draf', 'published' => 'Terbit'])
                    ->default('draft')
                    ->required(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('images')->label('Foto')->collection('default')->limit(1)->circular(),
                Tables\Columns\TextColumn::make('title')->label('Judul')->searchable()->limit(50),
                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => match ($state->value) {
                        'culture' => 'Budaya',
                        'village_potential' => 'Potensi Desa',
                        default => 'Masyarakat',
                    }),
                Tables\Columns\TextColumn::make('published_at')->label('Terbit')->dateTime('d M Y H:i', config('siwapas.display_timezone'))->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state->value === 'published' ? 'Terbit' : 'Draf')
                    ->color(fn ($state): string => $state->value === 'published' ? 'success' : 'gray'),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
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
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}
