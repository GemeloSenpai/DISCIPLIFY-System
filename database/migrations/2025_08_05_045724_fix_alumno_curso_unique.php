<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumno_curso', function (Blueprint $table) {
            // Elimina primero la foreign key
            $table->dropForeign(['user_id']);

            // Luego elimina el índice único
            $table->dropUnique('alumno_curso_user_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('alumno_curso', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'id_cursos']);
            $table->unique('user_id');
        });
    }

};
