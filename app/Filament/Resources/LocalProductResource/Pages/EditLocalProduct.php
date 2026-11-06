<?php

namespace App\Filament\Resources\LocalProductResource\Pages;

use App\Filament\Resources\LocalProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\EditRecord\Concerns\Translatable;

class EditLocalProduct extends EditRecord
{
    use Translatable;

    protected static string $resource = LocalProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
