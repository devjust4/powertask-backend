<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('students')->insert([
            'name' => 'Daniel Ximenez',
            'email' => 'danixidev@gmail.com',
            'image_url' => 'image01.png',
            'google_id' => '0',
        ]);
        DB::table('students')->insert([
            'name' => 'Daniel Torres',
            'email' => 'danitorres@gmail.com',
            'image_url' => 'image01.png',
            'google_id' => '1',
        ]);

        DB::table('courses')->insert([
            'name' => 'Curso 1',
            'student_id' => 1,
        ]);

        DB::table('subjects')->insert([
            'name' => 'Acceso a Datos',
            'color' => 'FF0000',
            'course_id' => 1,
            'student_id' => 1,
            'google_id' => "0",
        ]);
        DB::table('subjects')->insert([
            'name' => 'Ingles',
            'color' => 'FF0000',
            'course_id' => 1,
            'student_id' => 1,
            'google_id' => "1",
        ]);
        DB::table('subjects')->insert([
            'name' => 'Ingles',
            'color' => 'FF0000',
            'course_id' => 1,
            'student_id' => 2,
            'google_id' => "1",
        ]);
        DB::table('subjects')->insert([
            'name' => 'iOS',
            'color' => 'FF0000',
            'course_id' => 1,
            'student_id' => 1,
            'google_id' => "2",
        ]);
        DB::table('subjects')->insert([
            'name' => 'iOS',
            'color' => 'FF0000',
            'course_id' => 1,
            'student_id' => 2,
            'google_id' => "2",
        ]);

        DB::table('periods')->insert([
            'name' => 'Primer trimestre',
            'date_start' => '2022-01-05',
            'date_end' => '2022-05-24',
            'student_id' => 1,
        ]);
        DB::table('periods')->insert([
            'name' => 'Trimestre 1',
            'date_start' => '2022-01-01',
            'date_end' => '2022-06-01',
            'student_id' => 2,
        ]);
    }
}
