<?php

namespace App\Filament\Resources\PosterResource\Pages;

use App\Filament\Resources\PosterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPoster extends EditRecord
{
    use EditRecord\Concerns\Translatable;

    protected static string $resource = PosterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
