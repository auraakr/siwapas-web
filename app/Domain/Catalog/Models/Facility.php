<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Booking\Models\Reservation;
use App\Domain\Catalog\Enums\RentalUnit;
use Database\Factories\FacilityFactory;
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
 * @property string $name
 * @property string|null $description
 * @property int|null $capacity
 * @property RentalUnit $unit
 * @property int $base_price
 * @property array<int, string>|null $rules
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['slug', 'name', 'description', 'capacity', 'unit', 'base_price', 'rules', 'is_active', 'sort_order'])]
#[UseFactory(FacilityFactory::class)]

class Facility extends Model implements HasMedia
{
    /** @use HasFactory<FacilityFactory> */
    use HasFactory, HasTranslations, InteractsWithMedia;

    /**
     * @var list<string>
     */
    public array $translatable = ['name', 'description', 'rules'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit' => RentalUnit::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<PricingRule, $this>
     */
    public function pricingRules(): HasMany
    {
        return $this->hasMany(PricingRule::class);
    }

    /**
     * @return HasMany<BlockedDate, $this>
     */
    public function blockedDates(): HasMany
    {
        return $this->hasMany(BlockedDate::class);
    }

    /**
     * @return HasMany<Reservation, $this>
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
