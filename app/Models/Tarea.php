<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarea extends Model
{
    use HasFactory;

    protected $table = 'tareas';
    protected $primaryKey = 'id_tareas';

    protected $fillable = [
        'id_cursos',
        'titulo',
        'descripcion',
        'fecha_entrega',
    ];

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'id_cursos', 'id_cursos');
    }

    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class, 'tarea_id', 'id_tareas');
    }

    public function getRouteKeyName()
    {
        return 'id_tareas';
    }

}

