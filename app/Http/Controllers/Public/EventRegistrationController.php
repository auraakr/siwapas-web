<?php

namespace App\Http\Controllers\Public;

use App\Domain\Event\Actions\RegisterForEvent;
use App\Domain\Event\DTOs\EventRegistrationData;
use App\Domain\Event\Exceptions\EventRegistrationClosed;
use App\Domain\Event\Models\Event;
use App\Domain\Payment\DTOs\CustomerData;
use App\Domain\Payment\Exceptions\PaymentNotPossible;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreEventRegistrationRequest;
use Illuminate\Http\RedirectResponse;

class EventRegistrationController extends Controller
{
    public function store(StoreEventRegistrationRequest $request, Event $event, RegisterForEvent $registerForEvent): RedirectResponse
    {
        try {
            $registration = $registerForEvent->handle(new EventRegistrationData(
                eventId: $event->id,
                customer: new CustomerData(
                    name: $request->string('customer_name')->toString(),
                    phone: $request->string('customer_phone')->toString(),
                    email: $request->filled('customer_email') ? $request->string('customer_email')->toString() : null,
                ),
                participants: array_values($request->validated('participants')),
            ));
        } catch (EventRegistrationClosed) {
            return back()->withInput()->withErrors([
                'participants' => 'Pendaftaran belum dibuka, sudah ditutup, atau kuota tidak mencukupi.',
            ]);
        } catch (PaymentNotPossible) {
            return back()->withInput()->withErrors([
                'participants' => 'Pembayaran belum dapat diproses saat ini. Silakan hubungi pengelola.',
            ]);
        }

        if ($registration->order !== null) {
            return to_route('orders.show', $registration->order);
        }

        return to_route('events.show', $event)->with('registration_code', $registration->registration_code);
    }
}
