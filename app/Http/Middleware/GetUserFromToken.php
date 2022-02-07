<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

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
            $token = $request->header('token');
            if($token) {
                $user = Socialite::driver('google')->userFromToken($request->header('token'));
                if ($user) {
                    $request->user = $user;
                    return $next($request);
                } else {
                    Log::channel('errors')->info('[app/Http/Middleware/GetUserFromToken.php] Token not valid');
                    return response('Token not valid', 401);
                }
            } else {
                Log::channel('errors')->info('[app/Http/Middleware/GetUserFromToken.php] No token');
                return response('No token', 412);
            }
        } catch (\Throwable $th) {
            return response($th->getMessage(), 500);
        }
    }
}
