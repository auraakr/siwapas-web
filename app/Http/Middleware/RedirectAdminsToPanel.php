<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class RedirectAdminsToPanel
{
    /**
     * Admins work in the Filament panel; the /dashboard area is for registered visitors.
     * The panel is not an Inertia page, so Inertia requests get a full page visit instead
     * of rendering the panel inside a modal.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isAdmin()) {
            return Inertia::location('/admin');
        }

        return $next($request);
    }
}
