<?php

namespace App\Filament\Resources;

use App\Domain\Catalog\Models\PricingRule;
use App\Filament\Resources\PricingRuleResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PricingRuleResource extends Resource
{
    use Translatable;

    protected static ?string $model = PricingRule::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Layanan';

    protected static ?string $navigationLabel = 'Aturan Harga';

    protected static ?string $modelLabel = 'aturan harga';

    protected static ?string $pluralModelLabel = 'aturan harga';

    protected static ?int $navigationSort = 2;

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
            Forms\Components\TextInput::make('name')->label('Nama aturan')->required()->maxLength(255),
            Forms\Components\Select::make('type')
                ->label('Jenis')
                ->options([
                    'weekday' => 'Hari kerja',
                    'weekend' => 'Akhir pekan',
                    'holiday' => 'Hari libur',
                    'season' => 'Musim',
                ])
                ->required(),
            Forms\Components\TextInput::make('price')->label('Harga per hari')->numeric()->prefix('Rp')->minValue(0)->required(),
            Forms\Components\CheckboxList::make('days_of_week')
                ->label('Berlaku pada hari')
                ->helperText('Kosongkan untuk memakai bawaan jenis (hari kerja Sen–Jum, akhir pekan Sab–Min).')
                ->options([1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'])
                ->columns(4)
                ->columnSpanFull(),
            Forms\Components\DatePicker::make('date_from')->label('Mulai tanggal'),
            Forms\Components\DatePicker::make('date_to')->label('Sampai tanggal')->afterOrEqual('date_from'),
            Forms\Components\TextInput::make('priority')
                ->label('Prioritas')
                ->helperText('Aturan dengan prioritas lebih tinggi menang bila beberapa aturan cocok.')
                ->numeric()
                ->default(0)
                ->required(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('facility.name')->label('Fasilitas')->searchable(),
                Tables\Columns\TextColumn::make('name')->label('Aturan')->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => match ($state->value) {
                        'weekday' => 'Hari kerja',
                        'weekend' => 'Akhir pekan',
                        'holiday' => 'Hari libur',
                        default => 'Musim',
                    }),
                Tables\Columns\TextColumn::make('price')->label('Harga')->money('IDR', locale: 'id'),
                Tables\Columns\TextColumn::make('priority')->label('Prioritas')->sortable(),
            ])
            ->defaultSort('priority', 'desc')
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
            'index' => Pages\ListPricingRules::route('/'),
            'create' => Pages\CreatePricingRule::route('/create'),
            'edit' => Pages\EditPricingRule::route('/{record}/edit'),
        ];
    }
}
