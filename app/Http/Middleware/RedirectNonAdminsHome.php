<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectNonAdminsHome
{
    /**
     * Send signed-in visitors who are not admins back to the public home page.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isAdmin()) {
            return to_route('home');
        }

        return $next($request);
    }
}
