<?php

namespace App\Http\Controllers\Public;

use App\Domain\Booking\Actions\CreateReservation;
use App\Domain\Booking\DTOs\ReservationData;
use App\Domain\Booking\Exceptions\FacilityUnavailable;
use App\Domain\Catalog\Models\Facility;
use App\Domain\Payment\DTOs\CustomerData;
use App\Domain\Payment\Exceptions\PaymentNotPossible;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreReservationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Date;

class ReservationController extends Controller
{
    public function store(StoreReservationRequest $request, Facility $facility, CreateReservation $createReservation): RedirectResponse
    {
        abort_unless($facility->is_active, 404);

        try {
            $order = $createReservation->handle(new ReservationData(
                facilityId: $facility->id,
                checkIn: Date::parse($request->string('check_in')->toString()),
                checkOut: Date::parse($request->string('check_out')->toString()),
                guests: $request->integer('guests'),
                customer: new CustomerData(
                    name: $request->string('customer_name')->toString(),
                    phone: $request->string('customer_phone')->toString(),
                    email: $request->filled('customer_email') ? $request->string('customer_email')->toString() : null,
                    notes: $request->filled('notes') ? $request->string('notes')->toString() : null,
                ),
            ));
        } catch (FacilityUnavailable) {
            return back()->withInput()->withErrors([
                'check_in' => 'Fasilitas tidak tersedia pada tanggal tersebut atau data reservasi tidak valid.',
            ]);
        } catch (PaymentNotPossible) {
            return back()->withInput()->withErrors([
                'check_in' => 'Pembayaran belum dapat diproses saat ini. Silakan hubungi pengelola.',
            ]);
        }

        return to_route('orders.show', $order);
    }
}
