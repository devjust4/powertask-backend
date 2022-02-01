<?php

namespace App\Http\Controllers;

use App\Models\Period;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PeriodsController extends Controller
{
    function create(Request $request) {
        $data = $request->getContent();
        if($data) {
            try {
                $validator = Validator::make(json_decode($data, true), [
                    'name' => 'required|string',
                    'date_start' => 'required|date_format:Y-m-d',
                    'date_start' => 'required|date_format:Y-m-d',
                    'student_id' => 'required|integer|exists:students,id',
                ], [
                    'date_format' => 'Date format is YYYY-MM-DD (1999-03-25)',
                ]);

                if (!$validator->fails()) {
                    $data = json_decode($data);

                    $period = new Period();
                    $period->name = $data->name;
                    $period->date_start = $data->date_start;
                    $period->date_end = $data->date_end;
                    $period->student_id = $data->student_id;

                    $period->save();

                    $response['response'] = "Period created properly with id ".$period->id;
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
                    'date_start' => 'date_format:Y-m-d',
                ]);

                if (!$validator->fails()) {
                    $data = json_decode($data);

                    if($period = Period::find($id)) {
                        if(isset($data->name)) $period->name = $data->name;
                        if(isset($data->date_start)) $period->date_start = $data->date_start;
                        if(isset($data->date_end)) $period->date_end = $data->date_end;

                        $period->save();

                        $response['response'] = "Period edited properly";
                        $http_status_code = 200;
                    } else {
                        $response['response'] = "Period by that id doesn't exist.";
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
        try {
            if ($period = Period::find($id)) {
                $period->delete();
                $response['response'] = "Period deleted successfully.";
                $http_status_code = 200;
            } else {
                $response['response'] = "Period by that id doesn't exist.";
                $http_status_code = 404;
            }
        } catch (\Throwable $th) {
            $response['response'] = "An error has occurred: ".$th->getMessage();
            $http_status_code = 500;
        }
        return response()->json($response)->setStatusCode($http_status_code);
    }
}
