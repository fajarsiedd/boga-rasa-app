<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Penting: import Facade Auth
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticatedOrGuest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {        
        if (Auth::check()) {
            return redirect()->route('dashboard');
        } else {
            return redirect()->route('login');
        }

        // Ini adalah fallback, seharusnya tidak tercapai jika alur di atas sudah mencakup semua kondisi
        return $next($request);
    }
}