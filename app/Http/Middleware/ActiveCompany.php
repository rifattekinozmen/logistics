<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Schema;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ActiveCompany
{
    /**
     * Handle an incoming request.
     * Aktif firmayı bir kez yükleyip view ile paylaşır; layout/sidebar tekrar sorgu atmaz.
     *
     * @param  Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        $userCompanies = $this->userCompaniesForLayout($user);
        View::share('userCompaniesForLayout', $userCompanies);

        // Firma seçim sayfasında aktif firma zorunlu değil
        if ($request->routeIs('admin.companies.select')) {
            return $next($request);
        }

        $activeCompany = null;

        if (! session()->has('active_company_id')) {
            if ($userCompanies->isEmpty()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Aktif firma bulunamadı. Lütfen bir firma seçin.',
                    ], 403);
                }

                return redirect()->route('admin.companies.select');
            }

            // Kullanıcının firması var; giriş sonrası firma seçim modali gösterilsin (otomatik atama yapma)
            View::share('showCompanySelectModal', true);
            View::share('activeCompanyForLayout', null);

            return $next($request);
        }

        // Session'da firma var; yetki kontrolü
        $companyId = session('active_company_id');
        if (! $user->hasAccessToCompany($companyId)) {
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

        // Layout/sidebar ve navbar aynı veriyi tekrar sorgulamadan kullansın (sayfa yükleme hızı)
        if ($activeCompany) {
            View::share('activeCompanyForLayout', $activeCompany);
        }

        return $next($request);
    }

    /**
     * Kullanıcının firmalar listesini tek sorguda alıp layout'ta kullanılacak şekilde döndürür.
     *
     * @return \Illuminate\Support\Collection<int, \App\Models\Company>
     */
    private function userCompaniesForLayout($user): \Illuminate\Support\Collection
    {
        try {
            $query = $user->companies();
            if (Schema::hasColumn('user_companies', 'is_active')) {
                $query->wherePivot('is_active', true);
            } elseif (Schema::hasColumn('companies', 'status')) {
                $query->where('companies.status', 1);
            }

            return $query->get();
        } catch (Throwable) {
            return $user->companies()->get();
        }
    }
}
