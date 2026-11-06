<?php

namespace App\Domain\Content\Models;

use App\Domain\Content\Enums\ArticleCategory;
use App\Domain\Content\Enums\PublishStatus;
use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property string|null $excerpt
 * @property string|null $body
 * @property ArticleCategory $category
 * @property Carbon|null $published_at
 * @property string|null $author_name
 * @property PublishStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['slug', 'title', 'excerpt', 'body', 'category', 'published_at', 'author_name', 'status'])]
#[UseFactory(ArticleFactory::class)]

class Article extends Model implements HasMedia
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory, HasTranslations, InteractsWithMedia;

    /**
     * @var list<string>
     */
    public array $translatable = ['title', 'excerpt', 'body'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => ArticleCategory::class,
            'published_at' => 'datetime',
            'status' => PublishStatus::class,
        ];
    }
}
