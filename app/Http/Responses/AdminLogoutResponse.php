<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LogoutResponse;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class AdminLogoutResponse implements LogoutResponse
{
    /**
     * The panel has no login page, so signing out returns to the public site.
     */
    public function toResponse($request): RedirectResponse|Redirector
    {
        return redirect()->route('home');
    }
}
