<?php

namespace App\Domain\Payment\Exceptions;

use RuntimeException;

class PaymentNotPossible extends RuntimeException
{
    public static function noPaymentMethod(): self
    {
        return new self('No active payment method is configured.');
    }

    public static function orderNotPayable(): self
    {
        return new self('This order can no longer receive a payment proof.');
    }
}
