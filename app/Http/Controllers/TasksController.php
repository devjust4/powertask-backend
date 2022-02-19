<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Subject;
use App\Models\Task;
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
                    'date_start' => 'sometimes|date_format:Y-m-d',
                    'description' => 'sometimes|string',
                    'subject_id' => 'sometimes|int|exists:subjects,id',
                ], [
                    'date_format' => 'The format doesn\'t match with YYYY-MM-DD (e.g. 1999-03-25)',
                ]);

                if (!$validator->fails()) {
                    $data = json_decode($data);

                    $task = new Task();
                    $task->name = $data->name;
                    if(isset($data->date_start)) $task->date_start = $data->date_start;
                    if(isset($data->description)) $task->description = $data->description;
                    $task->student_id = $request->student->id;

                    if(isset($data->subject_id)) {
                        if (Subject::find($data->subject_id)) {
                            $task->subject_id = $data->subject_id;
                        } else {
                            return response('Subject id doesn\'t match any subject')->setStatusCode(400);
                        }
                    }

                    $task->save();

                    $response['id'] = $task->id;
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
                    'date_start' => 'date_format:Y-m-d',
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
                        if(isset($data->date_start)) $task->date_start = $data->date_start;
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
            $id = $request->student->id;
            if ($student = Student::find($id)) {
                $tasks = $student->tasks()->get();
                if(!$tasks->isEmpty()) {
                    foreach ($tasks as $task) {
                        if($task->subject()->where('deleted', false)->first()) {
                            $task->subtasks = $task->subtasks()->get();
                        }
                    }

                    $tasks = $student->tasks()->get();
                    if(!$tasks->isEmpty()) {
                        $tasks_array = array();
                        foreach ($tasks as $task) {
                            if($task->subject()->where('deleted', false)->first() || $task->subject()->first() == null) {
                                $task->subtasks = $task->subtasks()->get();
                                $task->subject = $task->subject()->first();
                                array_push($tasks_array, $task);
                            }
                        }

                        $response['tasks'] = $tasks_array;
                        $http_status_code = 200;
                    } else {
                        $response['msg'] = "Student doesn't have tasks";
                        $http_status_code = 400;
                    }
                } else {
                    $response['msg'] = "Student doesn't have tasks";
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
                $task->delete();
                $response['response'] = "Task deleted successfully.";
                $http_status_code = 200;
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
                    $response['response'] = 0;
                    $http_status_code = 200;
                } else {
                    $task->completed = true;
                    $task->save();
                    $response['response'] = 1;
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
