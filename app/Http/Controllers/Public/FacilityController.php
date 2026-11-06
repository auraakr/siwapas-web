<?php

namespace App\Http\Controllers\Public;

use App\Domain\Booking\Models\Reservation;
use App\Domain\Catalog\Models\Facility;
use App\Http\Controllers\Controller;
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

    public function show(Facility $facility): Response
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
        ]);
    }
}
