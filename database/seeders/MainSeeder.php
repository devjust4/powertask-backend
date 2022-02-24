<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tasks_repetitions = 20;
        $events_repetitions = 200;

        $event_types = ['personal', 'exam', 'vacation'];

        $students = Student::all();

        foreach ($students as $student) {
            $student_id = $student->id;

            for ($i=1; $i <= $tasks_repetitions; $i++) {

                DB::table('subjects')->insert([
                    'name' => Str::random(10),
                    // 'google_id' => Str::random(12),
                    'deleted' => 0,
                    'student_id' => $student_id,
                ]);

                DB::table('tasks')->insert([
                    'name' => Str::random(10),
                    'description' => Str::random(rand(70, 200)),
                    'subject_id' => rand(1, $i),
                    'student_id' => $student_id,
                ]);
                DB::table('tasks')->insert([
                    'name' => Str::random(10),
                    'description' => Str::random(rand(70, 200)),
                    'subject_id' => rand(1, $i),
                    'student_id' => $student_id,
                ]);

                DB::table('subtasks')->insert([
                    'name' => Str::random(10),
                    'completed' => rand(0, 1),
                    'task_id' => rand(1, $i),
                ]);
                DB::table('subtasks')->insert([
                    'name' => Str::random(10),
                    'completed' => rand(0, 1),
                    'task_id' => rand(1, $i),
                ]);
                DB::table('subtasks')->insert([
                    'name' => Str::random(10),
                    'completed' => rand(0, 1),
                    'task_id' => rand(1, $i),
                ]);
            }

            for ($i=1; $i <= $events_repetitions; $i++) {
                $timestamp_start = mt_rand(time()-1296000, time());
                $timestamp_end = mt_rand(time(), time()+1296000);

                DB::table('events')->insert([
                    'name' => Str::random(10),
                    'type' => $event_types[rand(0, 2)],
                    'all_day' => rand(0, 1),
                    'date_start' => date('Y-m-d', $timestamp_start),
                    'date_end' => date('Y-m-d', $timestamp_end),
                    'timestamp_start' => $timestamp_start,
                    'timestamp_end' => $timestamp_end,
                    'student_id' => $student_id,
                ]);
            }
        }
    }
}
