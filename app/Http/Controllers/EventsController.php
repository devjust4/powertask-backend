<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Student;
use DateInterval;
use DatePeriod;
use DateTime;
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
                    'type' => 'required|in:vacation,exam,personal',
                    'all_day' => 'required|boolean',
                    'notes' => 'sometimes|string',

                    'timestamp_start' => 'required|numeric',
                    'timestamp_end' => 'required|numeric|gte:timestamp_start',

                    'subject_id' => 'sometimes|integer|exists:subjects,id',
                ]);

                if (!$validator->fails()) {
                    $data = json_decode($data);

                    $event = new Event();
                    $event->name = $data->name;
                    $event->type = $data->type;
                    $event->all_day = $data->all_day;
                    if(isset($data->notes)) $event->notes = $data->notes;

                    $event->timestamp_start = $data->timestamp_start;
                    $event->timestamp_end = $data->timestamp_end;

                    if(isset($data->subject_id)) $event->subject_id = $data->subject_id;
                    $event->student_id = $request->student->id;

                    $event->save();

                    $response['id'] = $event->id;
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
                    'type' => 'in:vacation,exam,personal',
                    'all_day' => 'boolean',
                    'notes' => 'string',

                    'timestamp_start' => 'required|numeric',
                    'timestamp_end' => 'required|numeric|gte:timestamp_start',

                    'subject_id' => 'sometimes|integer|exists:subjects,id',
                ]);

                if (!$validator->fails()) {
                    $data = json_decode($data);

                    if($event = Event::find($id)) {
                        if(isset($data->name)) $event->name = $data->name;
                        if(isset($data->type)) $event->type = $data->type;
                        if(isset($data->all_day)) $event->all_day = $data->all_day;
                        if(isset($data->notes)) $event->notes = $data->notes;

                        if(isset($data->timestamp_start)) $event->timestamp_start = $data->timestamp_start;
                        if(isset($data->timestamp_end)) $event->timestamp_end = $data->timestamp_end;

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

    public function deprecatedList(Request $request) {
        try {
            $id = $request->student->id;
            $student = Student::find($id);

            if(!$student->events()->get()->isEmpty()) {
                $first_date = $student->events()->orderBy('date_start', 'asc')->first()->date_start;        //Recojo la primera fecha que haya
                $begin = new DateTime($first_date);

                $last_date = $student->events()->orderBy('date_end', 'desc')->first()->date_end;            //Recojo la ultima fecha que haya
                $end = new DateTime($last_date);
                $end->modify('+1 day');

                $interval = DateInterval::createFromDateString('1 day');
                $period = new DatePeriod($begin, $interval, $end);

                foreach ($period as $date) {                //Recorro todo ese intervalo
                    $date = $date->format("Y-m-d");

                    $events = $student->events()->where('date_start', '<=', $date)->where('date_end', '>=', $date)->get();
                    if(!$events->isEmpty()) {
                        $array["vacation"] = array();
                        $array["exam"] = array();
                        $array["personal"] = array();

                        foreach ($events as $event) {
                            if($event->all_day == true) $event->makeHidden(['time_start', 'time_end']);

                            if($event->type == "vacation") array_push($array["vacation"], $event);
                            if($event->type == "exam") {
                                $event->subject = $event->subject()->first();
                                array_push($array["exam"], $event);
                            }
                            if($event->type == "personal") array_push($array["personal"], $event);
                        }
                        $events_array[strtotime($date)] = $array;
                    }
                }

                if($events) {
                    $response['events'] = $events_array;
                    $http_status_code = 200;
                }
            } else {
                $response['msg'] = "User doesn't have events";
                $http_status_code = 404;
            }
        } catch (\Throwable $th) {
            $response['response'] = "An error has occurred: ".$th->getMessage();
            $http_status_code = 500;
        }
        return response()->json($response)->setStatusCode($http_status_code);
    }

    public function list(Request $request) {
        try {
            $id = $request->student->id;
            $student = Student::find($id);
            $events = $student->events()->orderBy('timestamp_start', 'asc')->get();

            if(!$events->isEmpty()) {
                foreach ($events as $event) {
                    if($event->type == "exam") {
                        $event->subject = $event->subject()->first();
                    }
                    $events_array[$event->id] = $event;
                }
                if($events_array) {
                    $response['events'] = $events_array;
                    $http_status_code = 200;
                }
            } else {
                $response['msg'] = "User doesn't have events";
                $http_status_code = 404;
            }
        } catch (\Throwable $th) {
            $response['response'] = "An error has occurred: ".$th->getMessage();
            $http_status_code = 500;
        }
        return response()->json($response)->setStatusCode($http_status_code);
    }
}
