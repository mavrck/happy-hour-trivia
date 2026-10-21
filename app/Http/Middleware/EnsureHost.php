<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHost
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->session()->get('host') === true, 403, 'Host login required.');

        return $next($request);
    }
}
