<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Services\ResponseService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class FreelancerMiddlewar
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
     
        if ($request->user()->role != UserRole::Freelancer)
            return ResponseService::Response([], 401, "You are unauthorized");

        return $next($request);
    }
}
