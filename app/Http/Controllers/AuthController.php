<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    function createUser(Request $request) {
        $data = $request->getContent();
        if($data) {
            try {
                $validator = Validator::make(json_decode($data, true), [
                    'token' => 'required|string',
                ]);

                if (!$validator->fails()) {
                    $data = json_decode($data);

                    $user = Socialite::driver('google')->userFromToken($data->token);

                    $student = new Student();
                    $student->name = $user->name;
                    $student->email = $user->email;
                    $student->image_url = $user->avatar;
                    $student->google_id = $user->id;

                    $student->save();

                    $response['response'] = "User created properly with id ".$student->id;
                    $http_status_code = 201;
                } else {
                    $response['response'] = $validator->errors()->first();
                    $http_status_code = 400;
                }
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
