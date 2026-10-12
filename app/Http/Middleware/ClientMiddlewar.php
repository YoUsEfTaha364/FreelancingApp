<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Services\ResponseService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClientMiddlewar
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()->role != UserRole::Client)
            return ResponseService::Response([], 401, "You are unauthorized");

        return $next($request);
    }
}
