<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Role
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        if (! Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        if ($user->hasRole($role)) {
            return $next($request);
        } else {
            abort(404);
        }
    }
}
