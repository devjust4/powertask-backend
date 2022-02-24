<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Subject;
use App\Models\Task;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Http\Request;
use Google\Service\Classroom;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StudentsController extends Controller
{
    function loginRegister(Request $request) {
        try {
            $user = $request->user;

            if(!Student::where('email', $user->email)->first()) {
                $student = new Student();
                $student->name = $user->name;
                $student->email = $user->email;
                $student->image_url = $user->avatar;
                $student->google_id = $user->id;
                $student->api_token = Hash::make($user->id.$user->email);

                $student->save();

                $response['token'] = $student->api_token;
                $http_status_code = 201;
            } else {
                $student = Student::where('google_id', $user->id)->first();
                $response['token'] = $student->api_token;
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
    function initialDownload(Request $request) {
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

                    $subjects = $student->subjects()->get();

                    $events_array = array();

                    if(!$subjects->isEmpty()) {
                        if(!$student->subjects()->where('google_id', '<>', null)->get()->isEmpty()) {
                            $client = new \Google\Client();
                            $client->setAuthConfig('../laravel_id_secret.json');
                            $client->addScope('https://www.googleapis.com/auth/classroom.course-work.readonly');
                            $client->addScope('https://www.googleapis.com/auth/classroom.student-submissions.me.readonly');
                            $client->setAccessToken($user->token);

                            $service = new Classroom($client);
                            foreach ($subjects as $subject) {
                                if($subject->google_id != null) {
                                    $google_tasks = $service->courses_courseWork->listCoursesCourseWork($subject->google_id)->courseWork;
                                }
                            }

                            foreach ($google_tasks as $google_task) {
                                $submission = $service->courses_courseWork_studentSubmissions->listCoursesCourseWorkStudentSubmissions($google_task->courseId, $google_task->id);
                                $submission = $submission->studentSubmissions[0];

                                $task_ref = Task::where('google_id', $google_task->id)->first();
                                if(!$task_ref) {
                                    $task = new Task();
                                    $task->student_id = $request->student->id;
                                    $task->google_id = $google_task->id;

                                    $task->name = $google_task->title;
                                    if($google_task->description) $task->description = $google_task->description;
                                    if($google_task->dueDate) $task->date_handover = $google_task->dueDate->year.'-'.$google_task->dueDate->month.'-'.$google_task->dueDate->day;

                                    if($submission->assignedGrade) $task->mark = $submission->assignedGrade;

                                    switch ($submission->state) {
                                        case 'TURNED_IN':
                                            $task->completed = 1;
                                            break;
                                        case 'RETURNED':
                                            $task->completed = 1;
                                            break;
                                        default:
                                            $task->completed = 0;
                                            break;
                                    }
                                    $task->save();
                                } else {
                                    $task_ref->name = $google_task->title;
                                    if($google_task->description) $task_ref->description = $google_task->description;
                                    if($google_task->dueDate) $task_ref->date_handover = $google_task->dueDate->year.'-'.$google_task->dueDate->month.'-'.$google_task->dueDate->day;
                                    if($google_task->description) $task_ref->description = $google_task->description;

                                    if($submission->assignedGrade) $task_ref->mark = $submission->assignedGrade;

                                    switch ($submission->state) {
                                        case 'TURNED_IN':
                                            $task_ref->completed = 1;
                                            break;
                                        case 'RETURNED':
                                            $task_ref->completed = 1;
                                            break;
                                        default:
                                        $task_ref->completed = 0;
                                            break;
                                    }
                                    $task_ref->save();
                                }
                            }
                        }

                        $tasks = $student->tasks()->get();
                        if(!$tasks->isEmpty()) {
                            // $tasks_array = array();
                            foreach ($tasks as $task) {
                                if($task->subject()->where('deleted', false)->first() || $task->subject()->first() == null) {
                                    $task->subtasks = $task->subtasks()->get();
                                    $task->subject = $task->subject()->first();
                                    // array_push($tasks_array, $task);
                                }
                            }

                            $http_status_code = 200;
                        }
                    }

                    if(!$student->events()->get()->isEmpty()) {
                        $first_date = $student->events()->orderBy('date_start', 'asc')->first()->date_start;        //Recojo la primera fecha que haya
                        $begin = new DateTime($first_date);

                        $last_date = $student->events()->orderBy('date_end', 'desc')->first()->date_end;            //Recojo la ultima fecha que haya
                        $end = new DateTime($last_date);
                        $end->modify('+1 day');

                        $interval = DateInterval::createFromDateString('1 day');
                        $period = new DatePeriod($begin, $interval, $end);

                        foreach ($period as $date) {                //Recorro todo ese intervalo
                            $date = $date->format("Y-m-d");

                            $events = $student->events()->where('date_start', '<=', $date)->where('date_end', '>=', $date)->get();
                            if(!$events->isEmpty()) {
                                $array["vacation"] = array();
                                $array["exam"] = array();
                                $array["personal"] = array();

                                foreach ($events as $event) {
                                    if($event->all_day == true) $event->makeHidden(['time_start', 'time_end']);

                                    if($event->type == "vacation") array_push($array["vacation"], $event);
                                    if($event->type == "exam") array_push($array["exam"], $event);
                                    if($event->type == "personal") array_push($array["personal"], $event);
                                }
                                $events_array[strtotime($date)] = $array;
                            }
                        }
                    }

                    $http_status_code = 200;
                } else {
                    $response['response'] = "Student not found";
                    $http_status_code = 404;
                }
            } else {
                $response['response'] = "User doesn't exist";
                $http_status_code = 404;
            }


            if($http_status_code == 200) {
                $student->tasks = $tasks;
                $student->subjects = $student->subjects()->where('deleted', false)->get();

                $periods = $student->periods()->get();
                foreach($periods as $period) {
                    $period->blocks = $period->blocks()->get();
                }
                $student->periods = $periods;

                $student->sessions = $student->sessions()->get();
                $student->events = $events_array;

                $response['student'] = $student;
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


    function uploadImage(Request $request) {
        $base_url = "http://powertask.kurokiji.com/";

        try {
            $validatedData = $request->validate([
                'image' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            ]);

            $path = $base_url.$request->file('image')->store('public/images');

            $student = Student::find($request->student->id);
            $student->image_url = $path;
            $student->save();

            $response['response'] = "Image uploaded to:";
            $response['url'] = $path;
            $http_status_code = 200;
        } catch (\Throwable $th) {
            $response['response'] = "An error has occurred: ".$th->getMessage();
            $http_status_code = 500;
        }
        return response()->json($response)->setStatusCode($http_status_code);
    }
}
