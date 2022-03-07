<?php

namespace App\Http\Controllers;

use App\Models\Contain;
use App\Models\Period;
use App\Models\Student;
use App\Models\Subject;
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

                    'date_start' => 'required|numeric',
                    'date_start' => 'required|numeric|gte:date_start',

                    'subjects' => 'required|json',
                ]);

                if (!$validator->fails()) {
                    $data = json_decode($data);

                    $continue = true;
                    $message = "";

                    $subjects = json_decode($data->subjects);

                    foreach ($subjects as $subject) {
                        if(is_numeric($subject)) {
                            $subject_exists = Subject::find($subject);
                            if(!$subject_exists) {
                                $continue = false;
                                $message = "Subject by that id doesn't exist";
                            }
                        } else {
                            $continue = false;
                            $message = "Subjects must be integers";
                        }
                    }

                    if($continue == true) {
                        $period = new Period();
                        $period->name = $data->name;
                        $period->date_start = $data->date_start;
                        $period->date_end = $data->date_end;
                        $period->student_id = $request->student->id;
                        $period->save();

                        foreach ($subjects as $subject) {
                            $contain = new Contain();
                            $contain->period_id = $period->id;
                            $contain->subject_id = $subject;
                            $contain->save();
                        }

                        $response['id'] = $period->id;
                        $http_status_code = 201;
                    } else {
                        $response['response'] = $message;
                        $http_status_code = 400;
                    }
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
                    'name' => 'sometimes|string',
                    'date_start' => 'sometimes|numeric',
                    'date_start' => 'sometimes|numeric|gte:date_start',
                    'subjects' => 'sometimes|array',
                ]);

                if (!$validator->fails()) {
                    $data = json_decode($data);

                    if($period = Period::find($id)) {
                        if(isset($data->name)) $period->name = $data->name;
                        if(isset($data->date_start)) $period->date_start = $data->date_start;
                        if(isset($data->date_end)) $period->date_end = $data->date_end;

                        $period->save();

                        foreach($data->subjects as $subject) {
                            if(!Contain::where('period_id', $period->id)->where('subject_id', $subject)->first()) {
                                $contain = new Contain();
                                $contain->period_id = $period->id;
                                $contain->subject_id = $subject;
                                $contain->save();
                            }
                        }

                        $response['response'] = "Period edited properly";
                        $http_status_code = 200;
                    } else {
                        $response['response'] = "Period by that id doesn't exist.";
                        $http_status_code = 404;
                    }
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
    public function delete(Request $request, $id) {
        try {
            if ($period = Period::find($id)) {

                if($blocks = $period->blocks()->get()) {
                    foreach ($blocks as $block) {
                        $block->delete();
                    }
                }
                if($subjects = $period->subjects()->get()) {
                    foreach ($subjects as $subject) {
                        Contain::where('period_id', $id)->where('subject_id', $subject->id)->first()->delete();
                    }
                }
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
    public function list(Request $request) {
        try {
            $id = $request->student->id;
            if ($student = Student::find($id)) {
                $periods = $student->periods()->get();
                if(!$periods->isEmpty()) {
                    foreach ($periods as $period) {
                        $period->blocks = $period->blocks()->get();
                    }
                    $response['periods'] = $periods;
                    $http_status_code = 200;
                } else {
                    $response['response'] = "Student doesn't have periods";
                    $http_status_code = 400;
                }
            } else {
                $response['response'] = "Student by that id doesn't exist.";
                $http_status_code = 404;
            }
        } catch (\Throwable $th) {
            $response['response'] = "An error has occurred: ".$th->getMessage();
            $http_status_code = 500;
        }
        return response()->json($response)->setStatusCode($http_status_code);
    }
    public function getSubjects(Request $request, $id) {
        try {
            if ($period = Period::find($id)) {
                $subjects = $period->subjects()->where('deleted', false)->get();
                if(!$subjects->isEmpty()) {
                    $response['subjects'] = $subjects;
                    $http_status_code = 200;
                } else {
                    $response['response'] = "Period doesn't have subjects";
                    $http_status_code = 400;
                }
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
