<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureSAPLoggedIn
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->session()->has('sap_user') || !$request->session()->has('sap_pass')) {
            return redirect()->route('sap.login');
        }

        return $next($request);
    }
}
