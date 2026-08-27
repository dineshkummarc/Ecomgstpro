<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Register in app/Http/Kernel.php under $routeMiddleware as:
 *   'single_device' => \App\Http\Middleware\EnsureSingleDeviceLogin::class,
 *
 * Apply alongside 'auth' on every protected route group:
 *   Route::middleware(['auth', 'single_device', 'subscription'])->group(...)
 *
 * How it works: on every successful login we generate a fresh random
 * token, save it on the user row (current_device_token) AND in that
 * browser's session. If someone logs in on a second device, the DB
 * token changes — so the first device's session token no longer
 * matches the DB, and this middleware force-logs it out.
 */
class EnsureSingleDeviceLogin
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        $sessionToken = $request->session()->get('device_token');

        if (!$sessionToken || $sessionToken !== $user->current_device_token) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('error', 'You have been logged out because this account was signed in on another device. Please log in again.');
        }

        return $next($request);
    }
}