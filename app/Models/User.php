<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'nombre_completo', 'fecha_nacimiento', 
        'DNI', 'telefono', 'direccion', 'role', 'estado', 
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // app/Models/User.php
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isMaestro(): bool
    {
        return $this->role === 'maestro';
    }

    public function isAlumno(): bool
    {
        return $this->role === 'alumno';
    }

    public function getRoleName(): string
    {
        return match($this->role) {
            'admin'   => 'Administrador',
            'maestro' => 'Maestro',
            'alumno'  => 'Alumno',
            default   => 'Sin rol definido',
        };
    }

    public function cursoAsignado()
    {
        return $this->hasOne(AlumnoCurso::class, 'user_id');
    }

    public function cursoComoMaestro()
    {
        return $this->hasOne(CursoMaestro::class, 'user_id');
    }

    public function entregas()
    {
        return $this->hasMany(Entrega::class, 'alumno_id');
    }

    public function cursosInscritos()
    {
        return $this->belongsToMany(\App\Models\Curso::class, 'alumno_curso', 'user_id', 'id_cursos');
    }

    public function cursos()
    {
        return $this->belongsToMany(Curso::class, 'curso_maestro', 'user_id', 'id_cursos');
    }

    /**
    * Relación: Un usuario/alumno tiene muchas calificaciones
    */
    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class, 'alumno_id', 'id');
    }

}
