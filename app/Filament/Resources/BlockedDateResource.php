<?php

namespace App\Filament\Resources;

use App\Domain\Catalog\Models\BlockedDate;
use App\Filament\Resources\BlockedDateResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BlockedDateResource extends Resource
{
    protected static ?string $model = BlockedDate::class;

    protected static ?string $navigationIcon = 'heroicon-o-no-symbol';

    protected static ?string $navigationGroup = 'Layanan';

    protected static ?string $navigationLabel = 'Tanggal Diblokir';

    protected static ?string $modelLabel = 'tanggal diblokir';

    protected static ?string $pluralModelLabel = 'tanggal diblokir';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('facility_id')
                ->label('Fasilitas')
                ->relationship('facility', 'slug')
                ->getOptionLabelFromRecordUsing(fn ($record): string => $record->name)
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\DatePicker::make('starts_on')->label('Mulai')->required(),
            Forms\Components\DatePicker::make('ends_on')->label('Sampai (termasuk)')->required()->afterOrEqual('starts_on'),
            Forms\Components\TextInput::make('reason')->label('Alasan')->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('facility.name')->label('Fasilitas')->searchable(),
                Tables\Columns\TextColumn::make('starts_on')->label('Mulai')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('ends_on')->label('Sampai')->date('d M Y'),
                Tables\Columns\TextColumn::make('reason')->label('Alasan'),
            ])
            ->defaultSort('starts_on', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('facility_id')->label('Fasilitas')->relationship('facility', 'slug'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageBlockedDates::route('/'),
        ];
    }
}
