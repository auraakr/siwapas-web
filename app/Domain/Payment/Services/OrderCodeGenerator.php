<?php

namespace App\Domain\Payment\Services;

use App\Domain\Payment\Models\Order;

class OrderCodeGenerator
{
    /**
     * Next code of the form SWP-YYMM-NNNN, numbered per month.
     */
    public function next(): string
    {
        $prefix = 'SWP-'.now(config('siwapas.display_timezone'))->format('ym').'-';

        $last = Order::query()
            ->where('code', 'like', $prefix.'%')
            ->orderByDesc('code')
            ->value('code');

        $sequence = $last === null ? 1 : ((int) substr($last, strlen($prefix))) + 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
