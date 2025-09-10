<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized');
        }
        $user = Auth::user();
        if (!$user->is_admin && !($user->role && $user->role->name === 'Admin')) {
            abort(403, 'Unauthorized');
        }
        return $next($request);
    }
}
