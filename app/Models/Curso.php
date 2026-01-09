<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;


class Curso extends Model
{
    protected $table = 'cursos';
    protected $primaryKey = 'id_cursos';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = ['titulo', 'descripcion'];

    public function maestro()
    {
        return $this->belongsToMany(User::class, 'curso_maestro', 'id_cursos', 'user_id');
    }

    public function alumnos()
    {
        return $this->belongsToMany(User::class, 'alumno_curso', 'id_cursos', 'user_id');
    }

    public function tareas()
    {
        return $this->hasMany(Tarea::class, 'id_cursos');
    }

    public function maestros()
    {
        return $this->belongsToMany(User::class, 'curso_maestro', 'id_cursos', 'user_id');
    }

    /**
    * Relación: Un curso tiene muchas calificaciones
    */
    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class, 'id_cursos', 'id_cursos');
    }

    public function getRouteKeyName()
    {
        return 'id_cursos';
    }


}
