<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Auth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Check if user is logged in and has 'admin' role
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return redirect()->route('unauthorized'); // Redirect if not an admin
        }
        
        return $next($request); // Allow request to proceed
    }
}
