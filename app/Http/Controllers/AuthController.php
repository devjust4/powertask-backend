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
        if($data) {
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
                $http_status_code = 201;

                Log::channel('errors')->info('Error en el usuario', [
                    'user_id' => $student->id,
                ]);
            } catch (\Throwable $th) {
                $response['response'] = "An error has occurred: ".$th->getMessage();
                $http_status_code = 500;
            }
            return response()->json($response)->setStatusCode($http_status_code);
        } else {
            return response(null, 412);     //Ran when received data is empty    (412: Precondition failed)
        }
    }
}
