<?php

namespace App\Domain\Content\Models;

use Carbon\CarbonImmutable;
use Database\Factories\PosterFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

/**
 * A promotional poster shown in the landing page carousel. The image lives in the "default" media collection.
 *
 * @property int $id
 * @property string $title
 * @property string|null $alt
 * @property int $sort_order
 * @property bool $is_published
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['title', 'alt', 'sort_order', 'is_published'])]
#[UseFactory(PosterFactory::class)]
class Poster extends Model implements HasMedia
{
    /** @use HasFactory<PosterFactory> */
    use HasFactory, HasTranslations, InteractsWithMedia;

    /**
     * @var list<string>
     */
    public array $translatable = ['title', 'alt'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_published' => 'boolean',
        ];
    }
}
