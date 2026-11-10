<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Median launch URL: https://<your-host>/?app=1
 *
 * App mode is presentation-only. It does not grant access or alter authorization.
 * The choice persists in the web session across internal links and authentication.
 */
class RememberMedianAppMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET') && $request->query('app') === '1') {
            $request->session()->put('median_app_mode', true);
        } elseif ($request->isMethod('GET') && $request->query('app') === '0') {
            $request->session()->forget('median_app_mode');
        }

        return $next($request);
    }
}
