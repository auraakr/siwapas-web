<?php

namespace App\Filament\Resources\LocalProductResource\Pages;

use App\Filament\Resources\LocalProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\Translatable;

class CreateLocalProduct extends CreateRecord
{
    use Translatable;

    protected static string $resource = LocalProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }
}
