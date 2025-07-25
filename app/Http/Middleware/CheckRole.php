<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return redirect('login');
        }
        
        if (!Auth::user()->hasRole($role)) {
            return abort(403, 'Unauthorized action.');
        }
        
        return $next($request);
    }
}