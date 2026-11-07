<?php

namespace App\Http\Middleware;

use Filament\Http\Middleware\Authenticate;

/**
 * The admin panel shares the site login instead of providing its own login page.
 */
class AuthenticateAdminPanel extends Authenticate
{
    protected function redirectTo($request): ?string
    {
        return route('login');
    }
}
