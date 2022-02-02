<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventsController extends Controller
{
    function create(Request $request) {
        $data = $request->getContent();
        if($data) {
            try {
                $validator = Validator::make(json_decode($data, true), [
                    'name' => 'required|string',
                    'type' => 'required|in:exam,medical,vacations',
                    'date_start' => 'required|date_format:Y-m-d',
                    'date_end' => 'required|date_format:Y-m-d',
                    'subject_id' => 'required|integer|exists:subjects,id',
                    'student_id' => 'required|integer|exists:students,id',
                ]);

                if (!$validator->fails()) {
                    $data = json_decode($data);

                    $event = new Event();
                    $event->name = $data->name;
                    $event->type = $data->type;
                    $event->date_start = $data->date_start;
                    $event->date_end = $data->date_end;
                    $event->subject_id = $data->subject_id;
                    $event->student_id = $data->student_id;

                    $event->save();

                    $response['response'] = "Event created properly with id ".$event->id;
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
                    'type' => 'in:exam,medical,vacations',
                    'date_start' => 'date_format:Y-m-d',
                    'date_end' => 'date_format:Y-m-d',
                    'subject_id' => 'integer|exists:subjects,id',
                ]);

                if (!$validator->fails()) {
                    $data = json_decode($data);

                    if($event = Event::find($id)) {
                        if(isset($data->name)) $event->name = $data->name;
                        if(isset($data->type)) $event->type = $data->type;
                        if(isset($data->date_start)) $event->date_start = $data->date_start;
                        if(isset($data->date_end)) $event->date_end = $data->date_end;
                        if(isset($data->subject_id)) $event->subject_id = $data->subject_id;

                        $event->save();

                        $response['response'] = "Event edited properly";
                        $http_status_code = 200;
                    } else {
                        $response['response'] = "Event by that id doesn't exist.";
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
            if ($event = Event::find($id)) {
                $event->delete();
                $response['response'] = "Event deleted successfully.";
                $http_status_code = 200;
            } else {
                $response['response'] = "Event by that id doesn't exist.";
                $http_status_code = 404;
            }
        } catch (\Throwable $th) {
            $response['response'] = "An error has occurred: ".$th->getMessage();
            $http_status_code = 500;
        }
        return response()->json($response)->setStatusCode($http_status_code);
    }
}
