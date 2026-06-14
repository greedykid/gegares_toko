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
            
            $user = User::where('google_id', $googleUser->id)->first();
            
            if ($user) {
                // Update avatar and name just in case
                $user->update([
                    'google_avatar' => $googleUser->avatar,
                    'name' => $googleUser->name
                ]);
                Auth::login($user);
            } else {
                // Check if user exists with the same email
                $userByEmail = User::where('email', $googleUser->email)->first();
                
                if ($userByEmail) {
                    $userByEmail->update([
                        'google_id' => $googleUser->id,
                        'google_avatar' => $googleUser->avatar
                    ]);
                    Auth::login($userByEmail);
                } else {
                    $newUser = User::create([
                        'name' => $googleUser->name,
                        'email' => $googleUser->email,
                        'google_id' => $googleUser->id,
                        'google_avatar' => $googleUser->avatar,
                        'role' => 'user', // Default role
                        'password' => Hash::make(Str::random(16)) // Generate random password
                    ]);
                    
                    Auth::login($newUser);
                }
            }
            
            if (!$user->phone) {
                return redirect()->route('settings.complete-profile');
            }
            
            return redirect()->intended(route('home'));
            
        } catch (Exception $e) {
            return redirect()->route('login')->withErrors(['google_error' => 'Gagal masuk menggunakan Google. Silakan coba lagi.']);
        }
    }
}
