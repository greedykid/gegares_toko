<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckProfileCompletion
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && ! auth()->user()->phone) {
            // Match by ROUTE NAME, not URL path: the URLs are Indonesian
            // (/pengaturan/lengkapi-profil, /keluar) while the names stay English.
            // Matching the old English paths let the complete-profile page fail
            // its own whitelist and redirect to itself — an infinite loop.
            $allowed = $request->routeIs(
                'settings.complete-profile',
                'settings.update-complete-profile',
                'logout',
            );

            if (! $allowed) {
                return redirect()->route('settings.complete-profile');
            }
        }

        return $next($request);
    }
}
