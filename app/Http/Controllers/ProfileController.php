<?php

namespace App\Http\Controllers;

use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return view('pages.settings', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'avatar' => ['nullable', 'image', 'max:2048'], // 2MB max
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = app(ImageOptimizer::class)->store($request->file('avatar'), 'avatars');
            $validated['avatar'] = $path;
        }

        $user->update($validated);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updateNotifications(Request $request)
    {
        $user = auth()->user();

        $user->update([
            'notification_settings' => [
                'order_updates' => $request->boolean('order_updates'),
                'promos' => $request->boolean('promos'),
                'newsletter' => $request->boolean('newsletter'),
            ]
        ]);

        return redirect()->to(route('settings.index') . '#notifications')->with('success', 'Pengaturan notifikasi berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        auth()->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Kata sandi berhasil diperbarui.');
    }

    public function showCompleteProfile()
    {
        $user = auth()->user();
        if ($user->phone) {
            return redirect()->route('home');
        }
        return view('pages.complete-profile', compact('user'));
    }

    public function updateCompleteProfile(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string', 'max:20'],
        ]);

        auth()->user()->update([
            'phone' => $request->phone,
        ]);

        // A guest who registered via Google mid-checkout completes their phone
        // here; honour the pending destination so they resume the order.
        return redirect()->intended(route('home'))->with('success', 'Profil berhasil dilengkapi. Selamat berbelanja!');
    }

    public function deleteAvatar()
    {
        $user = auth()->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->update(['avatar' => null]);
        }

        return back()->with('success', 'Foto profil berhasil dihapus.');
    }
}
