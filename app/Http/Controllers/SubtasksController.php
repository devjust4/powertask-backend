<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Subtask;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubtasksController extends Controller
{
    public function create(Request $req) {
        $http_status_code = 200;

        $data = $req->getContent();
        if($data) {
            if(gettype(json_decode($data, true)) === 'array') {

                $validator = Validator::make(json_decode($data, true), [
                    'name' => 'required|string',
                    'description' => 'required|string',
                    'task_id' => 'required|int',
                ]);

                if ($validator->fails()) {
                    $response = ['status'=>0, 'msg'=>$validator->errors()->first()];
                    $http_status_code = 400;
                } else {
                    $response = ['status'=>1, 'msg'=>''];

                    $data = json_decode($data);

                    try {
                        $subtask = new Subtask();

                        $subtask->name = $data->name;
                        $subtask->description = $data->description;

                        if (Task::find($data->task_id)) {
                            $subtask->task_id = $data->task_id;
                        } else {
                            return response('Task id doesn\'t match any task')->setStatusCode(400);
                        }

                        $subtask->save();

                        $response['msg'] = "Subtask created properly with id ".$subtask->id;
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
                    'subtask_id' => 'required|integer',
                    'name' => 'string',
                    'description' => 'string',
                    'completed' => 'boolean',
                    // 'task_id' => 'int',          #Para poder editar la tarea a la que pertenecen
                ]);

                if ($validator->fails()) {
                    $response = ['status'=>0, 'msg'=>$validator->errors()->first()];
                    $http_status_code = 400;
                } else {
                    $response = ['status'=>1, 'msg'=>''];

                    $data = json_decode($data);

                    try {
                        if($subtask = Subtask::find($data->subtask_id)) {
                            if(isset($data->name)) $subtask->name = $data->name;
                            if(isset($data->description)) $subtask->description = $data->description;
                            if(isset($data->completed)) $subtask->completed = $data->completed;
                            // if(isset($data->task_id)) {          #Para poder editar la tarea a la que pertenecen
                            //     if (Task::find($data->task_id)) {
                            //         $subtask->task_id = $data->task_id;
                            //     } else {
                            //         return response('Task id doesn\'t match any task')->setStatusCode(400);
                            //     }
                            // }
                            $subtask->save();

                            $response['msg'] = "Subtask edited properly";
                            $http_status_code = 200;
                        } else {
                            $response['msg'] = "Subtask by that id doesn't exist.";
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
    public function get(Request $req) {
        $http_status_code = 200;

        $data = $req->getContent();
        if($data) {
            if(gettype(json_decode($data, true)) === 'array') {

                $validator = Validator::make(json_decode($data, true), [
                    'subtask_id' => 'required|integer',
                ]);

                if ($validator->fails()) {
                    $response = ['status'=>0, 'msg'=>$validator->errors()->first()];
                    $http_status_code = 400;
                } else {
                    $response = ['status'=>1, 'msg'=>''];

                    $data = json_decode($data);

                    try {
                        if ($subtask = Subtask::find($data->subtask_id)) {
                            $response['msg'] = "Subtask found successfully.";
                            $response['data'] = $subtask;
                            $http_status_code = 200;
                        } else {
                            $response['msg'] = "Subtask by that id doesn't exist.";
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
    public function delete(Request $req) {
        $http_status_code = 200;

        $data = $req->getContent();
        if($data) {
            if(gettype(json_decode($data, true)) === 'array') {

                $validator = Validator::make(json_decode($data, true), [
                    'subtask_id' => 'required|integer',
                ]);

                if ($validator->fails()) {
                    $response = ['status'=>0, 'msg'=>$validator->errors()->first()];
                    $http_status_code = 400;
                } else {
                    $response = ['status'=>1, 'msg'=>''];

                    $data = json_decode($data);

                    try {
                        if ($subtask = Subtask::find($data->subtask_id)) {
                            $subtask->delete();
                            $response['msg'] = "Subtask deleted successfully.";
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
