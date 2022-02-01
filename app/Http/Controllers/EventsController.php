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
                    'type' => 'required|in:exam,doctor',
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
}
