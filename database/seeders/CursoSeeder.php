<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CursoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cursos')->insert([
            ['id_cursos' => 1, 'maestro_id' => 2, 'titulo' => 'Madurez Espiritual', 'descripcion' => 'Curso de crecimiento espiritual'],
            ['id_cursos' => 2, 'maestro_id' => 4, 'titulo' => 'Pre-Discipulado', 'descripcion' => 'Curso de introducción al discipulado'],
            ['id_cursos' => 3, 'maestro_id' => 2, 'titulo' => 'Proverbios de Salomón', 'descripcion' => 'Sabiduría práctica para la vida'],
            ['id_cursos' => 4, 'maestro_id' => 3, 'titulo' => 'Realidades de la Cruz', 'descripcion' => 'Profundización en el mensaje de la cruz'],
            ['id_cursos' => 5, 'maestro_id' => 4, 'titulo' => 'Corintios & Gracia', 'descripcion' => 'Estudio de Corintios y la gracia'],
            ['id_cursos' => 6, 'maestro_id' => 5, 'titulo' => 'Enviados - Misiones', 'descripcion' => 'Curso sobre misiones y el llamado de Dios'],
        ]);
    }
}
