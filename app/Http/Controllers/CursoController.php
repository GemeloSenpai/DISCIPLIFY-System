<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Curso;
use App\Models\User;

class CursoController extends Controller
{
    public function index()
    {
        $cursos = Curso::with('maestro')->get();
        return view('admin.cursos.cursos-mostrar', compact('cursos'));
    }

    public function alumnos($id)
    {
        $curso = Curso::findOrFail($id);

        // Obtenemos los alumnos a través de la tabla intermedia
        $alumnos = $curso->alumnos()->get(); // necesitas definir esta relación en el modelo Curso

        return view('admin.cursos.alumnos', compact('curso', 'alumnos'));
    }
}

