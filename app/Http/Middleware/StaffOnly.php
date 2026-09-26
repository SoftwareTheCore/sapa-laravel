<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StaffOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->is_staff, 403, 'Halaman ini hanya untuk petugas SAPA.');

        return $next($request);
    }
}
