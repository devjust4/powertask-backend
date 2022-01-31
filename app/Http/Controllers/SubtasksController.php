<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Subtask;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubtasksController extends Controller
{
    public function create(Request $request, $id) {
        $data = $request->getContent();
        if($data && $id) {
            try {
                $validator = Validator::make(json_decode($data, true), [
                    'name' => 'required|string',
                    'description' => 'required|string',
                ]);

                if (!$validator->fails()) {
                    $data = json_decode($data);

                    $subtask = new Subtask();
                    $subtask->name = $data->name;
                    $subtask->description = $data->description;

                    if (Task::find($id)) {
                        $subtask->task_id = $id;
                    } else {
                        return response('Task id doesn\'t match any task')->setStatusCode(400);
                    }

                    $subtask->save();

                    $response['response'] = "Subtask created properly with id ".$subtask->id;
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
        if($data && $id) {
            try {
                $validator = Validator::make(json_decode($data, true), [
                    'name' => 'nullable|string',
                    'description' => 'nullable|string',
                    'completed' => 'nullable|boolean',
                    // 'task_id' => 'nullable|int',          #Para poder editar la tarea a la que pertenecen
                ]);

                if (!$validator->fails()) {
                    $data = json_decode($data);

                    if($subtask = Subtask::find($id)) {
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

                        $response['response'] = "Subtask edited properly";
                        $http_status_code = 200;
                    } else {
                        $response['response'] = "Subtask by that id doesn't exist.";
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
    public function delete(Request $request, $id) {
        if($id) {
            try {
                if ($subtask = Subtask::find($id)) {
                    $subtask->delete();
                    $response['response'] = "Subtask deleted successfully.";
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
        } else {
            return response(null, 412);     //Ran when received id is empty    (412: Precondition failed)
        }
    }
}