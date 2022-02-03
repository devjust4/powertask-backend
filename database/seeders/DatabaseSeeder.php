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
        ]);
        DB::table('courses')->insert([
            'name' => 'Curso 1',
            'student_id' => 1,
        ]);
        DB::table('subjects')->insert([
            'name' => 'Acceso a Datos',
            'color' => 'FF0000',
            'course_id' => 1,
        ]);
    }
}
