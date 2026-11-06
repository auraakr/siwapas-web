<?php

namespace App\Domain\Event\Models;

use App\Domain\Event\Enums\EventStatus;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property string|null $description
 * @property string|null $organizer
 * @property string|null $location_name
 * @property string|null $address
 * @property string|null $latitude
 * @property string|null $longitude
 * @property Carbon $starts_at
 * @property Carbon|null $ends_at
 * @property int $fee
 * @property int|null $quota
 * @property string|null $requirements
 * @property Carbon|null $registration_opens_at
 * @property Carbon|null $registration_closes_at
 * @property EventStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['slug', 'title', 'description', 'organizer', 'location_name', 'address', 'latitude', 'longitude', 'starts_at', 'ends_at', 'fee', 'quota', 'requirements', 'registration_opens_at', 'registration_closes_at', 'status'])]
#[UseFactory(EventFactory::class)]

class Event extends Model implements HasMedia
{
    /** @use HasFactory<EventFactory> */
    use HasFactory, HasTranslations, InteractsWithMedia;

    /**
     * @var list<string>
     */
    public array $translatable = ['title', 'description', 'requirements'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'registration_opens_at' => 'datetime',
            'registration_closes_at' => 'datetime',
            'status' => EventStatus::class,
        ];
    }

    /**
     * @return HasMany<EventRegistration, $this>
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }
}
