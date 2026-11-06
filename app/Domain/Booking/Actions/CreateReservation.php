<?php

namespace App\Domain\Booking\Actions;

use App\Domain\Booking\DTOs\ReservationData;
use App\Domain\Booking\Exceptions\FacilityUnavailable;
use App\Domain\Booking\Models\Reservation;
use App\Domain\Booking\Services\AvailabilityChecker;
use App\Domain\Booking\Services\PriceCalculator;
use App\Domain\Catalog\Models\Facility;
use App\Domain\Payment\Actions\CreateOrder;
use App\Domain\Payment\Enums\OrderKind;
use App\Domain\Payment\Models\Order;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class CreateReservation
{
    private const EXCLUSION_VIOLATION = '23P01';

    public function __construct(
        private AvailabilityChecker $availability,
        private PriceCalculator $prices,
        private CreateOrder $createOrder,
    ) {}

    /**
     * Availability is checked under a row lock on the facility; the PostgreSQL
     * exclusion constraint on reservations is the final safety net.
     */
    public function handle(ReservationData $data): Order
    {
        $today = now(config('siwapas.display_timezone'))->toDateString();

        if ($data->checkIn->toDateString() < $today) {
            throw FacilityUnavailable::invalidRequest('Check-in date is in the past.');
        }

        if ($data->checkOut->toDateString() <= $data->checkIn->toDateString()) {
            throw FacilityUnavailable::invalidRequest('Check-out must be after check-in.');
        }

        try {
            return DB::transaction(function () use ($data): Order {
                $facility = Facility::query()->whereKey($data->facilityId)->lockForUpdate()->firstOrFail();

                if ($facility->capacity !== null && $data->guests > $facility->capacity) {
                    throw FacilityUnavailable::invalidRequest('Guest count exceeds the facility capacity.');
                }

                if (! $this->availability->isAvailable($facility, $data->checkIn, $data->checkOut)) {
                    throw FacilityUnavailable::forDates();
                }

                $price = $this->prices->calculate($facility, $data->checkIn, $data->checkOut);

                $order = $this->createOrder->handle(OrderKind::Reservation, $data->customer, $price['total']);

                Reservation::create([
                    'order_id' => $order->id,
                    'facility_id' => $facility->id,
                    'check_in' => $data->checkIn->toDateString(),
                    'check_out' => $data->checkOut->toDateString(),
                    'guests' => $data->guests,
                    'price_breakdown' => $price['breakdown'],
                    'is_active' => true,
                ]);

                return $order;
            });
        } catch (QueryException $e) {
            if ($e->getCode() === self::EXCLUSION_VIOLATION) {
                throw FacilityUnavailable::forDates();
            }

            throw $e;
        }
    }
}
