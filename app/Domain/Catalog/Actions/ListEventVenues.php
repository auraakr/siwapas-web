<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Models\Facility;
use App\Domain\Content\Services\SiteSettings;

class ListEventVenues
{
    public function __construct(private SiteSettings $settings) {}

    /**
     * The active facilities the managers picked under Pengaturan Situs for visitors who want to
     * hold their own event, in the facility order.
     *
     * @return list<array{slug: string, name: string}>
     */
    public function handle(): array
    {
        $ids = $this->settings->eventVenueFacilityIds();

        if ($ids === []) {
            return [];
        }

        return array_values(Facility::query()
            ->whereKey($ids)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Facility $facility): array => ['slug' => $facility->slug, 'name' => $facility->name])
            ->all());
    }
}
