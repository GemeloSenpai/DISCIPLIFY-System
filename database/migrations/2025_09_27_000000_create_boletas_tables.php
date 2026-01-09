<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Boleta "general" por alumno (1 por alumno). Si luego quieres múltiples, quita el UNIQUE.
        Schema::create('boletas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('alumno_id');
            $table->date('fecha_emision')->nullable(); // editable
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique('alumno_id', 'uniq_boleta_alumno');
            // FK opcional si tu users.id es BIGINT (si no, déjalo sin FK).
            // $table->foreign('alumno_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('boleta_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('boleta_id');
            $table->unsignedBigInteger('curso_id')->nullable();          // solo referencia (opcional)
            $table->string('curso_titulo_snapshot', 255);                 // NOMBRE del curso (snapshot)
            $table->string('nota_nombre', 120)->nullable();               // etiqueta libre: Final, Ajuste, etc.
            $table->decimal('nota_valor', 5, 2)->nullable();              // null = no cuenta para el promedio
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();

            $table->index('boleta_id');
            $table->foreign('boleta_id')->references('id')->on('boletas')->onDelete('cascade');
            // No ponemos FK a cursos porque la boleta debe sobrevivir aunque cambie/eliminemos el curso.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boleta_items');
        Schema::dropIfExists('boletas');
    }
};
