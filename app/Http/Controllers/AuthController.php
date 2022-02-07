<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    function create(Request $request) {
        $data = $request->getContent();
        try {
            $data = json_decode($data);

            $user = $request->user;

            $student = new Student();
            $student->name = $user->name;
            $student->email = $user->email;
            $student->image_url = $user->avatar;
            $student->google_id = $user->id;

            $student->save();

            $response['response'] = "User created properly with id ".$student->id;
            Log::channel('success')->info('[app/Http/Controllers/AuthController.php] Student created', [
                'student' => $student,
            ]);
            $http_status_code = 201;
        } catch (\Throwable $th) {
            $response['response'] = "An error has occurred: ".$th->getMessage();
            $http_status_code = 500;
            Log::channel('errors')->info('[app/Http/Controllers/AuthController.php] An error has ocurred', [
                'error' => $th->getMessage(),
            ]);
        }
        return response()->json($response)->setStatusCode($http_status_code);
    }
}
