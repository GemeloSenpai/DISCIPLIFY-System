<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entrega extends Model
{
    protected $table = 'entregas';
    protected $primaryKey = 'id_entregas';

    protected $fillable = [
        'tarea_id', 'alumno_id', 'entregada', 'fecha_entrega'
    ];
   
    public function tarea()
    {
        return $this->belongsTo(Tarea::class, 'tarea_id');
    }

    public function alumno()
    {
        return $this->belongsTo(User::class, 'alumno_id');
    }
}

