<?php

namespace App\Filament\Resources;

use App\Domain\Content\Models\Faq;
use App\Filament\Resources\FaqResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FaqResource extends Resource
{
    use Translatable;

    protected static ?string $model = Faq::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationGroup = 'Konten';

    protected static ?string $navigationLabel = 'Tanya Jawab (FAQ)';

    protected static ?string $modelLabel = 'pertanyaan';

    protected static ?string $pluralModelLabel = 'tanya jawab';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('question')->label('Pertanyaan')->required()->maxLength(255)->columnSpanFull(),
            Forms\Components\Textarea::make('answer')->label('Jawaban')->required()->rows(5)->columnSpanFull(),
            Forms\Components\TextInput::make('sort_order')->label('Urutan')->numeric()->default(0)->required(),
            Forms\Components\Toggle::make('is_published')->label('Tampilkan di beranda')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('question')->label('Pertanyaan')->searchable()->limit(70),
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
            'index' => Pages\ListFaqs::route('/'),
            'create' => Pages\CreateFaq::route('/create'),
            'edit' => Pages\EditFaq::route('/{record}/edit'),
        ];
    }
}
