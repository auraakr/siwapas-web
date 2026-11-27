<?php

namespace App\Http\Controllers\Public;

use App\Domain\Booking\Exceptions\BookingTooSoon;
use App\Domain\Booking\Models\Reservation;
use App\Domain\Booking\Services\AvailabilityChecker;
use App\Domain\Booking\Services\PaymentDeadline;
use App\Domain\Booking\Services\PriceCalculator;
use App\Domain\Catalog\Models\Facility;
use App\Domain\Payment\Services\DownPaymentCalculator;
use App\Domain\Payment\Services\PaymentSettings;
use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FacilityController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('public/facilities/index', [
            'facilities' => Facility::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->map(fn (Facility $facility): array => [
                    'slug' => $facility->slug,
                    'name' => $facility->name,
                    'description' => $facility->description,
                    'capacity' => $facility->capacity,
                    'base_price' => $facility->base_price,
                ]),
        ]);
    }

    public function show(Request $request, Facility $facility, PaymentSettings $paymentSettings): Response
    {
        abort_unless($facility->is_active, 404);

        $today = now(config('siwapas.display_timezone'))->toDateString();

        return Inertia::render('public/facilities/show', [
            'facility' => [
                'slug' => $facility->slug,
                'name' => $facility->name,
                'description' => $facility->description,
                'capacity' => $facility->capacity,
                'base_price' => $facility->base_price,
                'rules' => $facility->rules,
            ],
            'dp_percentage' => $paymentSettings->downPaymentPercentage(),
            'unavailable' => [
                ...Reservation::query()
                    ->where('facility_id', $facility->id)
                    ->where('is_active', true)
                    ->whereDate('check_out', '>', $today)
                    ->get()
                    ->map(fn (Reservation $reservation): array => [
                        'from' => $reservation->check_in->toDateString(),
                        'to' => $reservation->check_out->toDateString(),
                    ]),
            ],
            // Requested by the booking form (partial reload) once both dates are picked.
            'quote' => Inertia::optional(fn (): ?array => $this->quote($request, $facility, $paymentSettings)),
        ]);
    }

    /**
     * The price breakdown shown before submitting: full price, the down payment due now and the rest.
     *
     * @return array{nights: int, total: int, dp_percentage: int, dp_amount: int, remaining_amount: int, available: bool, too_soon: bool}|null
     */
    private function quote(Request $request, Facility $facility, PaymentSettings $paymentSettings): ?array
    {
        $checkIn = $request->query('check_in');
        $checkOut = $request->query('check_out');

        if (! is_string($checkIn) || ! is_string($checkOut)
            || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $checkIn) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $checkOut)
            || $checkOut <= $checkIn) {
            return null;
        }

        $from = CarbonImmutable::parse($checkIn);
        $to = CarbonImmutable::parse($checkOut);

        // Keep the preview cheap and bounded.
        if ($from->diffInDays($to) > 60) {
            return null;
        }

        $price = app(PriceCalculator::class)->calculate($facility, $from, $to);
        $percentage = $paymentSettings->downPaymentPercentage();
        $split = app(DownPaymentCalculator::class)->split($price['total'], $percentage);

        try {
            app(PaymentDeadline::class)->forCheckIn($checkIn);
            $tooSoon = false;
        } catch (BookingTooSoon) {
            $tooSoon = true;
        }

        return [
            'nights' => count($price['breakdown']),
            'total' => $price['total'],
            'dp_percentage' => $percentage,
            'dp_amount' => $split['dp_amount'],
            'remaining_amount' => $split['remaining_amount'],
            'available' => app(AvailabilityChecker::class)->isAvailable($facility, $from, $to),
            'too_soon' => $tooSoon,
        ];
    }
}
