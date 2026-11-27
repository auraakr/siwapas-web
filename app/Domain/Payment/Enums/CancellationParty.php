<?php

namespace App\Domain\Payment\Enums;

/**
 * Who asked for a cancellation. A tenant who cancels forfeits the down payment; a cancellation
 * by the managers needs a note on how it was settled with the tenant.
 */
enum CancellationParty: string
{
    case Tenant = 'tenant';
    case Manager = 'manager';
}
