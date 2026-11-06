<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Catalog\Enums\PricingRuleType;
use Database\Factories\PricingRuleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property int $facility_id
 * @property string $name
 * @property PricingRuleType $type
 * @property array<int, int>|null $days_of_week
 * @property Carbon|null $date_from
 * @property Carbon|null $date_to
 * @property int $price
 * @property int $priority
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['facility_id', 'name', 'type', 'days_of_week', 'date_from', 'date_to', 'price', 'priority'])]
#[UseFactory(PricingRuleFactory::class)]

class PricingRule extends Model
{
    /** @use HasFactory<PricingRuleFactory> */
    use HasFactory, HasTranslations;

    /**
     * @var list<string>
     */
    public array $translatable = ['name'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => PricingRuleType::class,
            'days_of_week' => 'array',
            'date_from' => 'date',
            'date_to' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Facility, $this>
     */
    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }
}
