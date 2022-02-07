<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Google\Service\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    function create(Request $request) {
        try {
            $user = $request->user;

            if(!Student::where('email', $user->email)->first()) {
                $student = new Student();
                $student->name = $user->name;
                $student->email = $user->email;
                $student->image_url = $user->avatar;
                $student->google_id = $user->id;

                $student->save();

                $response['response'] = "User created properly with id ".$student->id;
                Log::channel('success')->info('[app/Http/Controllers/AuthController.php] Student created', [
                    'student_id' => $student->id,
                ]);
                $http_status_code = 201;
            } else {
                $response['response'] = "User already exists";
                $http_status_code = 400;
            }
        } catch (\Throwable $th) {
            $response['response'] = "An error has occurred: ".$th->getMessage();
            $http_status_code = 500;
            Log::channel('errors')->info('[app/Http/Controllers/AuthController.php : create] An error has ocurred', [
                'error' => $th->getMessage(),
            ]);
        }
        return response()->json($response)->setStatusCode($http_status_code);
    }
    function getCourses(Request $request) {
        try {
            $user = $request->user;

            if($user) {
                $client = new \Google\Client();
                $client->setAuthConfig('../laravel_id_secret.json');
                $client->addScope(\Google\Service\Classroom::CLASSROOM_COURSES);
                $client->setAccessToken($user->token);

                $service = new Classroom($client);
                $courses = $service->courses->listCourses()->courses;

                $response['response'] = $courses;
                $http_status_code = 200;
            } else {
                $response['response'] = "User doesn't exist";
                $http_status_code = 404;
            }
        } catch (\Throwable $th) {
            $response['response'] = "An error has occurred: ".$th->getMessage();
            $http_status_code = 500;
            Log::channel('errors')->info('[app/Http/Controllers/AuthController.php : getCourses] An error has ocurred', [
                'error' => $th->getMessage(),
            ]);
        }
        return response()->json($response)->setStatusCode($http_status_code);
    }
}
