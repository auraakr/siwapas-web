<?php

namespace App\Filament\Resources;

use App\Domain\Content\Models\Page;
use App\Filament\Resources\PageResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PageResource extends Resource
{
    use Translatable;

    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Konten';

    protected static ?string $navigationLabel = 'Halaman Profil';

    protected static ?string $modelLabel = 'halaman';

    protected static ?string $pluralModelLabel = 'halaman profil';

    protected static ?int $navigationSort = 1;

    /**
     * The only pages the public site reads, keyed by what the village profile page expects.
     *
     * @var array<string, string>
     */
    public const KEYS = [
        'profile' => 'Cerita Desa (halaman Profil Desa)',
        'vision' => 'Visi & Misi (halaman Profil Desa)',
    ];

    private const SLUGS = [
        'profile' => 'profil-desa',
        'vision' => 'visi-misi',
    ];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Halaman')->columns(2)->schema([
                Forms\Components\Select::make('key')
                    ->label('Bagian')
                    ->options(self::KEYS)
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->disabledOn('edit')
                    ->live()
                    ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', self::SLUGS[$state] ?? $state)),
                Forms\Components\TextInput::make('slug')->label('Slug URL')->required()->alphaDash()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('title')->label('Judul')->required()->maxLength(255)->columnSpanFull(),
                Forms\Components\RichEditor::make('body')
                    ->label('Isi')
                    ->toolbarButtons(['bold', 'italic', 'h2', 'h3', 'bulletList', 'orderedList', 'blockquote', 'link', 'undo', 'redo'])
                    ->columnSpanFull(),
            ]),
            Forms\Components\Section::make('Mesin pencari (SEO)')->columns(1)->collapsed()->schema([
                Forms\Components\TextInput::make('seo_title')->label('Judul untuk Google')->maxLength(70),
                Forms\Components\Textarea::make('seo_description')->label('Deskripsi untuk Google')->rows(2)->maxLength(160),
            ]),
            Forms\Components\Section::make('Pengaturan')->schema([
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options(['draft' => 'Draf', 'published' => 'Terbit'])
                    ->default('published')
                    ->required(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label('Bagian')
                    ->formatStateUsing(fn (string $state): string => self::KEYS[$state] ?? $state),
                Tables\Columns\TextColumn::make('title')->label('Judul')->searchable()->limit(50),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state->value === 'published' ? 'Terbit' : 'Draf')
                    ->color(fn ($state): string => $state->value === 'published' ? 'success' : 'gray'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
