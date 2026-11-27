<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The first profile pages were saved with Indonesian keys; the site reads the English keys the
     * admin panel offers today. Data only, no schema change, plain query builder (PostgreSQL, MySQL,
     * SQLite).
     *
     * Each entry: old key => [new key, new slug, original slug]. The original slugs were read from the
     * shared database before this migration was written (they equal the old keys), so down() puts
     * every row back exactly as it was.
     *
     * @var array<string, array{0: string, 1: string, 2: string}>
     */
    private const KEYS = [
        'identitas' => ['profile', 'profil-desa', 'identitas'],
        'sejarah' => ['history', 'sejarah', 'sejarah'],
        'visi-misi' => ['vision', 'visi-misi', 'visi-misi'],
        'pengelola' => ['management', 'pengelola', 'pengelola'],
        'potensi' => ['potential', 'potensi-desa', 'potensi'],
    ];

    public function up(): void
    {
        foreach (self::KEYS as $old => [$key, $slug]) {
            // Never overwrite: skip when a page already uses the new key or the new slug.
            $taken = DB::table('pages')
                ->where('key', $key)
                ->orWhere(fn ($query) => $query->where('slug', $slug)->where('key', '!=', $old))
                ->exists();

            if ($taken) {
                continue;
            }

            DB::table('pages')->where('key', $old)->update(['key' => $key, 'slug' => $slug]);
        }
    }

    public function down(): void
    {
        foreach (self::KEYS as $old => [$key, , $originalSlug]) {
            if (DB::table('pages')->where('key', $old)->exists()) {
                continue;
            }

            DB::table('pages')->where('key', $key)->update(['key' => $old, 'slug' => $originalSlug]);
        }
    }
};
