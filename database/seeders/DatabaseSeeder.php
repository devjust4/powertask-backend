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
    }
}
