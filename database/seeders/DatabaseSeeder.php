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
            'name' => "Daniel Ximenez",
            'email' => "danixidev@gmail.com",
            'image_url' => "image.png",
        ]);
        DB::table('subjects')->insert([
            'name' => "Acceso a datos",
            'color' => "red",
        ]);
    }
}
