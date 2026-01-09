<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('sesiones', function (Blueprint $table) {
            $table->bigIncrements('id_sesiones');
            $table->unsignedBigInteger('curso_id');               // cursos.id_cursos
            $table->date('fecha');                                // viernes
            $table->enum('tipo', ['con_clase','sin_clase'])->default('con_clase'); // "no hubo clase" -> sin_clase
            $table->string('tema')->nullable();
            $table->string('observaciones', 500)->nullable();
            $table->unique(['curso_id','fecha']);                 // 1 sesión por curso/fecha
            $table->foreign('curso_id')->references('id_cursos')->on('cursos')->onDelete('cascade');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('sesiones'); }
};

