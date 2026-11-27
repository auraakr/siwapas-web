<?php

namespace App\Domain\Content\Services;

class SiteContact
{
    public function __construct(private SiteSettings $settings) {}

    /**
     * The public contact details from Pengaturan Situs (falling back to config/.env), without
     * empty values, plus ready-made WhatsApp and map links.
     *
     * @return array<string, string>
     */
    public function get(): array
    {
        /** @var array<string, string> $contact */
        $contact = array_filter($this->settings->contact(), fn (?string $value): bool => $value !== null);

        $whatsapp = $this->whatsappUrl($contact['whatsapp'] ?? null);

        if ($whatsapp !== null) {
            $contact['whatsapp_url'] = $whatsapp;
        }

        if (! isset($contact['maps_url']) && isset($contact['address'])) {
            $contact['maps_url'] = 'https://www.google.com/maps/search/?api=1&query='.urlencode($contact['address']);
        }

        return $contact;
    }

    public function whatsappUrl(?string $number): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $number);

        if ($digits === '') {
            return null;
        }

        return 'https://wa.me/'.(str_starts_with($digits, '0') ? '62'.substr($digits, 1) : $digits);
    }
}
