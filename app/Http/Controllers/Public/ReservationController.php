<?php

namespace App\Http\Controllers\Public;

use App\Domain\Booking\Actions\CreateReservation;
use App\Domain\Booking\DTOs\ReservationData;
use App\Domain\Booking\Exceptions\BookingTooSoon;
use App\Domain\Booking\Exceptions\FacilityUnavailable;
use App\Domain\Catalog\Models\Facility;
use App\Domain\Content\Services\SiteSettings;
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

        $english = app()->getLocale() === 'en';

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
        } catch (BookingTooSoon) {
            // Same-day and very short-notice rentals are arranged with the managers directly.
            $whatsapp = app(SiteSettings::class)->contact()['whatsapp'];
            $contact = filled($whatsapp) ? " {$whatsapp}" : '';

            return back()->withInput()->withErrors([
                'check_in' => $english
                    ? "This check-in date is too close: there is not enough time left to pay the down payment online. Please choose a later date, or contact the managers via WhatsApp{$contact} for a short-notice rental."
                    : "Tanggal check-in terlalu dekat sehingga waktu untuk membayar DP secara online tidak cukup. Silakan pilih tanggal lain, atau hubungi pengelola via WhatsApp{$contact} untuk sewa mendadak.",
            ]);
        } catch (FacilityUnavailable) {
            return back()->withInput()->withErrors([
                'check_in' => $english
                    ? 'The facility is not available on those dates, or the booking details are not valid.'
                    : 'Fasilitas tidak tersedia pada tanggal tersebut atau data reservasi tidak valid.',
            ]);
        } catch (PaymentNotPossible) {
            return back()->withInput()->withErrors([
                'check_in' => $english
                    ? 'Payments cannot be processed right now. Please contact the managers.'
                    : 'Pembayaran belum dapat diproses saat ini. Silakan hubungi pengelola.',
            ]);
        }

        return to_route('orders.show', $order);
    }
}
