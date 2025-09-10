<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$permissions
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        $user = $request->user();
        
        // Super admin bypasses all permission checks
        if ($user->hasRole('super-admin')) {
            return $next($request);
        }

        foreach ($permissions as $permission) {
            if ($user->hasPermissionTo($permission)) {
                return $next($request);
            }
        }

        // Log unauthorized access attempt
        Log::warning('Unauthorized access attempt', [
            'user_id' => $user->id,
            'email' => $user->email,
            'permissions_required' => $permissions,
            'user_permissions' => $user->getAllPermissions()->pluck('name'),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthorized: You do not have the required permissions.'], 403);
        }

        return redirect()->route('home')
            ->with('error', 'You do not have permission to access this page.');
    }
}