<?php

namespace App\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        if (! $request->user()) {
            abort(401, 'Unauthenticated.');
        }

        $user = $request->user();

        // Admin rolü her şeye erişebilsin
        if (method_exists($user, 'hasRole') && $user->hasRole('admin')) {
            return $next($request);
        }

        // User modelindeki hasPermission metodunu kullan (tüm roller için çalışır)
        if (method_exists($user, 'hasPermission')) {
            // Customer portal sayfaları için sadece müşteri portalı rolleri kontrol et
            $isCustomerPortal = str_starts_with($request->path(), 'customer/');
            $customerPortalOnly = $isCustomerPortal;
            
            // Debug: Kullanıcının izinlerini logla
            if ($isCustomerPortal) {
                $userPerms = $user->allPermissionCodes($customerPortalOnly);
                \Log::info('Permission Check Debug', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'path' => $request->path(),
                    'required' => $permissions,
                    'user_has' => $userPerms,
                    'customer_portal_only' => $customerPortalOnly,
                ]);
            }
            
            foreach ($permissions as $permission) {
                if ($user->hasPermission($permission, $customerPortalOnly)) {
                    return $next($request);
                }
            }
        } else {
            // Fallback: Eğer hasPermission metodu yoksa eski yöntemi kullan
            $user->loadMissing('roles.permissions');
            
            $userPermissions = [];

            // Kullanıcının rollerinden izinleri topla (sadece aktif rolleri)
            foreach ($user->roles as $role) {
                // Soft delete edilmiş rolleri atla
                if ($role->trashed()) {
                    continue;
                }

                // Sadece aktif izinleri al
                foreach ($role->permissions as $permission) {
                    if (! $permission->trashed()) {
                        $userPermissions[] = $permission->code;
                    }
                }
            }

            $userPermissions = array_unique($userPermissions);

            foreach ($permissions as $permission) {
                if (in_array($permission, $userPermissions, true)) {
                    return $next($request);
                }
            }
        }

        abort(403, 'Bu işlem için yetkiniz bulunmamaktadır.');
    }
}
