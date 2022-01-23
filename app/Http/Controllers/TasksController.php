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
                    'date_handover' => 'required|date_format:Y-m-d',
                    'description' => 'required|string',
                    'student_id' => 'required|int',
                    'subject_id' => 'int',
                ], [
                    'date_format' => 'The format doesn\'t match with YYYY-MM-DD (e.g. 1999-03-25)',
                ]);

                if (!$validator->fails()) {
                    $response = ['status'=>1, 'msg'=>''];

                    $data = json_decode($data);

                    $task = new Task();

                    $task->name = $data->name;
                    $task->date_handover = $data->date_handover;
                    $task->description = $data->description;

                    if (Student::find($data->student_id)) {
                        $task->student_id = $data->student_id;
                    } else {
                        return response('Student id doesn\'t match any student')->setStatusCode(400);
                    }
                    if(isset($data->subject_id)) {
                        if (Subject::find($data->subject_id)) {
                            $task->subject_id = $data->subject_id;
                        } else {
                            return response('Subject id doesn\'t match any subject')->setStatusCode(400);
                        }
                    }

                    $task->save();

                    $response['msg'] = "Task created properly with id ".$task->id;
                    $http_status_code = 201;
                } else {
                    $response = ['status'=>0, 'msg'=>$validator->errors()->first()];
                    $http_status_code = 400;
                }
            } catch (\Throwable $th) {
                $response['msg'] = "An error has occurred: ".$th->getMessage();
                $response['status'] = 0;
                $http_status_code = 500;
            }
            return response()->json($response)->setStatusCode($http_status_code);
        } else {
            return response(null, 412);     //Ran when received data is empty    (412: Precondition failed)
        }
    }
    public function edit(Request $request) {
        $data = $request->getContent();
        if($data) {
            try {
                $validator = Validator::make(json_decode($data, true), [
                    'task_id' => 'required|integer',
                    'name' => 'string',
                    'date_completed' => 'date_format:Y-m-d',
                    'date_handover' => 'date_format:Y-m-d',
                    'mark' => 'integer',
                    'description' => 'string',
                    'completed' => 'boolean',
                    'subject_id' => 'int',
                ], [
                    'date_format' => 'The format doesn\'t match with YYYY-MM-DD (e.g. 1999-03-25)',
                ]);

                if (!$validator->fails()) {
                    $response = ['status'=>1, 'msg'=>''];

                    $data = json_decode($data);

                    if($task = Task::find($data->task_id)) {
                        if(isset($data->name)) $task->name = $data->name;
                        if(isset($data->date_completed)) $task->date_completed = $data->date_completed;
                        if(isset($data->date_handover)) $task->date_handover = $data->date_handover;
                        if(isset($data->mark)) $task->mark = $data->mark;
                        if(isset($data->description)) $task->description = $data->description;
                        if(isset($data->completed)) $task->completed = $data->completed;
                        if(isset($data->subject_id)) {
                            if (Subject::find($data->subject_id)) {
                                $task->subject_id = $data->subject_id;
                            } else {
                                return response('Subject id doesn\'t match any subject')->setStatusCode(400);
                            }
                        }

                        $task->save();

                        $response['msg'] = "Task edited properly";
                        $http_status_code = 200;
                    } else {
                        $response['msg'] = "Task by that id doesn't exist.";
                        $http_status_code = 404;
                    }
                } else {
                    $response = ['status'=>0, 'msg'=>$validator->errors()->first()];
                    $http_status_code = 400;
                }
            } catch (\Throwable $th) {
                $response['msg'] = "An error has occurred: ".$th->getMessage();
                $response['status'] = 0;
                $http_status_code = 500;
            }
            return response()->json($response)->setStatusCode($http_status_code);
        } else {
            return response(null, 204);     //Ran when received data is empty    (412: Precondition failed)
        }
    }
    public function get(Request $request, $id) {
        if($id) {
            try {
                $response = ['status'=>1, 'msg'=>''];

                if ($task = Task::find($id)) {
                    $task->subtasks = $task->subtasks()->get();

                    $response['msg'] = "Task found successfully.";
                    $response['data'] = $task;
                    $http_status_code = 200;
                } else {
                    $response['msg'] = "Task by that id doesn't exist.";
                    $http_status_code = 404;
                }
            } catch (\Throwable $th) {
                $response['msg'] = "An error has occurred: ".$th->getMessage();
                $response['status'] = 0;
                $http_status_code = 500;
            }
            return response()->json($response)->setStatusCode($http_status_code);
        } else {
            return response(null, 412);     //Ran when received id is empty    (412: Precondition failed)
        }
    }
    public function list(Request $request, $id) {
        if($id) {
            try {
                $response = ['status'=>1, 'msg'=>''];

                if ($student = Student::find($id)) {
                    $tasks = $student->tasks()->get();
                    $task_array = array();
                    foreach ($tasks as $task) {
                        $task->subtasks = $task->subtasks()->get();
                        array_push($task_array, $task);
                    }

                    $response['msg'] = "Tasks found successfully.";
                    $response['data'] = $task_array;
                    $http_status_code = 200;
                } else {
                    $response['msg'] = "Student by that id doesn't exist.";
                    $http_status_code = 404;
                }
            } catch (\Throwable $th) {
                $response['msg'] = "An error has occurred: ".$th->getMessage();
                $response['status'] = 0;
                $http_status_code = 500;
            }
            return response()->json($response)->setStatusCode($http_status_code);
        } else {
            return response(null, 412);     //Ran when received id is empty    (412: Precondition failed)
        }
    }
    public function delete(Request $request, $id) {
        if($id) {
            try {
                $response = ['status'=>1, 'msg'=>''];

                if ($task = Task::find($id)) {
                    $task->delete();
                    $response['msg'] = "Task deleted successfully.";
                    $http_status_code = 200;
                } else {
                    $response['msg'] = "Task by that id doesn't exist.";
                    $http_status_code = 404;
                }
            } catch (\Throwable $th) {
                $response['msg'] = "An error has occurred: ".$th->getMessage();
                $response['status'] = 0;
                $http_status_code = 500;
            }
            return response()->json($response)->setStatusCode($http_status_code);
        } else {
            return response(null, 412);     //Ran when received id is empty    (412: Precondition failed)
        }
    }
}
