<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Strict Transport Security (HSTS)
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        // Content Security Policy - skip in local to avoid blocking Vite HMR, blob:, workers, etc.
        if (! app()->environment('local')) {
            $viteHosts = '';
            if (Vite::isRunningHot()) {
                $hotUrl = trim((string) file_get_contents(Vite::hotFile()));
                if ($hotUrl !== '') {
                    $viteHosts = ' '.$hotUrl.' '.str_replace('http', 'ws', $hotUrl);
                    $altUrl = str_contains($hotUrl, 'localhost')
                        ? str_replace('localhost', '127.0.0.1', $hotUrl)
                        : str_replace('127.0.0.1', 'localhost', $hotUrl);
                    $viteHosts .= ' '.$altUrl.' '.str_replace('http', 'ws', $altUrl);
                }
            }

            $response->headers->set('Content-Security-Policy', implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' cdn.jsdelivr.net unpkg.com blob:{$viteHosts}",
                "style-src 'self' 'unsafe-inline' cdn.jsdelivr.net fonts.googleapis.com blob:{$viteHosts}",
                "font-src 'self' fonts.gstatic.com data:",
                "img-src 'self' data: https: blob:",
                "connect-src 'self' blob: wss:{$viteHosts}",
            ]));
        }

        // X-Frame-Options
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // X-Content-Type-Options
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // X-XSS-Protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy
        $response->headers->set('Permissions-Policy', implode(', ', [
            'geolocation=(self)',
            'camera=()',
            'microphone=()',
        ]));

        return $response;
    }
}
