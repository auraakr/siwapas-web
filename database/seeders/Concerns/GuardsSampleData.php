<?php

namespace Database\Seeders\Concerns;

/**
 * Sample data must never end up in a shared database by accident: the local .env points at the
 * shared Supabase project. Sample seeders only run when APP_ENV=local and the default connection is
 * not a Supabase host, unless SEED_SAMPLE_DATA=true is set explicitly.
 */
trait GuardsSampleData
{
    /**
     * Why sample data may not be seeded here, or null when it may.
     */
    public static function sampleDataBlockedReason(): ?string
    {
        if (config('siwapas.seed_sample_data')) {
            return null;
        }

        if (! app()->environment('local')) {
            return 'APP_ENV bukan "local" (sekarang: "'.app()->environment().'").';
        }

        $host = self::databaseHost();

        if ($host !== null && preg_match('/(^|\.)(supabase\.co|supabase\.com)$/i', $host)) {
            return "koneksi database mengarah ke Supabase ({$host}).";
        }

        return null;
    }

    /**
     * Seeders call this first; it explains the refusal on the console.
     */
    protected function sampleDataBlocked(): bool
    {
        $reason = self::sampleDataBlockedReason();

        if ($reason === null) {
            return false;
        }

        $this->command?->error('Data contoh TIDAK diisi: '.$reason);
        $this->command?->line('Data contoh hanya diisi di APP_ENV=local dengan database lokal (bukan Supabase).');
        $this->command?->line('Jika memang disengaja, jalankan ulang dengan SEED_SAMPLE_DATA=true.');

        return true;
    }

    /**
     * Host of the default connection, read from DB_URL when it is set (it overrides DB_HOST).
     */
    private static function databaseHost(): ?string
    {
        $connection = (array) config('database.connections.'.config('database.default'));
        $url = $connection['url'] ?? null;

        $host = is_string($url) && $url !== '' ? parse_url($url, PHP_URL_HOST) : ($connection['host'] ?? null);

        return is_string($host) && $host !== '' ? $host : null;
    }
}
