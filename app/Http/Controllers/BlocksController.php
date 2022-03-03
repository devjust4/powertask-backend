<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Period;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BlocksController extends Controller
{
    function create(Request $request, $id) {
        $data = $request->getContent();
        if($data) {
            try {
                $validator = Validator::make(json_decode($data, true), [
                    'time_start' => 'required|date_format:H:i',
                    'time_end' => 'required|date_format:H:i|after:time_start',
                    'day' => 'required|integer',
                    'subject_id' => 'required|integer|exists:subjects,id',
                ]);

                if (!$validator->fails()) {
                    $data = json_decode($data);

                    $block = new Block();
                    $block->time_start = $data->time_start;
                    $block->time_end = $data->time_end;
                    $block->day = $data->day;
                    $block->student_id = $request->student->id;
                    $block->subject_id = $data->subject_id;
                    $block->period_id = $id;

                    $block->save();

                    $response['id'] = $block->id;
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
                    'time_start' => 'date_format:H:i',
                    'time_end' => 'date_format:H:i|after:time_start',
                    'day' => 'integer',
                    'subject_id' => 'integer|exists:subjects,id',
                ]);

                if (!$validator->fails()) {
                    $data = json_decode($data);

                    if($block = Block::find($id)) {
                        if(isset($data->time_start)) $block->time_start = $data->time_start;
                        if(isset($data->time_end)) $block->time_end = $data->time_end;
                        if(isset($data->day)) $block->day = $data->day;
                        if(isset($data->subject_id)) $block->subject_id = $data->subject_id;

                        $block->save();

                        $response['response'] = "Block edited properly";
                        $http_status_code = 200;
                    } else {
                        $response['response'] = "Period by that id doesn't exist.";
                        $http_status_code = 404;
                    }
                } else {
                    $response['response'] =$validator->errors()->first();
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
            if ($block = Block::find($id)) {
                $block->delete();
                $response['response'] = "Block deleted successfully.";
                $http_status_code = 200;
            } else {
                $response['response'] = "Block by that id doesn't exist.";
                $http_status_code = 404;
            }
        } catch (\Throwable $th) {
            $response['response'] = "An error has occurred: ".$th->getMessage();
            $http_status_code = 500;
        }
        return response()->json($response)->setStatusCode($http_status_code);
    }
    public function list(Request $request, $id) {
        try {
            if ($period = Period::find($id)) {
                $blocks = $period->blocks()->orderBy('day', 'asc')->get();
                if(!$blocks->isEmpty()) {
                    foreach ($blocks as $block) {
                        $block->subject = $block->subject()->first();
                        $blocks_array[$block->id] = $block;
                    }
                    if($blocks_array) {
                        $response['blocks'] = $blocks_array;
                        $http_status_code = 200;
                    }
                } else {
                    $response['response'] = "Period doesn't have blocks.";
                    $http_status_code = 400;
                }
            } else {
                $response['response'] = "Period not found.";
                $http_status_code = 404;
            }
        } catch (\Throwable $th) {
            $response['response'] = "An error has occurred: ".$th->getMessage();
            $http_status_code = 500;
        }
        return response()->json($response)->setStatusCode($http_status_code);
    }
}
