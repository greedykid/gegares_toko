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

            // Resolve to a single $user across every branch. The old code only
            // assigned $user when matching by google_id, so a new signup or an
            // email-linked account left it null and the `$user->phone` check
            // below threw "property on null" — which the catch turned into a
            // generic "Google gagal" even though the account had just been
            // created and logged in.
            $user = User::where('google_id', $googleUser->id)->first();

            if ($user) {
                // Update avatar and name just in case
                $user->update([
                    'google_avatar' => $googleUser->avatar,
                    'name' => $googleUser->name,
                ]);
            } else {
                // Link a pre-existing password account with the same email
                // instead of creating a duplicate.
                $user = User::where('email', $googleUser->email)->first();

                if ($user) {
                    $user->update([
                        'google_id' => $googleUser->id,
                        'google_avatar' => $googleUser->avatar,
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
