<?php

namespace App\Domain\Content\Services;

class MapLinks
{
    /**
     * What Google Maps should point at, taken from the managers' Maps link: coordinates ("@lat,lng"
     * or "q=lat,lng"), a search query ("q=" / "query=" / "destination="), or a place name
     * ("/place/Name/"). Short share links (maps.app.goo.gl) carry none of these, so the address is used.
     */
    public function target(?string $mapsUrl, ?string $address): ?string
    {
        $mapsUrl = trim((string) $mapsUrl);

        if ($mapsUrl !== '') {
            if (preg_match('/@(-?\d{1,2}\.\d+),(-?\d{1,3}\.\d+)/', $mapsUrl, $match)) {
                return "{$match[1]},{$match[2]}";
            }

            parse_str((string) parse_url($mapsUrl, PHP_URL_QUERY), $query);

            foreach (['q', 'query', 'destination'] as $parameter) {
                if (is_string($query[$parameter] ?? null) && trim($query[$parameter]) !== '') {
                    return trim($query[$parameter]);
                }
            }

            if (preg_match('#/place/([^/@]+)#', (string) parse_url($mapsUrl, PHP_URL_PATH), $match)) {
                return trim(str_replace('+', ' ', urldecode($match[1])));
            }
        }

        $address = trim((string) $address);

        return $address !== '' ? $address : null;
    }

    /**
     * An embeddable map (no API key needed), only when the managers set a Maps link.
     */
    public function embedUrl(?string $mapsUrl, ?string $address): ?string
    {
        if (trim((string) $mapsUrl) === '') {
            return null;
        }

        $target = $this->target($mapsUrl, $address);

        return $target === null ? null : 'https://maps.google.com/maps?q='.rawurlencode($target).'&z=15&output=embed';
    }

    /**
     * Opens Google Maps with directions to the village; falls back to the managers' own link.
     */
    public function directionsUrl(?string $mapsUrl, ?string $address): ?string
    {
        $target = $this->target($mapsUrl, $address);

        if ($target !== null) {
            return 'https://www.google.com/maps/dir/?api=1&destination='.rawurlencode($target);
        }

        return trim((string) $mapsUrl) !== '' ? trim((string) $mapsUrl) : null;
    }
}
