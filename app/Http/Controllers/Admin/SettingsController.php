<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Display the user's settings.
     */
    public function show(): View
    {
        $user = Auth::user()->load(['roles.permissions']);

        return view('admin.settings.show', compact('user'));
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'Mevcut şifre zorunludur.',
            'password.required' => 'Yeni şifre zorunludur.',
            'password.min' => 'Yeni şifre en az 8 karakter olmalıdır.',
            'password.confirmed' => 'Şifre onayı eşleşmiyor.',
        ]);

        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Mevcut şifre yanlış.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.settings.show')
            ->with('success', 'Şifreniz başarıyla güncellendi.');
    }
}
