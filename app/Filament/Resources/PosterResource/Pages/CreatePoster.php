<?php

namespace App\Filament\Resources\PosterResource\Pages;

use App\Filament\Resources\PosterResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePoster extends CreateRecord
{
    use CreateRecord\Concerns\Translatable;

    protected static string $resource = PosterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }
}
