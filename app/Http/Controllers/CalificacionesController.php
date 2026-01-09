<?php
// app/Http/Controllers/CalificacionesController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalificacionesController extends Controller
{
    // NUEVO: entra sin id, usa la sesión y redirige al cuadro
    public function desdeSesion()
{
    // Si ya hay curso en sesión, directo al cuadro
    if ($id = session('curso_actual')) {
        return redirect()->route('maestro.calificaciones.cuadro', $id);
    }

    // Si no hay, toma el primer curso del mentor y setea sesión
    $curso = \DB::table('cursos')
        ->join('curso_maestro', 'curso_maestro.id_cursos', '=', 'cursos.id_cursos')
        ->where('curso_maestro.user_id', auth()->id())
        ->orderBy('cursos.titulo')
        ->select('cursos.id_cursos', 'cursos.titulo')
        ->first();

    if ($curso) {
        session([
            'curso_actual' => $curso->id_cursos,
            'curso_titulo' => $curso->titulo,
        ]);
        return redirect()->route('maestro.calificaciones.cuadro', $curso->id_cursos);
    }

    // Sin cursos: vuelve al dashboard con aviso
    return redirect()->route('maestro.dashboard')
        ->with('warning', 'No tienes cursos asignados.');
}
 

    public function cuadro($id_cursos)
{
    $curso = \DB::table('cursos')
        ->where('id_cursos', $id_cursos)
        ->select('id_cursos','titulo')
        ->first();

    if (!$curso) {
        abort(404);
    }

    // 👉 Contexto de curso
    session([
        'curso_actual' => $curso->id_cursos,
        'curso_titulo' => $curso->titulo,
    ]);

    // ... lo demás como lo tienes
    $mentores = \DB::table('curso_maestro')
        ->join('users', 'users.id', '=', 'curso_maestro.user_id')
        ->where('curso_maestro.id_cursos', $id_cursos)
        ->pluck('users.nombre_completo')
        ->toArray();

    if (empty($mentores)) {
        $m = \DB::table('cursos')
            ->join('users','users.id','=','cursos.maestro_id')
            ->where('cursos.id_cursos',$id_cursos)
            ->value('users.nombre_completo');
        if ($m) $mentores = [$m];
    }

    $alumnos = \DB::table('alumno_curso')
        ->join('users', 'users.id', '=', 'alumno_curso.user_id')
        ->where('alumno_curso.id_cursos', $id_cursos)
        ->select('users.id', 'users.nombre_completo', 'users.telefono')
        ->orderBy('users.nombre_completo')
        ->get();

    $tareas = \DB::table('tareas')
        ->where('id_cursos', $id_cursos)
        ->orderBy('id_tareas')
        ->limit(15)
        ->get(['id_tareas','titulo']);

    $califs = \DB::table('calificaciones')
        ->where('id_cursos', $id_cursos)
        ->select('alumno_id','tipo','numero','nota','entregado','tarea_id')
        ->get();

    $byAlumno = [];
    foreach ($califs as $c) {
        $aid = $c->alumno_id;
        $byAlumno[$aid] = $byAlumno[$aid] ?? ['tareas'=>[], 'evals'=>[], 'examen'=>null];

        if ($c->tipo === 'tarea') {
            $n = intval($c->numero ?? 0);
            if ($n > 0) $byAlumno[$aid]['tareas'][$n] = $c->nota;
        } elseif ($c->tipo === 'evaluacion') {
            $n = intval($c->numero ?? 0);
            if ($n > 0) $byAlumno[$aid]['evals'][$n] = $c->nota;
        } elseif ($c->tipo === 'examen') {
            $byAlumno[$aid]['examen'] = $c->nota;
        }
    }

    return view('maestro.calificaciones.cuadro', [
        'cursoId'  => $id_cursos,
        'curso'    => $curso,
        'mentores' => $mentores,
        'alumnos'  => $alumnos,
        'tareas'   => $tareas,
        'byAlumno' => $byAlumno,
    ]);
}


    public function upsert(\Illuminate\Http\Request $request)
{
    $data = $request->validate([
        'alumno_id' => 'required|exists:users,id',
        'id_cursos' => 'required|exists:cursos,id_cursos',
        'tipo'      => 'required|in:tarea,evaluacion,examen',
        'numero'    => 'required|integer|min:1',
        'nota' => 'nullable|numeric|min:0',
        'tarea_id'  => 'nullable|exists:tareas,id_tareas',
    ]);

    // Política básica: solo maestros del curso pueden editar (si ya tienes Policy, úsala aquí)
    // $this->authorize('editarCalificacion', [Curso::class, $data['id_cursos']]);

    // Regla de negocio: entregado se marca si hay nota > 0 (o al menos no null)
    $entregado = isset($data['nota']) ? 1 : null;

    $payload = [
        'nota'      => $data['nota'],
        'entregado' => $entregado,
    ];

    if ($data['tipo'] === 'tarea') {
        // Mantener vínculo con la tarea real si lo tenemos
        $payload['tarea_id'] = $data['tarea_id'] ?? null;
    } else {
        $payload['tarea_id'] = null;
    }

    $cal = \DB::table('calificaciones')->where([
        'alumno_id' => $data['alumno_id'],
        'id_cursos' => $data['id_cursos'],
        'tipo'      => $data['tipo'],
        'numero'    => $data['numero'],
    ])->first();

    if ($cal) {
        \DB::table('calificaciones')->where('id_calificacion', $cal->id_calificacion)->update(array_merge($payload, [
            'updated_at' => now(),
        ]));
        $id = $cal->id_calificacion;
    } else {
        $id = \DB::table('calificaciones')->insertGetId(array_merge([
            'alumno_id' => $data['alumno_id'],
            'id_cursos' => $data['id_cursos'],
            'tipo'      => $data['tipo'],
            'numero'    => $data['numero'],
            'created_at'=> now(),
            'updated_at'=> now(),
        ], $payload));
    }

    return response()->json([
        'ok' => true,
        'id_calificacion' => $id,
        'entregado' => $entregado,
    ]);
}
}
