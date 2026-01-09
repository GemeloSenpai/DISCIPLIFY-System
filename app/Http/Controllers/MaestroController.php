<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Curso;
use Illuminate\Support\Facades\Auth;
use App\Models\Tarea;
use Illuminate\Support\Facades\DB;


class MaestroController extends Controller
{
    public function cursos()
{
     // Antes: return view('maestro.cursos', compact('cursos'));
    return redirect()->route('maestro.dashboard');
}


    public function dashboard()
    {
        $maestro = Auth::user();

        $cursos = $maestro->cursos()
            ->withCount(['alumnos', 'tareas'])
            ->addSelect([
                // 🔵 Total de entregas realizadas (tipo=tarea y entregado=1) por curso
                'entregas_realizadas' => DB::table('calificaciones')
                    ->whereColumn('calificaciones.id_cursos', 'cursos.id_cursos')
                    ->where('tipo', 'tarea')
                    ->where('entregado', 1)
                    ->selectRaw('COUNT(*)'),
            ])
            ->get();

        return view('maestro.dashboard', compact('cursos'));
    }

public function showCurso($id)
{
    $user = \Auth::user();
    $isAdmin = method_exists($user, 'hasRole')
        ? $user->hasRole('admin')
        : (($user->role ?? null) === 'admin');

    $q = \App\Models\Curso::withCount('tareas')
        ->with(['maestro'])
        ->where('id_cursos', $id);

    if (!$isAdmin) {
        $q->whereHas('maestro', function ($sub) {
            $sub->where('user_id', auth()->id());
        });
    }

    $curso = $q->firstOrFail();

    // 👉 Contexto de curso
    session([
        'curso_actual' => $curso->id_cursos,
        'curso_titulo' => $curso->titulo,
    ]);

    // ... lo demás queda igual
    $alumnos = $curso->alumnos()
        ->select('users.id', 'users.nombre_completo')
        ->orderBy('users.nombre_completo')
        ->get();

    $totalTareas = \DB::table('tareas')
        ->where('id_cursos', $curso->id_cursos)
        ->count();

    $conteos = \DB::table('calificaciones as c')
        ->join('tareas as t', 't.id_tareas', '=', 'c.tarea_id')
        ->where('t.id_cursos', $curso->id_cursos)
        ->groupBy('c.alumno_id')
        ->select('c.alumno_id', \DB::raw('SUM(CASE WHEN c.entregado = 1 THEN 1 ELSE 0 END) AS entregadas'))
        ->pluck('entregadas', 'c.alumno_id');

    $maestros = $curso->maestro;
    $tareas   = $curso->tareas;

    return view('maestro.detalles', compact('curso','maestros','alumnos','tareas','totalTareas','conteos'));
}





    public function actualizarEntrega(Request $request)
    {
        $data = $request->validate([
            'alumno_id' => 'required|exists:users,id',
            'tarea_id'  => 'required|exists:tareas,id_tareas',
            'entregada' => 'required|boolean',
        ]);

        \App\Models\Entrega::updateOrCreate(
            [
                'alumno_id' => $data['alumno_id'],
                'tarea_id'  => $data['tarea_id'],
            ],
            [
                'entregada' => $data['entregada'],
            ]
        );

        return response()->json(['success' => true]);
    }

public function storeTarea(Request $request)
{
    $validated = $request->validate([
        'id_cursos'     => ['required','exists:cursos,id_cursos'],
        'titulo'        => ['required','string','max:255'],
        'descripcion'   => ['required','string'],
        'fecha_entrega' => ['nullable','date'],
    ]);

    // Siguiente número correlativo de tarea en el curso
    $nextNumero = DB::table('tareas')
        ->where('id_cursos', $validated['id_cursos'])
        ->count() + 1;

    // Crear la tarea
    $tarea = Tarea::create([
        'id_cursos'     => $validated['id_cursos'],
        'titulo'        => $validated['titulo'],
        'descripcion'   => $validated['descripcion'],
        'fecha_entrega' => $validated['fecha_entrega'] ?? null,
    ]);

    // Alumnos del curso: solo IDs válidos en users (evita FK rota)
    $alumnos = DB::table('alumno_curso as ac')
        ->join('users as u', 'u.id', '=', 'ac.user_id')
        ->where('ac.id_cursos', $validated['id_cursos'])
        ->where('u.role', 'alumno') // quita esta línea si no usas el campo role
        ->pluck('u.id')
        ->unique();

    // Inserción masiva en calificaciones
    $now  = now();
    $rows = [];
    foreach ($alumnos as $alumnoId) {
        $rows[] = [
            'alumno_id'  => $alumnoId,
            'id_cursos'  => $validated['id_cursos'],
            'tarea_id'   => $tarea->id_tareas,
            'tipo'       => 'tarea',
            'numero'     => $nextNumero,
            'nota'       => null,
            'entregado'  => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
    if (!empty($rows)) {
        DB::table('calificaciones')->insert($rows);
    }

    return response()->json([
        'success'  => true,
        'tarea_id' => $tarea->id_tareas,
        'numero'   => $nextNumero,
    ], 201);
}


public function upsertCalificacion(Request $request)
{
    $data = $request->validate([
        'alumno_id' => ['required','exists:users,id'],
        'id_cursos' => ['nullable','exists:cursos,id_cursos'],      // la inferimos si falta
        'tarea_id'  => ['nullable','exists:tareas,id_tareas'],       // SOLO requerido si tipo=tarea
        'tipo'      => ['required','in:tarea,evaluacion,examen'],
        'numero'    => ['required','integer','min:1'],
        'nota'      => ['nullable','numeric','between:0,100'],
        'entregado' => ['nullable','boolean'],
    ]);

    // Si es tarea y no mandaron tarea_id => error explícito
    if ($data['tipo'] === 'tarea' && empty($data['tarea_id'])) {
        return response()->json(['message' => 'tarea_id es obligatorio para tipo=tarea'], 422);
    }

    // Si no mandan id_cursos lo inferimos (desde la tarea si es tipo tarea)
    if (empty($data['id_cursos'])) {
        if (!empty($data['tarea_id'])) {
            $data['id_cursos'] = DB::table('tareas')
                ->where('id_tareas', $data['tarea_id'])
                ->value('id_cursos');
        } else {
            // Como fallback en eval/examen: usa curso de sesión guardado si lo manejas en sesión
            $data['id_cursos'] = session('curso_actual');
        }
    }

    // Reglas de "entregado"
    // - Si llega nota (no null) y es tarea => entregado = 1 (salvo que venga explícito)
    // - Para eval/examen no tocamos "entregado" (no aplica), lo dejamos 0/null
    $entregado = $data['entregado'] ?? null;
    if ($data['tipo'] === 'tarea') {
        if ($entregado === null) {
            $entregado = $data['nota'] !== null ? 1 : 0;
        }
    } else {
        $entregado = 0;
    }

    $now = now();

    if ($data['tipo'] === 'tarea') {
        // Clave única por alumno-curso-tarea
        DB::table('calificaciones')->updateOrInsert(
            [
                'alumno_id' => $data['alumno_id'],
                'id_cursos' => $data['id_cursos'],
                'tarea_id'  => $data['tarea_id'],
            ],
            [
                'tipo'       => 'tarea',
                'numero'     => $data['numero'],
                'nota'       => $data['nota'],
                'entregado'  => $entregado,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
    } else {
        // Clave única por alumno-curso-tipo-numero (sin tarea_id)
        DB::table('calificaciones')->updateOrInsert(
            [
                'alumno_id' => $data['alumno_id'],
                'id_cursos' => $data['id_cursos'],
                'tipo'      => $data['tipo'],
                'numero'    => $data['numero'],
            ],
            [
                'tarea_id'   => null,         // asegúrate que sea NULLABLE en BD
                'nota'       => $data['nota'],
                'entregado'  => 0,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
    }

    return response()->json(['success' => true], 200);
}

public function updateTarea(Request $request, \App\Models\Tarea $tarea)
{
    // Verifica que el maestro sea dueño del curso de la tarea
    \App\Models\Curso::where('id_cursos', $tarea->id_cursos)
        ->whereHas('maestro', fn($q) => $q->where('user_id', auth()->id()))
        ->firstOrFail();

    $data = $request->validate([
        'titulo'        => ['required','string','max:255'],
        'descripcion'   => ['required','string'],
        'fecha_entrega' => ['nullable','date'],
    ]);

    $tarea->update($data);

    return response()->json(['success' => true, 'tarea_id' => $tarea->id_tareas], 200);
}

public function destroyTarea(Request $request, \App\Models\Tarea $tarea)
{
    // Verifica que el maestro sea dueño del curso
    \App\Models\Curso::where('id_cursos', $tarea->id_cursos)
        ->whereHas('maestro', fn($q) => $q->where('user_id', auth()->id()))
        ->firstOrFail();

    DB::transaction(function () use ($tarea) {
        $cursoId = $tarea->id_cursos;

        // borra calificaciones de esa tarea (por si no hay FK ON DELETE CASCADE)
        DB::table('calificaciones')->where('tarea_id', $tarea->id_tareas)->delete();

        // borra la tarea
        $tarea->delete();

        // renumera 'numero' en calificaciones para tareas restantes del curso
        $ids = DB::table('tareas')
            ->where('id_cursos', $cursoId)
            ->orderBy('id_tareas')
            ->pluck('id_tareas')
            ->values();

        $n = 1;
        foreach ($ids as $tid) {
            DB::table('calificaciones')
              ->where('tarea_id', $tid)
              ->update(['numero' => $n]);
            $n++;
        }
    });

    return response()->json(['success' => true], 200);
}


public function pendientesTarea(\App\Models\Tarea $tarea)
    {
        // Asegura que el mentor es dueño del curso de la tarea
        \App\Models\Curso::where('id_cursos', $tarea->id_cursos)
            ->whereHas('maestro', fn($q) => $q->where('user_id', auth()->id()))
            ->firstOrFail();

        $pendientes = DB::table('alumno_curso as ac')
            ->join('users as u', 'u.id', '=', 'ac.user_id')
            ->leftJoin('calificaciones as c', function ($j) use ($tarea) {
                $j->on('c.alumno_id', '=', 'u.id')
                  ->where('c.tarea_id', '=', $tarea->id_tareas)
                  ->where('c.tipo', '=', 'tarea');
            })
            ->where('ac.id_cursos', $tarea->id_cursos)
            // No existe registro o está marcado como NO entregado
            ->where(function ($q) {
                $q->whereNull('c.entregado')->orWhere('c.entregado', 0);
            })
            ->orderBy('u.nombre_completo')
            ->get([
                'u.id', 'u.nombre_completo', 'u.email', 'u.telefono'
            ]);

        return response()->json([
            'tarea_id'     => $tarea->id_tareas,
            'tarea_titulo' => $tarea->titulo,
            'curso_id'     => $tarea->id_cursos,
            'pendientes'   => $pendientes,
        ]);
    }

}