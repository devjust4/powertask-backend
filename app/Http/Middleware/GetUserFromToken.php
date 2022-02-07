<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
            $user = $request->header('token');
            if ($user) {
                $request->user = $user;
                return $next($request);
            } else {
                Log::channel('errors')->info('Error con el token');
                return response('No token', 412);
            }
        } catch (\Throwable $th) {
            return response($th->getMessage(), 500);
        }
    }
}
