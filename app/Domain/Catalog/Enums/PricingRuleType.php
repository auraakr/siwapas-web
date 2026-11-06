<?php

namespace App\Domain\Catalog\Enums;

enum PricingRuleType: string
{
    case Weekday = 'weekday';
    case Weekend = 'weekend';
    case Holiday = 'holiday';
    case Season = 'season';
}
