<?php

namespace App\Domain\Content\Services;

use App\Domain\Content\Models\SiteSetting;
use Illuminate\Container\Attributes\Singleton;

/**
 * Site details the managers edit under Pengaturan Situs, stored in site_settings. Every value falls
 * back to config/siwapas.php (and so to .env) while it is left empty in the admin panel.
 */
#[Singleton]
class SiteSettings
{
    /**
     * Contact details and links, stored as "contact.<name>".
     */
    public const CONTACT_FIELDS = ['whatsapp', 'phone', 'email', 'address', 'hours', 'maps_url', 'instagram', 'facebook', 'youtube'];

    public const ADMISSION_KEY = 'visit.admission';

    public const EVENT_VENUES_KEY = 'events.venue_facility_ids';

    public const IMAGES_KEY = 'images';

    /**
     * @var array<string, mixed>|null
     */
    private ?array $stored = null;

    /**
     * Contact details with the admin's values over the config fallbacks; empty values are null.
     *
     * @return array<string, string|null>
     */
    public function contact(): array
    {
        $contact = [];

        foreach (self::CONTACT_FIELDS as $field) {
            $value = $this->stored()["contact.{$field}"] ?? null;

            if (! is_string($value) || trim($value) === '') {
                $value = config("siwapas.contact.{$field}");
            }

            $contact[$field] = is_string($value) && trim($value) !== '' ? trim($value) : null;
        }

        return $contact;
    }

    /**
     * The admission text per language, each falling back to config on its own.
     *
     * @return array{id: string, en: string}
     */
    public function admission(): array
    {
        $stored = $this->stored()[self::ADMISSION_KEY] ?? [];
        $fallback = (array) config('siwapas.visit.admission');

        return [
            'id' => $this->filledOr($stored['id'] ?? null, (string) ($fallback['id'] ?? '')),
            'en' => $this->filledOr($stored['en'] ?? null, (string) ($fallback['en'] ?? '')),
        ];
    }

    public function admissionFor(string $locale): string
    {
        $admission = $this->admission();

        return $locale === 'en' ? $admission['en'] : $admission['id'];
    }

    /**
     * Facilities the managers suggest for visitors who want to hold their own event.
     *
     * @return list<int>
     */
    public function eventVenueFacilityIds(): array
    {
        $ids = $this->stored()[self::EVENT_VENUES_KEY] ?? [];

        return array_values(array_map('intval', array_filter((array) $ids, 'is_numeric')));
    }

    /**
     * The row that carries the uploaded landing page photos.
     */
    public function imagesRecord(): SiteSetting
    {
        return SiteSetting::query()->firstOrCreate(['key' => self::IMAGES_KEY], ['value' => null]);
    }

    /**
     * URL of an uploaded landing photo (SiteSetting::HERO_IMAGE or ABOUT_IMAGE), if any.
     */
    public function imageUrl(string $collection): ?string
    {
        $record = SiteSetting::query()->with('media')->where('key', self::IMAGES_KEY)->first();

        return $record?->getFirstMediaUrl($collection) ?: null;
    }

    /**
     * Store the values from the settings page. Empty strings are kept as null so the fallback applies.
     *
     * @param  array{contact?: array<string, string|null>, admission?: array<string, string|null>, event_venue_facility_ids?: array<int, int|string>}  $values
     */
    public function save(array $values): void
    {
        foreach (self::CONTACT_FIELDS as $field) {
            if (array_key_exists($field, $values['contact'] ?? [])) {
                $this->put("contact.{$field}", $this->nullIfBlank($values['contact'][$field]));
            }
        }

        if (array_key_exists('admission', $values)) {
            $this->put(self::ADMISSION_KEY, [
                'id' => $this->nullIfBlank($values['admission']['id'] ?? null),
                'en' => $this->nullIfBlank($values['admission']['en'] ?? null),
            ]);
        }

        if (array_key_exists('event_venue_facility_ids', $values)) {
            $this->put(self::EVENT_VENUES_KEY, array_values(array_map('intval', (array) $values['event_venue_facility_ids'])));
        }

        $this->stored = null;
    }

    /**
     * Raw stored values, read once per request.
     *
     * @return array<string, mixed>
     */
    public function stored(): array
    {
        return $this->stored ??= SiteSetting::query()
            ->where('key', '!=', self::IMAGES_KEY)
            ->pluck('value', 'key')
            ->all();
    }

    private function put(string $key, mixed $value): void
    {
        SiteSetting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }

    private function nullIfBlank(?string $value): ?string
    {
        return $value === null || trim($value) === '' ? null : trim($value);
    }

    private function filledOr(mixed $value, string $fallback): string
    {
        return is_string($value) && trim($value) !== '' ? trim($value) : $fallback;
    }
}
