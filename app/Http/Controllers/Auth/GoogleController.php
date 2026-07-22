<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Resolve to a single $user up front — by google_id, then by email so
            // a password account gets linked rather than duplicated. The old code
            // only set $user on the google_id match, so a new signup or an
            // email-linked account left it null and the `$user->phone` check below
            // threw "property on null", which the catch turned into a generic
            // "Google gagal" even though the account had just been created.
            $user = User::where('google_id', $googleUser->id)->first()
                ?? User::where('email', $googleUser->email)->first();

            // Admins must use the admin door. LoginController already refuses to
            // sign an admin in through the storefront; the public Google button
            // must not be a way around that. Checked before any write so a stray
            // Google login never even links itself onto an admin account.
            if ($user && $user->isAdmin()) {
                return redirect()->route('login')->withErrors([
                    'google_error' => 'Akun admin harus masuk melalui halaman admin.',
                ]);
            }

            if ($user) {
                $user->update([
                    // ?: so an email-first account becomes linked while a
                    // google_id match keeps the id it already has.
                    'google_id' => $user->google_id ?: $googleUser->id,
                    'google_avatar' => $googleUser->avatar,
                    'name' => $googleUser->name,
                ]);
            } else {
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'google_avatar' => $googleUser->avatar,
                    'role' => 'user', // Default role
                    'password' => Hash::make(Str::random(16)), // Generate random password
                ]);
            }

            Auth::login($user);

            if (! $user->phone) {
                return redirect()->route('settings.complete-profile');
            }

            return redirect()->intended(route('home'));
            
        } catch (Exception $e) {
            return redirect()->route('login')->withErrors(['google_error' => 'Gagal masuk menggunakan Google. Silakan coba lagi.']);
        }
    }
}
