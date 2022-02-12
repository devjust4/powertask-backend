<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Subject;
use Google\Service\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ClassroomController extends Controller
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
    function getSubjects(Request $request) {
        try {
            $user = $request->user;

            if($user) {
                $student = Student::where('google_id', $user->id)->first();

                if($student) {
                    $client = new \Google\Client();
                    $client->setAuthConfig('../laravel_id_secret.json');
                    $client->addScope(\Google\Service\Classroom::CLASSROOM_COURSES);
                    $client->setAccessToken($user->token);

                    $service = new Classroom($client);
                    $courses = $service->courses->listCourses()->courses;

                    foreach ($courses as $course) {
                        if(!Subject::where('google_id', $course->id)->where('student_id', $student->id)->first()) {
                            $subject = new Subject();
                            $subject->name = $course->name;
                            $subject->google_id = $course->id;
                            $subject->student_id = $student->id;
                            $subject->save();
                        }
                    }
                    $response['response'] = $student->subjects;
                    $http_status_code = 200;
                } else {
                    $response['response'] = "Student not found";
                    $http_status_code = 404;
                }
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
    function editSubject(Request $request, $id) {
        $data = $request->getContent();
        if($data) {
            try {
                $validator = Validator::make(json_decode($data, true), [
                    'name' => 'string',
                    'color' => 'string',
                ]);

                if (!$validator->fails()) {
                    $data = json_decode($data);

                    if($subject = Subject::find($id)) {
                        if(isset($data->name)) $subject->name = $data->name;
                        if(isset($data->color)) $subject->color = $data->color;

                        $subject->save();

                        $response['response'] = "Subject edited properly";
                        $http_status_code = 200;
                    } else {
                        $response['response'] = "Subject by that id doesn't exist.";
                        $http_status_code = 404;
                    }
                } else {
                    $response = ['status'=>0, 'msg'=>$validator->errors()->first()];
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
