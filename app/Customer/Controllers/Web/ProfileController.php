<?php

namespace App\Customer\Controllers\Web;

use App\Customer\Concerns\ResolvesCustomerFromUser;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    use ResolvesCustomerFromUser;

    public function profile(): View
    {
        $this->authorizeCustomerPermission('customer.portal.profile.view');
        $customer = $this->resolveCustomer();

        return view('customer.profile', compact('customer'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $this->authorizeCustomerPermission('customer.portal.profile.update');
        $customer = $this->resolveCustomer();

        $validated = $request->validate([
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:1000',
        ]);

        $customer->update($validated);

        return back()->with('success', 'Profil başarıyla güncellendi.');
    }

    public function changePassword(Request $request): RedirectResponse
    {
        $this->authorizeCustomerPermission('customer.portal.profile.update');
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'Mevcut şifre gereklidir.',
            'password.required' => 'Yeni şifre gereklidir.',
            'password.min' => 'Yeni şifre en az 8 karakter olmalıdır.',
            'password.confirmed' => 'Yeni şifreler eşleşmiyor.',
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Mevcut şifre yanlış.']);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Şifre başarıyla değiştirildi.');
    }
}
