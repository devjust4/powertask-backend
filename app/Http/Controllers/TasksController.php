<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TasksController extends Controller
{
    public function create(Request $req) {
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
                        return response()->json($response)->setStatusCode(201);
                    } catch (\Throwable $th) {
                        $response['msg'] = "An error has occurred: ".$th->getMessage();
                        $response['status'] = 0;
                    }
                }
                return response()->json($response)->setStatusCode(500);
            } else {
                return response(null, 400);     //Ran when received data is not an array    (400: Bad Request)
            }
        } else {
            return response(null, 204);     //Ran when received data is empty    (204: No Content)
        }
    }
}
