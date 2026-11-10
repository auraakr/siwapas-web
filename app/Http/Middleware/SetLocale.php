<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * The site language is the visitor's choice (kept in the session) and defaults to Indonesian.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale');

        if (! in_array($locale, config('siwapas.locales'), true)) {
            $locale = config('siwapas.default_locale');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
