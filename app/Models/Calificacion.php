<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calificacion extends Model
{
    use HasFactory;

    protected $table = 'calificaciones';
    protected $primaryKey = 'id_calificacion';

    protected $fillable = [
        'alumno_id',
        'id_cursos',
        'tarea_id',
        'tipo',
        'numero',
        'nota',
        'entregado',
    ];


    /* explicacion de las relaciones
    $calificacion->alumno → Devuelve el usuario/alumno dueño de esa calificación.
    $calificacion->curso → Devuelve el curso al que pertenece.
    $calificacion->tarea → Devuelve la tarea (solo si es tipo tarea y tiene tarea_id).
    */
    
    /**
     * Relación con el alumno (User)
     */
    public function alumno()
    {
        return $this->belongsTo(User::class, 'alumno_id');
    }

    /**
     * Relación con el curso
     */
    public function curso()
    {
        return $this->belongsTo(Curso::class, 'id_cursos', 'id_cursos');
    }

    /**
     * Relación con la tarea (opcional)
     */
    public function tarea()
    {
        return $this->belongsTo(Tarea::class, 'tarea_id', 'id_tareas');
    }

    
}
