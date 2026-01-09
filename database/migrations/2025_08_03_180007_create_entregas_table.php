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
        Schema::create('entregas', function (Blueprint $table) {
            $table->id('id_entregas');
            $table->foreignId('tarea_id')->constrained('tareas', 'id_tareas')->onDelete('cascade');
            $table->foreignId('alumno_id')->constrained('users')->onDelete('cascade');
            $table->boolean('entregada')->default(false);
            $table->timestamp('fecha_entrega')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entregas');
    }
};
