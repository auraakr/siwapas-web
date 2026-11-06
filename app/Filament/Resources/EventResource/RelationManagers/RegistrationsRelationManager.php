<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use App\Domain\Event\Actions\CancelEventRegistration;
use App\Domain\Event\Models\EventRegistration;
use App\Domain\Payment\Exceptions\InvalidOrderTransition;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class RegistrationsRelationManager extends RelationManager
{
    protected static string $relationship = 'registrations';

    protected static ?string $title = 'Pendaftar';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('registration_code')
            ->columns([
                Tables\Columns\TextColumn::make('registration_code')->label('Kode')->searchable(),
                Tables\Columns\TextColumn::make('participants.name')->label('Peserta')->listWithLineBreaks(),
                Tables\Columns\TextColumn::make('participant_count')->label('Jumlah'),
                Tables\Columns\TextColumn::make('order.code')->label('Pesanan')->placeholder('Gratis'),
                Tables\Columns\TextColumn::make('order.customer_phone')->label('Kontak'),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->actions([
                Tables\Actions\Action::make('cancel')
                    ->label('Batalkan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (EventRegistration $record): bool => $record->is_active)
                    ->form([
                        Forms\Components\TextInput::make('reason')->label('Alasan pembatalan')->required()->maxLength(500),
                    ])
                    ->action(function (EventRegistration $record, array $data): void {
                        try {
                            app(CancelEventRegistration::class)->handle($record, auth()->user(), $data['reason']);
                        } catch (InvalidOrderTransition) {
                            Notification::make()->title('Pendaftaran ini tidak dapat dibatalkan lagi.')->danger()->send();

                            return;
                        }

                        Notification::make()->title('Pendaftaran dibatalkan.')->success()->send();
                    }),
            ]);
    }
}
