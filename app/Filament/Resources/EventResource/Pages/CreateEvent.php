<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\Translatable;
use Illuminate\Support\Str;

class CreateEvent extends CreateRecord
{
    use Translatable;

    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['slug'] ?? null)) {
            $base = Str::slug((string) $data['title']) ?: 'acara';
            $slug = $base;

            for ($suffix = 2; EventResource::getModel()::query()->where('slug', $slug)->exists(); $suffix++) {
                $slug = "{$base}-{$suffix}";
            }

            $data['slug'] = $slug;
        }

        return $data;
    }
}
