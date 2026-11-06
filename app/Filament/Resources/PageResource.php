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

    protected static ?string $navigationLabel = 'Profil Desa';

    protected static ?string $modelLabel = 'halaman';

    protected static ?string $pluralModelLabel = 'halaman profil desa';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Isi halaman')->schema([
                Forms\Components\TextInput::make('title')->label('Judul')->required()->maxLength(255),
                Forms\Components\RichEditor::make('body')->label('Isi')->columnSpanFull(),
            ]),
            Forms\Components\Section::make('Pengaturan')->columns(3)->schema([
                Forms\Components\TextInput::make('key')
                    ->label('Kunci')
                    ->helperText('Pengenal internal, mis. about, history.')
                    ->required()
                    ->alphaDash()
                    ->unique(ignoreRecord: true)
                    ->maxLength(100),
                Forms\Components\TextInput::make('slug')
                    ->label('Slug URL')
                    ->required()
                    ->alphaDash()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options(['draft' => 'Draf', 'published' => 'Terbit'])
                    ->default('draft')
                    ->required(),
            ]),
            Forms\Components\Section::make('SEO')->collapsed()->schema([
                Forms\Components\TextInput::make('seo_title')->label('Judul SEO')->maxLength(70),
                Forms\Components\Textarea::make('seo_description')->label('Deskripsi SEO')->maxLength(160),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Judul')->searchable(),
                Tables\Columns\TextColumn::make('slug')->label('Slug'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state->value === 'published' ? 'Terbit' : 'Draf')
                    ->color(fn ($state): string => $state->value === 'published' ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('updated_at')->label('Diubah')->since(),
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
