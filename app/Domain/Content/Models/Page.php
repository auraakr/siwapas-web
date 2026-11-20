<?php

namespace App\Domain\Content\Models;

use App\Domain\Content\Enums\PublishStatus;
use Carbon\CarbonImmutable;
use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string $key
 * @property string $slug
 * @property string $title
 * @property string|null $body
 * @property string|null $seo_title
 * @property string|null $seo_description
 * @property PublishStatus $status
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['key', 'slug', 'title', 'body', 'seo_title', 'seo_description', 'status'])]
#[UseFactory(PageFactory::class)]

class Page extends Model implements HasMedia
{
    /** @use HasFactory<PageFactory> */
    use HasFactory, HasTranslations, InteractsWithMedia;

    /**
     * @var list<string>
     */
    public array $translatable = ['title', 'body', 'seo_title', 'seo_description'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PublishStatus::class,
        ];
    }
}
