<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds defensive HTTP response headers to every response.
 *
 * Compatibility notes (why the policy looks the way it does):
 *  - The site loads Tailwind from cdn.tailwindcss.com (needs inline styles/scripts),
 *    Google Fonts, YouTube/Vimeo/Facebook/Instagram/TikTok embeds, placehold.co images,
 *    and admin-pasted ad code rendered inside sandboxed <iframe srcdoc> elements.
 *    srcdoc iframes INHERIT the parent page's CSP, so a strict script-src/frame-src would
 *    silently break ads and embeds.
 *  - Therefore the always-enforced CSP only contains directives that cannot break existing
 *    features (object-src, base-uri, frame-ancestors). The full, stricter policy is sent as
 *    Content-Security-Policy-Report-Only so you can watch the browser console, then switch it
 *    to enforcing with SECURITY_CSP_ENFORCE=true once nothing legitimate is reported.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = $response->headers;

        $headers->set('X-Content-Type-Options', 'nosniff');
        $headers->set('X-Frame-Options', 'SAMEORIGIN');
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');
        $headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');

        $enforced = "object-src 'none'; base-uri 'self'; frame-ancestors 'self'";
        $full = $this->fullPolicy();

        if (config('security.csp_enforce', false)) {
            $headers->set('Content-Security-Policy', $enforced.'; '.$full);
        } else {
            $headers->set('Content-Security-Policy', $enforced);
            $headers->set('Content-Security-Policy-Report-Only', $full);
        }

        // HSTS only makes sense (and is only honoured) over HTTPS in production.
        if ($request->isSecure() && config('security.hsts', false)) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Never let browsers/proxies cache authenticated admin or login pages.
        if ($request->is('admin', 'admin/*', 'login', 'register')) {
            $headers->set('Cache-Control', 'no-store, private');
            $headers->set('Pragma', 'no-cache');
        }

        return $response;
    }

    private function fullPolicy(): string
    {
        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://www.tiktok.com https://pagead2.googlesyndication.com https://www.googletagmanager.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.tailwindcss.com",
            "font-src 'self' https://fonts.gstatic.com data:",
            "img-src 'self' data: https:",
            "media-src 'self' https:",
            "frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com https://player.vimeo.com https://www.facebook.com https://www.instagram.com https://www.tiktok.com https://googleads.g.doubleclick.net",
            "connect-src 'self'",
            "form-action 'self'",
        ]);
    }
}
