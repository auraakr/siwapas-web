<?php

namespace App\Domain\Content\Models;

use App\Domain\Content\Enums\DestinationType;
use App\Domain\Content\Enums\PublishStatus;
use Carbon\CarbonImmutable;
use Database\Factories\DestinationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string $slug
 * @property DestinationType $type
 * @property string $name
 * @property string|null $summary
 * @property string|null $description
 * @property string|null $address
 * @property string|null $latitude
 * @property string|null $longitude
 * @property array<string, mixed>|null $opening_hours
 * @property array<int, string>|null $facilities
 * @property array<int, string>|null $rules
 * @property bool $requires_reservation
 * @property bool $is_featured
 * @property int $sort_order
 * @property PublishStatus $status
 * @property string|null $video_url
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['slug', 'type', 'name', 'summary', 'description', 'address', 'latitude', 'longitude', 'opening_hours', 'facilities', 'rules', 'requires_reservation', 'is_featured', 'sort_order', 'status', 'video_url'])]
#[UseFactory(DestinationFactory::class)]

class Destination extends Model implements HasMedia
{
    /** @use HasFactory<DestinationFactory> */
    use HasFactory, HasTranslations, InteractsWithMedia;

    /**
     * @var list<string>
     */
    public array $translatable = ['name', 'summary', 'description'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => DestinationType::class,
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'opening_hours' => 'array',
            'facilities' => 'array',
            'rules' => 'array',
            'requires_reservation' => 'boolean',
            'is_featured' => 'boolean',
            'status' => PublishStatus::class,
        ];
    }
}
