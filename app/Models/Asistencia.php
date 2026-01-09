<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    protected $table = 'asistencias';
    protected $primaryKey = 'id_asistencias';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = ['sesion_id','alumno_id','estado','hora_llegada','comentario'];

    // ✅ Para que {asistencia} en la ruta busque por id_asistencias y no por id
    public function getRouteKeyName()
    {
        return 'id_asistencias';
    }

    public function alumno()
    {
        return $this->belongsTo(User::class, 'alumno_id', 'id');
    }

    public function sesion()
    {
        return $this->belongsTo(Sesion::class, 'sesion_id', 'id_sesiones');
    }
}


