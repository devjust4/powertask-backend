<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TasksController extends Controller
{
    public function create(Request $req) {
        $http_status_code = 200;

        $data = $req->getContent();
        if($data) {
            if(gettype(json_decode($data, true)) === 'array') {

                $validator = Validator::make(json_decode($data, true), [
                    'name' => 'required|string',
                    'date_handover' => 'required|date_format:Y-m-d',
                    'description' => 'required|string',
                    'student_id' => 'required|int',
                    'subject_id' => 'required|int',
                ], [
                    'date_format' => 'The format doesn\'t match with YYYY-MM-DD (e.g. 1999-03-25)',
                ]);

                if ($validator->fails()) {
                    $response = ['status'=>0, 'msg'=>$validator->errors()->first()];
                    $http_status_code = 400;
                } else {
                    $response = ['status'=>1, 'msg'=>''];

                    $data = json_decode($data);

                    try {
                        $task = new Task();

                        $task->name = $data->name;
                        $task->date_handover = $data->date_handover;
                        $task->description = $data->description;
                        $task->student_id = $data->student_id;
                        $task->subject_id = $data->subject_id;

                        $task->save();

                        $response['msg'] = "Task created properly with id ".$task->id;
                        $http_status_code = 201;
                    } catch (\Throwable $th) {
                        $response['msg'] = "An error has occurred: ".$th->getMessage();
                        $response['status'] = 0;
                        $http_status_code = 500;
                    }
                }
                return response()->json($response)->setStatusCode($http_status_code);
            } else {
                return response(null, 400);     //Ran when received data is not an array    (400: Bad Request)
            }
        } else {
            return response(null, 204);     //Ran when received data is empty    (204: No Content)
        }
    }
    public function edit(Request $req) {
        $http_status_code = 200;

        $data = $req->getContent();
        if($data) {
            if(gettype(json_decode($data, true)) === 'array') {

                $validator = Validator::make(json_decode($data, true), [
                    'task_id' => 'required|integer',
                    'name' => 'string',
                    'date_completed' => 'date_format:Y-m-d',
                    'date_handover' => 'date_format:Y-m-d',
                    'mark' => 'integer',
                    'description' => 'string',
                    'completed' => 'boolean',
                    'student_id' => 'int',
                    'subject_id' => 'int',
                ], [
                    'date_format' => 'The format doesn\'t match with YYYY-MM-DD (e.g. 1999-03-25)',
                ]);

                if ($validator->fails()) {
                    $response = ['status'=>0, 'msg'=>$validator->errors()->first()];
                    $http_status_code = 400;
                } else {
                    $response = ['status'=>1, 'msg'=>''];

                    $data = json_decode($data);

                    try {
                        $task = Task::find($data->task_id);

                        if(isset($data->name)) $task->name = $data->name;
                        if(isset($data->date_completed)) $task->date_completed = $data->date_completed;
                        if(isset($data->date_handover)) $task->date_handover = $data->date_handover;
                        if(isset($data->mark)) $task->mark = $data->mark;
                        if(isset($data->description)) $task->description = $data->description;
                        if(isset($data->completed)) $task->completed = $data->completed;
                        if(isset($data->student_id)) $task->student_id = $data->student_id;
                        if(isset($data->subject_id)) $task->subject_id = $data->subject_id;

                        $task->save();

                        $response['msg'] = "Task edited properly";
                        $http_status_code = 200;
                    } catch (\Throwable $th) {
                        $response['msg'] = "An error has occurred: ".$th->getMessage();
                        $response['status'] = 0;
                        $http_status_code = 500;
                    }
                }
                return response()->json($response)->setStatusCode($http_status_code);
            } else {
                return response(null, 400);     //Ran when received data is not an array    (400: Bad Request)
            }
        } else {
            return response(null, 204);     //Ran when received data is empty    (204: No Content)
        }
    }
    public function get(Request $req) {
        $http_status_code = 200;

        $data = $req->getContent();
        if($data) {
            if(gettype(json_decode($data, true)) === 'array') {

                $validator = Validator::make(json_decode($data, true), [
                    'task_id' => 'required|integer',
                ]);

                if ($validator->fails()) {
                    $response = ['status'=>0, 'msg'=>$validator->errors()->first()];
                    $http_status_code = 400;
                } else {
                    $response = ['status'=>1, 'msg'=>''];

                    $data = json_decode($data);

                    try {
                        if ($task = Task::find($data->task_id)) {
                            $response['msg'] = "Task shown properly.";
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
                }
                return response()->json($response)->setStatusCode($http_status_code);
            } else {
                return response(null, 400);     //Ran when received data is not an array    (400: Bad Request)
            }
        } else {
            return response(null, 204);     //Ran when received data is empty    (204: No Content)
        }
    }
}
