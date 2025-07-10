<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgencyAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if agency admin is authenticated
        if (!Auth::guard('agency')->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('agency.login');
        }

        $admin = Auth::guard('agency')->user();

        // Check if admin account is active
        if (!$admin->status) {
            Auth::guard('agency')->logout();
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Account deactivated.'], 403);
            }
            return redirect()->route('agency.login')->withErrors(['Your account has been deactivated.']);
        }

        // Check if agency is active
        if (!$admin->agency || !$admin->agency->status) {
            Auth::guard('agency')->logout();
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Agency deactivated.'], 403);
            }
            return redirect()->route('agency.login')->withErrors(['Your agency has been deactivated.']);
        }

        return $next($request);
    }
}