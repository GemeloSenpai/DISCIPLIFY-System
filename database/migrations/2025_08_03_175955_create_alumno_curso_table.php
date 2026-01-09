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
        Schema::create('alumno_curso', function (Blueprint $table) {
            $table->id('id_alumno_curso');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_cursos')->constrained('cursos', 'id_cursos')->onDelete('cascade');
            $table->timestamps();

            $table->unique('user_id'); // Solo un curso por alumno
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumno_curso');
    }
};
