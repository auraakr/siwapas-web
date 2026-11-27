<?php

namespace App\Domain\Content\Models;

use Carbon\CarbonImmutable;
use Database\Factories\SiteSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * One setting the managers can change in the admin panel, stored as JSON under a unique key.
 * The "images" row also holds the landing page photos in its media collections.
 *
 * @property int $id
 * @property string $key
 * @property mixed $value
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['key', 'value'])]
#[UseFactory(SiteSettingFactory::class)]
class SiteSetting extends Model implements HasMedia
{
    /** @use HasFactory<SiteSettingFactory> */
    use HasFactory, InteractsWithMedia;

    public const HERO_IMAGE = 'hero';

    public const ABOUT_IMAGE = 'about';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::HERO_IMAGE)->singleFile()->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
        $this->addMediaCollection(self::ABOUT_IMAGE)->singleFile()->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }
}
