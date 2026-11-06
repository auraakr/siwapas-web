<?php

namespace App\Domain\Payment\Exceptions;

use App\Domain\Payment\Enums\OrderStatus;
use RuntimeException;

class InvalidOrderTransition extends RuntimeException
{
    public static function between(OrderStatus $from, OrderStatus $to): self
    {
        return new self("Order cannot move from {$from->value} to {$to->value}.");
    }
}
