<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckIfAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // Check if the user is an admin
        if (auth()->check() && auth()->user()->is_admin !== 1) {
            // Redirect non-admin users to a different page, e.g., home
            return redirect('/');
        }

        return $next($request);
    }
}

