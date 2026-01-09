<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlumnoCurso extends Model
{
    protected $table = 'alumno_curso';
    protected $primaryKey = 'id_alumno_curso';

    protected $fillable = ['user_id', 'id_cursos'];

    public function alumno()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'id_cursos');
    }
}
