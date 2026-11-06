<?php

namespace App\Domain\Content\Models;

use App\Domain\Content\Enums\GalleryItemType;
use Carbon\CarbonImmutable;
use Database\Factories\GalleryItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property GalleryItemType $type
 * @property string|null $title
 * @property string|null $youtube_url
 * @property string|null $category
 * @property int $sort_order
 * @property bool $is_published
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['type', 'title', 'youtube_url', 'category', 'sort_order', 'is_published'])]
#[UseFactory(GalleryItemFactory::class)]

class GalleryItem extends Model implements HasMedia
{
    /** @use HasFactory<GalleryItemFactory> */
    use HasFactory, HasTranslations, InteractsWithMedia;

    /**
     * @var list<string>
     */
    public array $translatable = ['title'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => GalleryItemType::class,
            'is_published' => 'boolean',
        ];
    }
}
