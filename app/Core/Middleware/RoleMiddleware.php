<?php

namespace App\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            abort(401, 'Unauthenticated.');
        }

        $user = $request->user();

        // Admin rolü her şeye erişebilsin
        if (method_exists($user, 'hasRole') && $user->hasRole('admin')) {
            return $next($request);
        }

        // Rolleri yükle ve sadece aktif rolleri filtrele
        $user->loadMissing('roles');
        $userRoles = $user->roles
            ->whereNull('deleted_at')
            ->pluck('name')
            ->toArray();

        foreach ($roles as $role) {
            if (in_array($role, $userRoles, true)) {
                return $next($request);
            }
        }

        abort(403, 'Bu işlem için yetkiniz bulunmamaktadır.');
    }
}
