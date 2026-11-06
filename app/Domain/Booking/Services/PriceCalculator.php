<?php

namespace App\Domain\Booking\Services;

use App\Domain\Catalog\Enums\PricingRuleType;
use App\Domain\Catalog\Models\Facility;
use App\Domain\Catalog\Models\PricingRule;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class PriceCalculator
{
    /**
     * Price each night of the stay. The matching rule with the highest priority wins;
     * without a matching rule the facility base price applies.
     *
     * @return array{total: int, breakdown: list<array{date: string, price: int, rule: string|null}>}
     */
    public function calculate(Facility $facility, CarbonInterface $checkIn, CarbonInterface $checkOut): array
    {
        /** @var Collection<int, PricingRule> $rules */
        $rules = $facility->pricingRules()->orderByDesc('priority')->orderBy('id')->get();

        $breakdown = [];
        $total = 0;

        $end = $checkOut->toImmutable()->startOfDay();

        for ($night = $checkIn->toImmutable()->startOfDay(); $night->lt($end); $night = $night->addDay()) {
            $rule = $rules->first(fn (PricingRule $candidate): bool => $this->matches($candidate, $night));
            $price = $rule->price ?? $facility->base_price;

            $breakdown[] = [
                'date' => $night->toDateString(),
                'price' => $price,
                'rule' => $rule?->getTranslation('name', 'id'),
            ];
            $total += $price;
        }

        return ['total' => $total, 'breakdown' => $breakdown];
    }

    private function matches(PricingRule $rule, CarbonInterface $night): bool
    {
        if ($rule->date_from !== null && $night->lt($rule->date_from->startOfDay())) {
            return false;
        }

        if ($rule->date_to !== null && $night->gt($rule->date_to->startOfDay())) {
            return false;
        }

        $days = $rule->days_of_week ?? [];

        if ($days === []) {
            return match ($rule->type) {
                PricingRuleType::Weekday => $night->isWeekday(),
                PricingRuleType::Weekend => $night->isWeekend(),
                default => true,
            };
        }

        return in_array($night->dayOfWeekIso, $days, true);
    }
}
