<?php

namespace App\Domain\Content\Enums;

enum ArticleCategory: string
{
    case Culture = 'culture';
    case VillagePotential = 'village_potential';
    case Community = 'community';
}
