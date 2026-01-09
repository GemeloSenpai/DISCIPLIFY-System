<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sesion extends Model
{
    protected $table = 'sesiones';
    protected $primaryKey = 'id_sesiones';
    protected $fillable = ['curso_id','fecha','tipo','tema','observaciones'];

    public function curso(): BelongsTo {
        return $this->belongsTo(Curso::class, 'curso_id', 'id_cursos');
    }

    public function asistencias(): HasMany {
        return $this->hasMany(Asistencia::class, 'sesion_id', 'id_sesiones');
    }

    public function getRouteKeyName()
    {
        return 'id_sesiones';
    }
}
