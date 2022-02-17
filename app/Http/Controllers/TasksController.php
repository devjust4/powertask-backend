<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Subject;
use App\Models\Task;
use Google\Service\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TasksController extends Controller
{
    public function create(Request $request) {
        $data = $request->getContent();
        if($data) {
            try {
                $validator = Validator::make(json_decode($data, true), [
                    'name' => 'required|string',
                    'date_handover' => 'required|date_format:Y-m-d',
                    'description' => 'required|string',
                    'subject_id' => 'int|exists:subjects,id',
                ], [
                    'date_format' => 'The format doesn\'t match with YYYY-MM-DD (e.g. 1999-03-25)',
                ]);

                if (!$validator->fails()) {
                    $data = json_decode($data);

                    $task = new Task();
                    $task->name = $data->name;
                    $task->date_handover = $data->date_handover;
                    $task->description = $data->description;
                    $task->student_id = $request->student->id;

                    if(isset($data->subject_id)) {
                        if (Subject::find($data->subject_id)) {
                            $task->subject_id = $data->subject_id;
                        } else {
                            return response('Subject id doesn\'t match any subject')->setStatusCode(400);
                        }
                    }

                    $task->save();

                    $response['response'] = "Task created properly with id ".$task->id;
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
    public function edit(Request $request, $id) {
        $data = $request->getContent();
        if($data) {
            try {
                $validator = Validator::make(json_decode($data, true), [
                    'name' => 'string',
                    'date_completed' => 'date_format:Y-m-d',
                    'date_handover' => 'date_format:Y-m-d',
                    'mark' => 'integer',
                    'description' => 'string',
                    'completed' => 'boolean',
                    'subject_id' => 'integer|exists:subjects,id',
                ], [
                    'date_format' => 'The format doesn\'t match with YYYY-MM-DD (e.g. 1999-03-25)',
                ]);

                if (!$validator->fails()) {
                    $data = json_decode($data);

                    if($task = Task::find($id)) {
                        if(isset($data->name)) $task->name = $data->name;
                        if(isset($data->date_completed)) $task->date_completed = $data->date_completed;
                        if(isset($data->date_handover)) $task->date_handover = $data->date_handover;
                        if(isset($data->mark)) $task->mark = $data->mark;
                        if(isset($data->description)) $task->description = $data->description;
                        if(isset($data->completed)) $task->completed = $data->completed;
                        if(isset($data->subject_id)) $task->subject_id = $data->subject_id;

                        $task->save();

                        $response['response'] = "Task edited properly";
                        $http_status_code = 200;
                    } else {
                        $response['response'] = "Task by that id doesn't exist.";
                        $http_status_code = 404;
                    }
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
    public function list(Request $request) {
        try {
            $user = $request->user;
            $id = $request->student->id;
            if ($student = Student::find($id)) {
                $subjects = $student->subjects()->get();

                if(!$subjects->isEmpty()) {
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
                        // $submission = $service->courses_courseWork_studentSubmissions->listCoursesCourseWorkStudentSubmissions("458803316828", "458803317576");
                        $submission = $submission->studentSubmissions[0];

                        $task_ref = Task::where('google_id', $google_task->id)->first();
                        if(!$task_ref) {
                            $task = new Task();
                            $task->student_id = $request->student->id;
                            $task->google_id = $google_task->id;

                            $task->name = $google_task->title;
                            if($google_task->description) $task->description = $google_task->description;
                            if($google_task->dueDate) $task->date_handover = $google_task->dueDate->year.'-'.$google_task->dueDate->month.'-'.$google_task->dueDate->day;

                            if($submission->assignedGrade) $task_ref->mark = $submission->assignedGrade;
                            if($submission->updateTime) $task_ref->date_completed = explode("T", $submission->updateTime)[0];

                            switch ($submission->state) {
                                case 'CREATED':
                                    $task_ref->completed = false;
                                    break;
                                case 'TURNED_IN':
                                    $task_ref->completed = true;
                                    break;
                                case 'RETURNED':
                                    $task_ref->completed = true;
                                    break;
                                default:
                                    # code...
                                    break;
                            }

                            $task->save();
                        } else {
                            $task_ref->name = $google_task->title;
                            if($google_task->description) $task_ref->description = $google_task->description;
                            if($google_task->dueDate) $task_ref->date_handover = $google_task->dueDate->year.'-'.$google_task->dueDate->month.'-'.$google_task->dueDate->day;
                            if($google_task->description) $task_ref->description = $google_task->description;

                            if($submission->assignedGrade) $task_ref->mark = $submission->assignedGrade;
                            if($submission->updateTime) $task_ref->date_completed = explode("T", $submission->updateTime)[0];

                            switch ($submission->state) {
                                case 'CREATED':
                                    $task_ref->completed = false;
                                    break;
                                case 'TURNED_IN':
                                    $task_ref->completed = true;
                                    break;
                                case 'RETURNED':
                                    $task_ref->completed = true;
                                    break;
                                default:
                                    # code...
                                    break;
                            }
                            $task_ref->save();
                        }
                    }

                    $tasks = $student->tasks()->get();
                    if(!$tasks->isEmpty()) {
                        foreach ($tasks as $task) {
                            if($task->subject()->where('deleted', false)->first()) {
                                $task->subtasks = $task->subtasks()->get();
                            }
                        }

                        $response['tasks'] = $tasks;
                        $http_status_code = 200;
                    } else {
                        $response['msg'] = "Student doesn't have tasks";
                        $http_status_code = 400;
                    }
                } else {
                    $response['msg'] = "Student doesn't have subjects";
                    $http_status_code = 400;
                }

            } else {
                $response['response'] = "Student by that id doesn't exist.";
                $http_status_code = 404;
            }
        } catch (\Throwable $th) {
            $response['response'] = "An error has occurred: ".$th->getMessage();
            $http_status_code = 500;
        }
        return response()->json($response)->setStatusCode($http_status_code);
    }
    public function delete(Request $request, $id) {
        try {
            if ($task = Task::find($id)) {
                if($task->google_id == null) {
                    $task->delete();
                    $response['response'] = "Task deleted successfully.";
                    $http_status_code = 200;
                } else {
                    $response['response'] = "Task can't be deleted.";
                    $http_status_code = 403;
                }
            } else {
                $response['response'] = "Task by that id doesn't exist.";
                $http_status_code = 404;
            }
        } catch (\Throwable $th) {
            $response['response'] = "An error has occurred: ".$th->getMessage();
            $http_status_code = 500;
        }
        return response()->json($response)->setStatusCode($http_status_code);
    }
    public function toggleCheck(Request $request, $id) {
        try {
            if ($task = Task::find($id)) {
                if($task->completed == true) {
                    $task->completed = false;
                    $task->save();
                    $response['response'] = $task->completed;
                    $http_status_code = 200;
                } else {
                    $task->completed = true;
                    $task->save();
                    $response['response'] = $task->completed;
                    $http_status_code = 200;
                }
            } else {
                $response['response'] = "Task by that id doesn't exist.";
                $http_status_code = 404;
            }
        } catch (\Throwable $th) {
            $response['response'] = "An error has occurred: ".$th->getMessage();
            $http_status_code = 500;
        }
        return response()->json($response)->setStatusCode($http_status_code);
    }
}
