<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ActiveCompany
{
    /**
     * Handle an incoming request.
     * Aktif firmayı bir kez yükleyip view ile paylaşır; layout/sidebar tekrar sorgu atmaz.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        // Firma seçim sayfasında aktif firma zorunlu değil
        if ($request->routeIs('admin.companies.select')) {
            return $next($request);
        }

        $user = Auth::user();
        $activeCompany = null;

        // Eğer session'da aktif firma yoksa, default firmayı set et
        if (! session()->has('active_company_id')) {
            $activeCompany = $user->activeCompany();

            if (! $activeCompany) {
                // Kullanıcının hiç firması yoksa, firma seçim sayfasına yönlendir
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Aktif firma bulunamadı. Lütfen bir firma seçin.',
                    ], 403);
                }

                return redirect()->route('admin.companies.select');
            }
        } else {
            // Session'da firma var ama kullanıcının yetkisi var mı kontrol et
            $companyId = session('active_company_id');

            if (! $user->hasAccessToCompany($companyId)) {
                // Yetkisiz firma erişimi, default firmaya yönlendir
                $activeCompany = $user->activeCompany();

                if ($activeCompany) {
                    session(['active_company_id' => $activeCompany->id]);
                } else {
                    session()->forget('active_company_id');

                    if ($request->expectsJson()) {
                        return response()->json([
                            'message' => 'Bu firmaya erişim yetkiniz bulunmamaktadır.',
                        ], 403);
                    }

                    return redirect()->route('admin.companies.select');
                }
            } else {
                $activeCompany = Company::withoutGlobalScopes()->find($companyId);
            }
        }

        // Layout/sidebar aynı firmayı tekrar sorgulamadan kullansın
        if ($activeCompany) {
            View::share('activeCompanyForLayout', $activeCompany);
        }

        return $next($request);
    }
}
