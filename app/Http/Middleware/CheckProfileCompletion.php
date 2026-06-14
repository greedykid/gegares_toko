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
        if (auth()->check() && !auth()->user()->phone) {
            if (!$request->is('settings/complete-profile*') && !$request->is('logout')) {
                return redirect()->route('settings.complete-profile');
            }
        }

        return $next($request);
    }
}
