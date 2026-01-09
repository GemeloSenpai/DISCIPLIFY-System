<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'maestro', 'alumno']); // Tres roles
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->rememberToken();

            // Personalizados
            $table->string('nombre_completo')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('DNI', 50)->nullable();
            $table->string('telefono', 50)->nullable();
            $table->text('direccion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
