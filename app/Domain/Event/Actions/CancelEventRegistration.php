<?php

namespace App\Domain\Event\Actions;

use App\Domain\Event\Models\EventRegistration;
use App\Domain\Payment\Actions\CancelOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CancelEventRegistration
{
    public function __construct(private CancelOrder $cancelOrder) {}

    /**
     * Paid registrations are cancelled through their order; free ones just free their quota.
     */
    public function handle(EventRegistration $registration, User $admin, string $reason): EventRegistration
    {
        return DB::transaction(function () use ($registration, $admin, $reason): EventRegistration {
            if ($registration->order !== null) {
                $this->cancelOrder->handle($registration->order, $admin, $reason);
            } else {
                $registration->update(['is_active' => false]);
            }

            return $registration->refresh();
        });
    }
}
