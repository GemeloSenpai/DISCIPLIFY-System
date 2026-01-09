<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Orden correcto para evitar errores de clave foránea
        $this->call([
            UserSeeder::class,
            CursoSeeder::class,
            CursoMaestroSeeder::class,
            AlumnoCursoSeeder::class,
            TareaSeeder::class,
            EntregaSeeder::class,
        ]);
    }
}
