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
                    'notes' => 'required|string',
                    'date_start' => 'required|date_format:Y-m-d H:i:s',
                    'date_end' => 'required|date_format:Y-m-d H:i:s',
                    'subject_id' => 'required|integer|exists:subjects,id',
                    'student_id' => 'required|integer|exists:students,id',
                ]);

                if (!$validator->fails()) {
                    $data = json_decode($data);

                    $event = new Event();
                    $event->name = $data->name;
                    $event->type = $data->type;
                    $event->all_day = $data->all_day;
                    $event->notes = $data->notes;
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

    public function getEvents(Request $request, $id) {
        try {
            $student = Student::find($id);

            if(!$student->events()->get()->isEmpty()) {
                $first_date = $student->events()->orderBy('date_start', 'asc')->first()->date_start;        //Recojo la primera fecha que haya
                $begin = new DateTime( explode(" ", $first_date)[0] . ' 00:00:00' );
                $begin->modify('-1 day');

                $last_date = $student->events()->orderBy('date_end', 'desc')->first()->date_end;            //Recojo la ultima fecha que haya
                $end = new DateTime($last_date);
                // $end->modify('+1 day');
                $end->setTime(23, 59, 59);

                $interval = DateInterval::createFromDateString('1 day');
                $period = new DatePeriod($begin, $interval, $end);

                foreach ($period as $date) {                //Recorro todo ese intervalo
                    $date_format = $date->format("Y-m-d");
                    $date_comparison = $date->format("Y-m-d H:i:s");

                    $event = $student->events()->where('date_start', '<=', $date_comparison)->where('date_end', '>=', $date_comparison)->get();
                    if(!$event->isEmpty()) {
                        $events[$date_format] = $event;
                    }
                }

                $response['events'] = $events;
                $http_status_code = 200;
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
