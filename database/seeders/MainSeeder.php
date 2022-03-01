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
        $faker = Faker::create('en_US');
        $tasks_repetitions = 20;
        $events_repetitions = 200;

        $event_types = ['personal', 'exam', 'vacation'];

        $students = Student::all();

        foreach ($students as $student) {
            $student_id = $student->id;

            for ($i=1; $i <= $tasks_repetitions; $i++) {

                DB::table('subjects')->insert([
                    'name' => $faker->numerify('Asignatura ###'),
                    'google_id' => Str::random(12),
                    'deleted' => 0,
                    'student_id' => $student_id,
                    'color' => $faker->hexcolor,
                ]);

                if ($i%2 == 1) {
                    DB::table('tasks')->insert([
                        'name' => $faker->sentence($nbWords = 3, $variableNbWords = true),
                        'description' => $faker->text($maxNbChars = 200),
                        'subject_id' => rand(1, $i),
                        'student_id' => $student_id,
                        'mark' => $faker->randomDigit,
                        'google_id' => Str::random(12),
                    ]);
                } else {
                    DB::table('tasks')->insert([
                        'name' => $faker->sentence($nbWords = 3, $variableNbWords = true),
                        'description' => $faker->text($maxNbChars = 200),
                        'subject_id' => rand(1, $i),
                        'student_id' => $student_id,
                    ]);
                }
                DB::table('tasks')->insert([
                    'name' => $faker->sentence($nbWords = 3, $variableNbWords = true),
                    'description' =>  $faker->text($maxNbChars = 200),
                    'subject_id' => rand(1, $i),
                    'student_id' => $student_id,
                ]);

                DB::table('subtasks')->insert([
                    'name' => $faker->sentence($nbWords = 3, $variableNbWords = true),
                    'completed' => rand(0, 1),
                    'task_id' => rand(1, $i),
                ]);
                DB::table('subtasks')->insert([
                    'name' => $faker->sentence($nbWords = 3, $variableNbWords = true),
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
                $timestamp_start = mt_rand(time()-2592000, time());
                $timestamp_end = mt_rand(time(), time()+2592000);
            $type = $faker->randomElement($array = array ('vacation','personal','exam'));
            $randomName = $faker->sentence($nbWords = 2, $variableNbWords = true);
            switch ($type) {
                    case 'vacation':
                        DB::table('events')->insert([
                            'name' => "Fiesta $randomName",
                            'type' => 'vacation',
                            'all_day' => 1,
                            'date_start' => date('Y-m-d', $timestamp_start),
                            'date_end' => date('Y-m-d', $timestamp_start),
                            'timestamp_start' => $timestamp_start,
                            'timestamp_end' => $timestamp_start,
                            'student_id' => $student_id,
                        ]);
                        break;
                    case 'personal':
                        DB::table('events')->insert([
                            'name' => "Evento $randomName",
                            'type' => 'personal',
                            'all_day' => rand(0, 1),
                            'date_start' => date('Y-m-d', $timestamp_start),
                            'date_end' => date('Y-m-d', $timestamp_end),
                            'timestamp_start' => $timestamp_start,
                            'timestamp_end' => $timestamp_end,
                            'student_id' => $student_id,
                        ]);
                        break;
                    case 'exam':
                        DB::table('events')->insert([
                            'name' => "Examen $randomName",
                            'type' => 'exam',
                            'all_day' => 0,
                            'date_start' => date('Y-m-d', $timestamp_start),
                            'date_end' => date('Y-m-d', $timestamp_start + (60*60)),
                            'timestamp_start' => $timestamp_start,
                            'timestamp_end' => $timestamp_start + (60*60),
                            'student_id' => $student_id,
                            'subject_id' => rand(1, $tasks_repetitions),
                        ]);
                        break;

                    default:
                        break;
                }
            }
        }
    }
}
