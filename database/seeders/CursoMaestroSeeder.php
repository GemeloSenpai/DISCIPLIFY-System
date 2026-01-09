<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CursoMaestroSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('curso_maestro')->insert([
            [
                'id_curso_maestro' => 1,
                'id_cursos' => 1,
                'user_id' => 2, // maestro 1
            ],
            [
                'id_curso_maestro' => 2,
                'id_cursos' => 2,
                'user_id' => 3, // maestro 2
            ],
            [
                'id_curso_maestro' => 3,
                'id_cursos' => 3,
                'user_id' => 4, // maestro 3
            ],
            [
                'id_curso_maestro' => 4,
                'id_cursos' => 4,
                'user_id' => 5, // maestro 4
            ],
            [
                'id_curso_maestro' => 5,
                'id_cursos' => 5,
                'user_id' => 6, // maestro 5
            ],
        ]);
    }
}
