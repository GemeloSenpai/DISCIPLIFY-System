<?php

namespace Database\Factories;

use App\Models\Curso;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;


class CursoFactory extends Factory
{
    protected $model = Curso::class;

    public function definition(): array
    {
        return [
            'maestro_id' => User::where('role', 'maestro')->inRandomOrder()->first()->id ?? 1,
            'titulo' => $this->faker->sentence(3),
            'descripcion' => $this->faker->sentence(8),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

