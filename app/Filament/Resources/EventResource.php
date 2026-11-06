<?php

namespace App\Filament\Resources;

use App\Domain\Event\Models\Event;
use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers\RegistrationsRelationManager;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EventResource extends Resource
{
    use Translatable;

    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Acara';

    protected static ?string $navigationLabel = 'Event & Kegiatan';

    protected static ?string $modelLabel = 'event';

    protected static ?string $pluralModelLabel = 'event & kegiatan';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        $timezone = config('siwapas.display_timezone');

        return $form->schema([
            Forms\Components\Section::make('Informasi')->columns(2)->schema([
                Forms\Components\TextInput::make('title')->label('Judul')->required()->maxLength(255),
                Forms\Components\TextInput::make('slug')
                    ->label('Slug URL')
                    ->helperText('Kosongkan saat membuat acara untuk dibuat otomatis dari judul.')
                    ->alphaDash()
                    ->unique(ignoreRecord: true)
                    ->required(fn (string $operation): bool => $operation === 'edit'),
                Forms\Components\RichEditor::make('description')->label('Deskripsi')->columnSpanFull(),
                Forms\Components\Textarea::make('requirements')->label('Persyaratan')->columnSpanFull(),
                Forms\Components\SpatieMediaLibraryFileUpload::make('images')
                    ->label('Foto')
                    ->collection('default')
                    ->image()
                    ->multiple()
                    ->maxSize(5120)
                    ->columnSpanFull(),
            ]),
            Forms\Components\Section::make('Waktu & lokasi')->columns(2)->schema([
                Forms\Components\DateTimePicker::make('starts_at')->label('Mulai (WIB)')->timezone($timezone)->seconds(false)->required(),
                Forms\Components\DateTimePicker::make('ends_at')->label('Selesai (WIB)')->timezone($timezone)->seconds(false)->after('starts_at'),
                Forms\Components\TextInput::make('organizer')->label('Penyelenggara')->maxLength(255),
                Forms\Components\TextInput::make('location_name')->label('Nama lokasi')->maxLength(255),
                Forms\Components\TextInput::make('address')->label('Alamat')->maxLength(500)->columnSpanFull(),
                Forms\Components\TextInput::make('latitude')->label('Latitude')->numeric()->minValue(-90)->maxValue(90),
                Forms\Components\TextInput::make('longitude')->label('Longitude')->numeric()->minValue(-180)->maxValue(180),
            ]),
            Forms\Components\Section::make('Pendaftaran')->columns(3)->schema([
                Forms\Components\TextInput::make('fee')->label('Biaya per peserta (0 = gratis)')->numeric()->prefix('Rp')->minValue(0)->default(0)->required(),
                Forms\Components\TextInput::make('quota')->label('Kuota (kosong = tanpa batas)')->numeric()->minValue(1),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options(['draft' => 'Draf', 'published' => 'Terbit', 'cancelled' => 'Dibatalkan'])
                    ->default('draft')
                    ->required(),
                Forms\Components\DateTimePicker::make('registration_opens_at')->label('Pendaftaran dibuka (WIB)')->timezone($timezone)->seconds(false),
                Forms\Components\DateTimePicker::make('registration_closes_at')->label('Pendaftaran ditutup (WIB)')->timezone($timezone)->seconds(false)->after('registration_opens_at'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('images')->label('Foto')->collection('default')->limit(1)->circular(),
                Tables\Columns\TextColumn::make('title')->label('Judul')->searchable(),
                Tables\Columns\TextColumn::make('starts_at')->label('Mulai')->dateTime('d M Y H:i', config('siwapas.display_timezone'))->sortable(),
                Tables\Columns\TextColumn::make('fee')->label('Biaya')->formatStateUsing(fn (int $state): string => $state > 0 ? 'Rp '.number_format($state, 0, ',', '.') : 'Gratis'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => match ($state->value) {
                        'published' => 'Terbit',
                        'cancelled' => 'Dibatalkan',
                        default => 'Draf',
                    })
                    ->color(fn ($state): string => match ($state->value) {
                        'published' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('starts_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(['draft' => 'Draf', 'published' => 'Terbit', 'cancelled' => 'Dibatalkan']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn (Event $record): bool => $record->registrations()->exists())
                    ->modalDescription('Acara yang sudah punya pendaftar tidak dapat dihapus. Ubah statusnya menjadi Dibatalkan.'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RegistrationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
