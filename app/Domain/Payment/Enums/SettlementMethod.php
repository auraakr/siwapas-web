<?php

namespace App\Domain\Payment\Enums;

enum SettlementMethod: string
{
    case Transfer = 'transfer';
    case Cash = 'cash';
}
