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
     * The only pages the public site reads: the sections of "Profil Desa" and the two text blocks of
     * "Kunjungan Kami".
     *
     * @var array<string, string>
     */
    public const KEYS = [
        'profile' => 'Profil Desa: Identitas Wisata (Tentang Kami)',
        'history' => 'Profil Desa: Sejarah',
        'vision' => 'Profil Desa: Visi',
        'mission' => 'Profil Desa: Misi',
        'management' => 'Profil Desa: Pengelola',
        'potential' => 'Profil Desa: Potensi Desa',
        'visit_info' => 'Kunjungan Kami: Informasi Reservasi',
        'terms' => 'Kunjungan Kami: Ketentuan Reservasi',
    ];

    /** Sections that show a photo beside the text. */
    private const WITH_PHOTO = ['profile', 'history', 'management', 'potential'];

    private const SLUGS = [
        'profile' => 'profil-desa',
        'history' => 'sejarah',
        'vision' => 'visi-misi',
        'mission' => 'misi',
        'management' => 'pengelola',
        'potential' => 'potensi-desa',
        'visit_info' => 'informasi-reservasi',
        'terms' => 'ketentuan-reservasi',
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
                Forms\Components\SpatieMediaLibraryFileUpload::make('photo')
                    ->label('Foto (opsional)')
                    ->collection('default')
                    ->image()
                    ->maxSize(5120)
                    ->helperText('Ditampilkan di samping teks pada halaman Profil Desa.')
                    ->visible(fn (Forms\Get $get): bool => in_array($get('key'), self::WITH_PHOTO, true))
                    ->columnSpanFull(),
                Forms\Components\RichEditor::make('body')
                    ->label('Isi')
                    ->toolbarButtons(['bold', 'italic', 'h2', 'h3', 'bulletList', 'orderedList', 'blockquote', 'link', 'undo', 'redo'])
                    ->helperText(fn (Forms\Get $get): ?string => match ($get('key')) {
                        'vision' => 'Tulis kalimat visinya saja. Di halaman Profil Desa visi tampil sebagai kutipan besar.',
                        'mission' => 'Tulis setiap misi sebagai satu butir daftar bernomor. Butir-butir ini tampil sebagai daftar bernomor di bawah visi.',
                        default => null,
                    })
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
