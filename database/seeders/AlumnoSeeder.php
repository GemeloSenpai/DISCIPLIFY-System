<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AlumnoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('alumno_curso')->insert([
            ['user_id' => 6, 'id_cursos' => 1],
            ['user_id' => 7, 'id_cursos' => 1],
            ['user_id' => 8, 'id_cursos' => 2],
            ['user_id' => 9, 'id_cursos' => 3],
            ['user_id' => 10, 'id_cursos' => 4],
        ]);
    }
}
