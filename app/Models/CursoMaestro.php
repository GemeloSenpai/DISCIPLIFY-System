<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CursoMaestro extends Model
{
    protected $table = 'curso_maestro';
    protected $primaryKey = 'id_curso_maestro';

    protected $fillable = ['id_cursos', 'user_id'];

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'id_cursos');
    }

    public function maestro()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

