<?php

namespace App\Domain\Payment\Enums;

enum PaymentMethodType: string
{
    case Bank = 'bank';
    case Qris = 'qris';
}
