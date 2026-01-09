<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TareaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tareas')->insert([
            ['id_tareas' => 1, 'id_cursos' => 1, 'titulo' => 'Lectura del capítulo 1', 'descripcion' => 'Responder preguntas del capítulo', 'fecha_entrega' => Carbon::now()->addDays(2)],
            ['id_tareas' => 2, 'id_cursos' => 1, 'titulo' => 'Reflexión espiritual', 'descripcion' => 'Redactar una reflexión', 'fecha_entrega' => Carbon::now()->addDays(5)],
            ['id_tareas' => 3, 'id_cursos' => 2, 'titulo' => 'Cuestionario introductorio', 'descripcion' => null, 'fecha_entrega' => Carbon::now()],
        ]);
    }
}
