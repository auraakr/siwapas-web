<?php

namespace App\Console\Commands;

use App\Domain\Payment\Actions\ExpireOverdueOrders;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('orders:expire')]
#[Description('Expire unpaid orders past their deadline and release their slots')]
class ExpireOverdueOrdersCommand extends Command
{
    public function handle(ExpireOverdueOrders $expireOverdueOrders): int
    {
        $this->info("Expired {$expireOverdueOrders->handle()} order(s).");

        return self::SUCCESS;
    }
}
