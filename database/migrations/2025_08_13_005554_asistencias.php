<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('asistencias', function (Blueprint $table) {
            $table->bigIncrements('id_asistencias');
            $table->unsignedBigInteger('sesion_id');              // sesiones.id_sesiones
            $table->unsignedBigInteger('alumno_id');              // users.id (role = alumno)
            $table->enum('estado', ['presente','tarde','ausente','justificado'])->default('ausente');
            $table->time('hora_llegada')->nullable();
            $table->string('comentario', 300)->nullable();

            $table->unique(['sesion_id','alumno_id']);
            $table->foreign('sesion_id')->references('id_sesiones')->on('sesiones')->onDelete('cascade');
            $table->foreign('alumno_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('asistencias'); }
};
