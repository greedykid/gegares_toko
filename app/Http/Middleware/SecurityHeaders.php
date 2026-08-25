<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Security headers
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        // Note: X-XSS-Protection is deprecated and ignored by modern browsers;
        // the Content-Security-Policy below is the effective XSS defense.
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        // Snap & Buy takes a photo from the page itself, so the camera must be
        // allowed for this origin — `camera=()` disables it document-wide and the
        // browser then rejects getUserMedia no matter what the user permits.
        $response->headers->set('Permissions-Policy', 'camera=(self), microphone=(), geolocation=()');

        // Prevent browser caching for dynamic HTML pages and JSON APIs to avoid stale states (e.g. Livewire/cart/auth)
        $contentType = $response->headers->get('Content-Type');
        if ($contentType && (str_contains($contentType, 'text/html') || str_contains($contentType, 'application/json'))) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');
        }
        
        // Strict CSP Policy (Adjusted for local development, Lottie, and reCAPTCHA)
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com https://www.google.com https://www.gstatic.com *:5173; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://unpkg.com https://cdn.jsdelivr.net *:5173; " .
               "font-src 'self' https://fonts.gstatic.com; " .
               "img-src 'self' data: https: blob:; " .
               "connect-src 'self' https://lite.koboillm.com https://api.mapbox.com https://unpkg.com https://www.google.com https://www.gstatic.com ws://*:5173 *:5173; " .
               "frame-src 'self' https://www.google.com https://recaptcha.google.com; " .
               "object-src 'none'; " .
               "base-uri 'self'; " .
               "form-action 'self';";

        $response->headers->set('Content-Security-Policy', $csp);

        // Remove headers that leak information
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}
