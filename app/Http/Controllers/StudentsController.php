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
                        if($student->subjects()->where('google_id', '<>', null)->first()) {
                            $client = new \Google\Client();
                            $client->setAuthConfig('../laravel_id_secret.json');
                            $client->addScope('https://www.googleapis.com/auth/classroom.course-work.readonly');
                            $client->addScope('https://www.googleapis.com/auth/classroom.student-submissions.me.readonly');
                            $client->setAccessToken($user->token);

                            $service = new Classroom($client);
                            foreach ($subjects as $subject) {
                                $google_tasks = $service->courses_courseWork->listCoursesCourseWork($subject->google_id)->courseWork;
                            }

                            foreach ($google_tasks as $google_task) {
                                $submission = $service->courses_courseWork_studentSubmissions->listCoursesCourseWorkStudentSubmissions($google_task->courseId, $google_task->id);
                                $submission = $submission->studentSubmissions[0];

                                $task_ref = Task::where('google_id', $google_task->id)->first();
                                if(!$task_ref) {
                                    $task = new Task();
                                    $task->student_id = $request->student->id;
                                    $task->google_id = $google_task->id;
                                    $task->subject_id = Subject::where('google_id', $google_task->courseId)->first()->id;

                                    $task->name = $google_task->title;
                                    if($google_task->description) $task->description = $google_task->description;
                                    if($google_task->dueDate) {
                                        $task->date_handover = strtotime($google_task->dueDate->year.'-'.$google_task->dueDate->month.'-'.$google_task->dueDate->day);
                                    }

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
                                    if($google_task->dueDate) {
                                        $task_ref->date_handover = strtotime($google_task->dueDate->year.'-'.$google_task->dueDate->month.'-'.$google_task->dueDate->day);
                                    }
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
                            foreach ($tasks as $task) {
                                if($task->subject()->where('deleted', false)->first() || $task->subject()->first() == null) {
                                    $task->subtasks = $task->subtasks()->get();
                                    $task->subject = $task->subject()->first();
                                }
                            }
                        }
                    }

                    $events = $student->events()->orderBy('timestamp_start', 'asc')->get();

                    if(!$events->isEmpty()) {
                        foreach ($events as $event) {
                            if($event->type == "exam") {
                                $event->subject = $event->subject()->first();
                            }
                            $events_array[$event->id] = $event;
                        }
                    }

                    $http_status_code = 200;
                } else {
                    $response['response'] = "Student not found";
                    $http_status_code = 404;
                }
            } else {
                $response['response'] = "Student doesn't exist";
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
    public function edit(Request $request) {
        $data = $request->getContent();
        if($data) {
            try {
                $validator = Validator::make(json_decode($data, true), [
                    'name' => 'required|string',
                ]);

                if (!$validator->fails()) {
                    $data = json_decode($data);

                    $student = Student::find($request->student->id);
                    $student->name = $data->name;
                    $student->save();

                    $response['response'] = "Student edited properly.";
                    $http_status_code = 200;
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
            return response(null, 204);     //Ran when received data is empty    (412: Precondition failed)
        }
    }
    function uploadImage(Request $request) {
        $base_url = "http://powertask.kurokiji.com/";

        try {
            $request->validate([
                'image' => 'required|image|mimes:jpg,png,jpeg,gif,svg',
            ]);

            $path = $request->file('image')->store('public/images');
            $path = $base_url.'public/storage/images'.explode('images', $path)[1];

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

    function widget_totalSessionTime(Request $request) {
        try {
            $student = Student::find($request->student->id);
            $sessions = $student->sessions()->get();

            if(!$sessions->isEmpty()) {
                $time = 0;                              //Time in seconds
                foreach ($sessions as $session) {
                    $time += $session->total_time;
                }

                if($time) {
                    $hours = intval($time / 3600);
                    $minutes = intval(($time % 3600) / 60);
                    $seconds = intval(($time % 3600) % 60);

                    $response['hours'] = $hours;
                    $response['minutes'] = $minutes;
                    $response['seconds'] = $seconds;
                }
            }

            $http_status_code = 200;
        } catch (\Throwable $th) {
            $response['response'] = "An error has occurred: ".$th->getMessage();
            $http_status_code = 500;
        }
        return response()->json($response)->setStatusCode($http_status_code);
    }
    function widget_daysUntilPeriodEnds(Request $request) {
        try {
            $student = Student::find($request->student->id);
            $period = $student->periods()->where('date_start', '<=', time())->where('date_end', '>=', time())->first();

            if($period) {
                $start = $period->date_start;
                $finish = $period->date_end;

                $days = 0;
                $percentage = 0;

                // for ($i=time(); $i <= $finish; $i+=86400) {
                //     $days++;
                // }

                // More efective way of calculating days

                $days = round(($finish - time()) / 86400);      //Precision can be switched, calculation returns double

                $percentage = round(((time() - $start) / ($finish - $start)) * 100, 2);         //Returns percentage with a decimal precision of 2 of the current period's completion

                $response['days'] = $days;
                $response['percentage'] = $percentage;
                $http_status_code = 200;
            }
        } catch (\Throwable $th) {
            $response['response'] = "An error has occurred: ".$th->getMessage();
            $http_status_code = 500;
        }
        return response()->json($response)->setStatusCode($http_status_code);
    }
    function widget_completedTasks(Request $request) {
        try {
            $student = Student::find($request->student->id);
            $tasks = $student->tasks()->get();

            if($tasks) {
                $completed = 0;
                $total = 0;

                foreach ($tasks as $task) {
                    if($task->completed) {
                        $completed++;
                    }
                    $total++;
                }

                $response['completed'] = $completed;
                $response['total'] = $total;
                $http_status_code = 200;
            }
        } catch (\Throwable $th) {
            $response['response'] = "An error has occurred: ".$th->getMessage();
            $http_status_code = 500;
        }
        return response()->json($response)->setStatusCode($http_status_code);
    }
    function widget_markAverage(Request $request) {
        try {
            $student = Student::find($request->student->id);
            $period = $student->periods()->where('date_start', '<=', time())->where('date_end', '>=', time())->first();
            $subjects = $period->subjects()->where('deleted', true)->get();

            $tasks = array();
            foreach ($subjects as $subject) {
                foreach ($subject->tasks()->get() as $task) {
                    array_push($tasks, $task);
                }
            }

            if($tasks) {
                $mark = 0;
                $count = 0;

                foreach ($tasks as $task) {
                    if($task->mark) {
                        $mark += $task->mark;
                        $count++;
                    }
                }

                $response['average'] = $mark / $count;
                $http_status_code = 200;
            }
        } catch (\Throwable $th) {
            $response['response'] = "An error has occurred: ".$th->getMessage();
            $http_status_code = 500;
        }
        return response()->json($response)->setStatusCode($http_status_code);
    }
}
