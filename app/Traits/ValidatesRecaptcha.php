<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;

trait ValidatesRecaptcha
{
    /**
     * Validate reCAPTCHA v3 token.
     *
     * @param string|null $token
     * @return bool
     */
    protected function validateRecaptcha(?string $token): bool
    {
        if (config('app.env') === 'local' || !config('services.recaptcha.site') || !config('services.recaptcha.secret')) {
            return true; 
        }

        if (!$token) {
            return false;
        }

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('services.recaptcha.secret'),
            'response' => $token,
            'remoteip' => request()->ip(),
        ]);

        return $response->json('success') === true && $response->json('score') >= 0.5;
    }
}
