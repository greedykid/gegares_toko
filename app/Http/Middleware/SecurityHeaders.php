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
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(self "https://app.sandbox.midtrans.com" "https://app.midtrans.com"), clipboard-write=(self "https://app.sandbox.midtrans.com" "https://app.midtrans.com")');
        
        // Strict CSP Policy (Adjusted for local development, Lottie, and reCAPTCHA)
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com https://www.google.com https://www.gstatic.com https://app.sandbox.midtrans.com https://app.midtrans.com *:5173; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://unpkg.com *:5173; " .
               "font-src 'self' https://fonts.gstatic.com; " .
               "img-src 'self' data: https: blob:; " .
               "connect-src 'self' https://lite.koboillm.com https://api.mapbox.com https://unpkg.com https://www.google.com https://www.gstatic.com https://app.sandbox.midtrans.com https://app.midtrans.com ws://*:5173 *:5173; " .
               "frame-src 'self' https://www.google.com https://recaptcha.google.com https://app.sandbox.midtrans.com https://app.midtrans.com; " .
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
