<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * Register in app/Http/Kernel.php under $routeMiddleware as:
 *   'subscription' => \App\Http\Middleware\CheckSubscriptionStatus::class,
 *
 * Then apply to protected routes, e.g.:
 *   Route::middleware(['auth', 'subscription'])->group(function () { ... });
 *
 * Allow-list the pricing/payment routes themselves so a locked-out
 * user can still reach the checkout page.
 */
class CheckSubscriptionStatus
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        // Auto-expire a trial that has run past its date but is still
        // marked 'trial' in the DB (keeps the status column accurate).
        if ($user->subscription_status === 'trial'
            && $user->trial_ends_at
            && Carbon::now()->greaterThan($user->trial_ends_at)) {
            $user->subscription_status = 'expired';
            $user->save();
        }

        // Auto-expire a paid plan that has run out.
        if ($user->subscription_status === 'active'
            && $user->subscription_ends_at
            && Carbon::now()->greaterThan($user->subscription_ends_at)) {
            $user->subscription_status = 'expired';
            $user->save();
        }

        if ($user->isLocked()) {
            return redirect()
                ->route('subscription.pricing')
                ->with('error', 'Your free trial has ended. Please choose a plan to continue using GSTpilot.');
        }

        return $next($request);
    }
}