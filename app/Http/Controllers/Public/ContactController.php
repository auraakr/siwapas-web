<?php

namespace App\Http\Controllers\Public;

use App\Domain\Content\Actions\StoreContactMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreContactMessageRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('site/contact', [
            'sent' => (bool) session('contact_sent'),
        ]);
    }

    public function store(StoreContactMessageRequest $request, StoreContactMessage $storeContactMessage): RedirectResponse
    {
        // The hidden "website" field is a trap for bots: pretend it worked, store nothing.
        if (! $request->filled('website')) {
            $storeContactMessage->handle($request->safe()->except('website'));
        }

        return to_route('contact.show')->with('contact_sent', true);
    }
}
