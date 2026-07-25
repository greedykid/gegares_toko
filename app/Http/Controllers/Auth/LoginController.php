<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use \App\Traits\ValidatesRecaptcha;
    use \App\Traits\HandlesPendingWishlist;

    public function showLoginForm()
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'g-recaptcha-response' => 'required',
        ]);

        if (!$this->validateRecaptcha($request->input('g-recaptcha-response'))) {
            return back()->withErrors([
                'email' => 'Verifikasi bot gagal. Silakan coba lagi.',
            ])->onlyInput('email');
        }

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            if (Auth::user()->isAdmin()) {
                Auth::logout();
                $request->session()->invalidate();
                return back()->withErrors([
                    'email' => 'Akses ditolak. Silakan login melalui pintu masuk admin.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();
            $this->handlePendingWishlist();
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
