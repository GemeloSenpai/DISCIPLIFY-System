<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('calificaciones', function (Blueprint $table) {
            $table->id('id_calificacion');
            
            // Relación con usuarios (alumnos)
            $table->unsignedBigInteger('alumno_id');
            $table->foreign('alumno_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');

            // Relación con cursos
            $table->unsignedBigInteger('id_cursos');
            $table->foreign('id_cursos')
                  ->references('id_cursos')->on('cursos')
                  ->onDelete('cascade');

            // Relación opcional con tareas
            $table->unsignedBigInteger('tarea_id')->nullable();
            $table->foreign('tarea_id')
                  ->references('id_tareas')->on('tareas')
                  ->onDelete('cascade');

            // Datos de calificación
            $table->enum('tipo', ['tarea', 'evaluacion', 'examen']);
            $table->integer('numero')->nullable()->comment('Número de tarea o evaluación');
            $table->decimal('nota', 5, 2)->nullable();
            $table->boolean('entregado')->nullable()->comment('Solo para tareas');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calificaciones');
    }
};

