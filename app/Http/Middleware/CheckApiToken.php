<?php

namespace App\Http\Middleware;

use App\Models\Student;
use Closure;
use Illuminate\Http\Request;

class CheckApiToken
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
            if($request->header('api-token')){
                $token = $request->header('api-token');
                $student = Student::where('api_token', $token)->first();
                if($student){
                    $request->student = $student;
                    return $next($request);
                } else {
                    return response('Api token not valid', 401);
                }
            } else {
                return response('No api token', 401);
            }
        } catch (\Throwable $th) {
            return response($th->getMessage(), 500);
        }
    }
}
