<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class GetUserFromToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            if ($user = $request->header('token')) {
                $request->user = $user;
                return $next($request);
            } else {
                return response('No token', 412);
            }
        } catch (\Throwable $th) {
            return response($th->getMessage(), 500);
        }
    }
}
