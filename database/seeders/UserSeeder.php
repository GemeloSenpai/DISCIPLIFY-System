<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            ['name' => 'Admin General', 'email' => 'admin@correo.test', 'password' => Hash::make('password123'), 'role' => 'admin', 'estado' => 'activo', 'nombre_completo' => null, 'fecha_nacimiento' => null, 'DNI' => null, 'telefono' => null, 'direccion' => null],
            ['name' => 'Carlos Díaz', 'email' => 'carlos.díaz@correo.test', 'password' => Hash::make('password123'), 'role' => 'maestro', 'estado' => 'activo', 'nombre_completo' => null, 'fecha_nacimiento' => null, 'DNI' => null, 'telefono' => null, 'direccion' => null],
            ['name' => 'Ana Gómez', 'email' => 'ana.gómez@correo.test', 'password' => Hash::make('password123'), 'role' => 'maestro', 'estado' => 'activo', 'nombre_completo' => null, 'fecha_nacimiento' => null, 'DNI' => null, 'telefono' => null, 'direccion' => null],
            ['name' => 'Luis Fernández', 'email' => 'luis.fernandez@correo.test', 'password' => Hash::make('password123'), 'role' => 'maestro', 'estado' => 'activo', 'nombre_completo' => null, 'fecha_nacimiento' => null, 'DNI' => null, 'telefono' => null, 'direccion' => null],
            ['name' => 'María López', 'email' => 'maria.lopez@correo.test', 'password' => Hash::make('password123'), 'role' => 'maestro', 'estado' => 'activo', 'nombre_completo' => null, 'fecha_nacimiento' => null, 'DNI' => null, 'telefono' => null, 'direccion' => null],
            ['name' => 'Aleyda Licona', 'email' => 'aleyda.licona@correo.test', 'password' => Hash::make('password123'), 'role' => 'alumno', 'estado' => 'activo', 'nombre_completo' => null, 'fecha_nacimiento' => null, 'DNI' => null, 'telefono' => '33483536', 'direccion' => null],
            ['name' => 'Ángela Murillo', 'email' => 'angela.murillo@correo.test', 'password' => Hash::make('password123'), 'role' => 'alumno', 'estado' => 'activo', 'nombre_completo' => null, 'fecha_nacimiento' => null, 'DNI' => null, 'telefono' => '98722692', 'direccion' => null],
            ['name' => 'Elvin Romero', 'email' => 'elvin.romero@correo.test', 'password' => Hash::make('password123'), 'role' => 'alumno', 'estado' => 'activo', 'nombre_completo' => null, 'fecha_nacimiento' => null, 'DNI' => null, 'telefono' => '99370406', 'direccion' => null],
            ['name' => 'Emely Sarmiento', 'email' => 'emely.sarmiento@correo.test', 'password' => Hash::make('password123'), 'role' => 'alumno', 'estado' => 'activo', 'nombre_completo' => null, 'fecha_nacimiento' => null, 'DNI' => null, 'telefono' => '97147826', 'direccion' => null],
            ['name' => 'Jelin Soler', 'email' => 'jelin.soler@correo.test', 'password' => Hash::make('password123'), 'role' => 'alumno', 'estado' => 'activo', 'nombre_completo' => null, 'fecha_nacimiento' => null, 'DNI' => null, 'telefono' => '98228201', 'direccion' => null],
        ]);
    }
}
