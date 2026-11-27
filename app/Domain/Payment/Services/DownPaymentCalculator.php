<?php

namespace App\Domain\Payment\Services;

use InvalidArgumentException;

class DownPaymentCalculator
{
    /**
     * Split a full price into the down payment due now and the rest due later. The down payment is
     * rounded up to the next whole rupiah, so the two parts always add up to the full price.
     *
     * @return array{dp_amount: int, remaining_amount: int}
     */
    public function split(int $fullPrice, int $percentage): array
    {
        if ($fullPrice < 0 || $percentage < 1 || $percentage > 100) {
            throw new InvalidArgumentException('The price must not be negative and the percentage must be 1 to 100.');
        }

        $downPayment = intdiv($fullPrice * $percentage + 99, 100);

        return [
            'dp_amount' => $downPayment,
            'remaining_amount' => $fullPrice - $downPayment,
        ];
    }
}
