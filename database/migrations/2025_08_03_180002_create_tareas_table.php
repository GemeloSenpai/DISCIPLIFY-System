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
        Schema::create('tareas', function (Blueprint $table) {
            $table->id('id_tareas');
            $table->foreignId('id_cursos')->constrained('cursos', 'id_cursos')->onDelete('cascade');
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->date('fecha_entrega')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tareas');
    }
};
