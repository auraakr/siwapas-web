<?php

namespace App\Filament\Resources;

use App\Domain\Content\Models\ContactMessage;
use App\Filament\Resources\ContactMessageResource\Pages;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Konten';

    protected static ?string $navigationLabel = 'Pesan Masuk';

    protected static ?string $modelLabel = 'pesan';

    protected static ?string $pluralModelLabel = 'pesan masuk';

    protected static ?int $navigationSort = 6;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $unhandled = ContactMessage::query()->whereNull('handled_at')->count();

        return $unhandled > 0 ? (string) $unhandled : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make('name')->label('Nama'),
            TextEntry::make('created_at')
                ->label('Diterima')
                ->dateTime('d M Y, H:i')
                ->timezone(config('siwapas.display_timezone')),
            TextEntry::make('phone')->label('WhatsApp')->placeholder('—'),
            TextEntry::make('email')->label('Email')->placeholder('—'),
            TextEntry::make('subject')->label('Subjek')->placeholder('—')->columnSpanFull(),
            TextEntry::make('message')
                ->label('Pesan')
                ->columnSpanFull()
                ->extraAttributes(['class' => 'whitespace-pre-line']),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('handled_at')
                    ->label('Selesai')
                    ->boolean()
                    ->state(fn (ContactMessage $record): bool => $record->handled_at !== null),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diterima')
                    ->dateTime('d M Y, H:i')
                    ->timezone(config('siwapas.display_timezone'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('WhatsApp')->placeholder('—'),
                Tables\Columns\TextColumn::make('email')->label('Email')->placeholder('—'),
                Tables\Columns\TextColumn::make('subject')->label('Subjek')->limit(40)->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('handled_at')
                    ->label('Status')
                    ->nullable()
                    ->placeholder('Semua')
                    ->trueLabel('Sudah ditangani')
                    ->falseLabel('Belum ditangani'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->slideOver(),
                Tables\Actions\Action::make('whatsapp')
                    ->label('Balas WA')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->url(fn (ContactMessage $record): ?string => self::whatsappUrl($record))
                    ->openUrlInNewTab()
                    ->visible(fn (ContactMessage $record): bool => filled($record->phone)),
                Tables\Actions\Action::make('handled')
                    ->label('Tandai selesai')
                    ->icon('heroicon-o-check')
                    ->visible(fn (ContactMessage $record): bool => $record->handled_at === null)
                    ->action(fn (ContactMessage $record) => $record->update(['handled_at' => now()])),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageContactMessages::route('/'),
        ];
    }

    private static function whatsappUrl(ContactMessage $record): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $record->phone);

        if ($digits === '' || $digits === null) {
            return null;
        }

        return 'https://wa.me/'.preg_replace('/^(62|0)/', '62', $digits);
    }
}
