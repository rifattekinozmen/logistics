<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        // Eğer kullanıcı zaten giriş yapmışsa, rolüne göre yönlendir
        if (Auth::check()) {
            $user = Auth::user();
            
            // Müşteri portalı rolleri kontrolü
            $hasCustomerRole = $user->hasRole('customer') || $user->hasRole('customer_user') || $user->hasRole('customer_viewer');
            $hasAdminRole = $user->hasRole('admin') || $user->hasRole('company_admin');
            
            if ($hasCustomerRole && !$hasAdminRole) {
                return redirect()->route('customer.dashboard');
            }
            
            return redirect()->route('admin.dashboard');
        }
        
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();
        
        // Kullanıcının rollerini kontrol et
        $hasCustomerRole = $user->hasRole('customer') || $user->hasRole('customer_user') || $user->hasRole('customer_viewer');
        $hasAdminRole = $user->hasRole('admin') || $user->hasRole('company_admin');
        $portal = $request->input('portal', null);
        
        // Portal seçilmişse kontrol et
        if ($portal === 'admin') {
            if (!$hasAdminRole) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Admin paneline sadece yönetici kullanıcılar giriş yapabilir. Lütfen müşteri portalını kullanın.',
                ])->withInput($request->only('email'));
            }
            return redirect()->intended(route('admin.dashboard'));
        }
        
        if ($portal === 'customer') {
            if (!$hasCustomerRole) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Müşteri portalına sadece müşteri kullanıcılar giriş yapabilir. Lütfen admin panelini kullanın.',
                ])->withInput($request->only('email'));
            }
            return redirect()->intended(route('customer.dashboard'));
        }

        // Portal seçilmemişse, kullanıcının rollerine göre otomatik yönlendir
        // Önce müşteri portalı rolleri kontrol et (sadece müşteri rolü varsa)
        if ($hasCustomerRole && !$hasAdminRole) {
            return redirect()->intended(route('customer.dashboard'));
        }
        
        // Admin rolü varsa admin dashboard'a yönlendir
        if ($hasAdminRole) {
            return redirect()->intended(route('admin.dashboard'));
        }

        // Hiç rol yoksa veya belirsizse admin dashboard'a yönlendir (varsayılan)
        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
