<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AlumnoCursoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('alumno_curso')->insert([
            ['id_alumno_curso' => 1, 'user_id' => 6, 'id_cursos' => 1],
            ['id_alumno_curso' => 2, 'user_id' => 7, 'id_cursos' => 1],
            ['id_alumno_curso' => 3, 'user_id' => 8, 'id_cursos' => 2],
            ['id_alumno_curso' => 4, 'user_id' => 9, 'id_cursos' => 2],
            ['id_alumno_curso' => 5, 'user_id' => 10, 'id_cursos' => 3],
        ]);
    }
}

