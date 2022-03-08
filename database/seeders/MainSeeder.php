<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class MainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $students = Student::all();
        foreach ($students as $student) {
            if($student->periods()->get()->isEmpty()) {
                DB::table('periods')->insert([
                    'name' => "Primer trimestre",
                    'date_start' => "1631704831",
                    'date_end' => "1640258431",
                    'student_id' => $student->id,
                ]);
                DB::table('periods')->insert([
                    'name' => "Segundo trimestre",
                    'date_start' => "1641813631",
                    'date_end' => "1648466431",
                    'student_id' => $student->id,
                ]);
                DB::table('periods')->insert([
                    'name' => "Tercer trimestre",
                    'date_start' => "1648552831",
                    'date_end' => "1655292031",
                    'student_id' => $student->id,
                ]);

                $period_id = $student->periods()->where('name', 'Segundo trimestre')->first()->id;
                $subject_ids = array();
                foreach ($student->subjects()->get() as $subject) {
                    DB::table('contains')->insert([
                        'period_id' => $period_id,
                        'subject_id' => $subject->id,
                    ]);
                    array_push($subject_ids, $subject->id);
                }

                for ($i=1; $i <= 5; $i++) {
                    $subject_id = array_rand($subject_ids);
                    $this->createBlock(1646730000, 1646736000, $i, $student->id, $subject_id, $period_id);
                    $this->createBlock(1646737200, 1646743200, $i, $student->id, $subject_id, $period_id);
                    $this->createBlock(1646744400, 1646750400, $i, $student->id, $subject_id, $period_id);
                }
            }
        }
    }

    public function createBlock($time_start, $time_end, $day, $student_id, $subject_id, $period_id) {
        DB::table('blocks')->insert([
            'time_start' => $time_start,
            'time_end' => $time_end,
            'day' => $day,
            'student_id' => $student_id,
            'subject_id' => $subject_id,
            'period_id' => $period_id,
        ]);
    }
}
