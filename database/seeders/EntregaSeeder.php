<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EntregaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('entregas')->insert([
            ['tarea_id' => 1, 'alumno_id' => 6, 'entregada' => true, 'fecha_entrega' => Carbon::now()->subDays(1)],
            ['tarea_id' => 1, 'alumno_id' => 7, 'entregada' => true, 'fecha_entrega' => Carbon::now()->subDays(2)],
            ['tarea_id' => 2, 'alumno_id' => 6, 'entregada' => false, 'fecha_entrega' => null],
            ['tarea_id' => 3, 'alumno_id' => 10, 'entregada' => true, 'fecha_entrega' => Carbon::now()],
        ]);
    }
}
