<?php

namespace App\Http\Controllers;

use App\Models\Sesion;
use App\Models\Curso;
use App\Models\Asistencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str; 

class SesionController extends Controller
{
    /**
     * Lista de sesiones de un curso.
     */
public function index(\App\Models\Curso $curso)
{
    $sesiones = \App\Models\Sesion::where('curso_id', $curso->id_cursos)
        ->withCount('asistencias')   // 👈 trae asistencias_count
        ->orderByDesc('fecha')
        ->get();

    $totalAlumnos = $curso->alumnos()->count(); // 👈 total del curso (una sola vez)

    return view('maestro.asistencias.index', compact('curso', 'sesiones', 'totalAlumnos'));
}
    /**
     * Crea una sesión (con_clase o sin_clase) y, si es con_clase,
     * precrea las asistencias para todos los alumnos inscritos al curso.
     */
   public function store(Request $request, Curso $curso)
{
    $data = $request->validate([
        'fecha'         => ['required','date'],
        'tipo'          => ['required','in:con_clase,sin_clase'],
        'tema'          => ['nullable','string','max:255'],
        'observaciones' => ['nullable','string','max:500'],
    ]);

    // 1) ¿Ya existe sesión para ese curso/fecha?
    $existing = Sesion::where('curso_id', $curso->id_cursos)
        ->whereDate('fecha', $data['fecha'])
        ->first();

    if ($existing) {
        // Si mandaron cambios (tipo, tema, observaciones) y difieren, se actualiza
        $toUpdate = [];
        foreach (['tipo','tema','observaciones'] as $f) {
            if (array_key_exists($f, $data) && $data[$f] !== $existing->$f) {
                $toUpdate[$f] = $data[$f];
            }
        }
        if ($toUpdate) {
            $existing->fill($toUpdate)->save();
        }

        // Si es con clase, crear asistencias faltantes (sin duplicar)
        $insertados = 0;
        if ($existing->tipo === 'con_clase') {
            $alumnosIds = \DB::table('alumno_curso as ac')
                ->join('users as u', 'u.id', '=', 'ac.user_id')
                ->where('ac.id_cursos', $curso->id_cursos)
                ->where('u.role', 'alumno')
                ->pluck('u.id');

            if ($alumnosIds->isNotEmpty()) {
                $ya = Asistencia::where('sesion_id', $existing->id_sesiones)->pluck('alumno_id');
                $faltantes = $alumnosIds->diff($ya);

                if ($faltantes->isNotEmpty()) {
                    $now  = now();
                    $hora = $now->format('H:i');
                    $rows = $faltantes->map(fn ($alumnoId) => [
                        'sesion_id'     => $existing->id_sesiones,
                        'alumno_id'     => $alumnoId,
                        'estado'        => 'presente',
                        'hora_llegada'  => $hora,
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ])->all();
                    Asistencia::insert($rows);
                    $insertados = count($rows);
                }
            }
        }

        return redirect()
            ->route('maestro.sesiones.show', [$curso->id_cursos, $existing->id_sesiones])
            ->with([
                'exists'     => true,
                'insertados' => $insertados,
                'info'       => 'Ya existía una sesión para esa fecha. Se abrió la existente.',
            ]);
    }

    // 2) Crear nueva sesión (y pre-crear asistencias si es con clase)
    $insertados = 0;
    DB::transaction(function () use ($data, $curso, &$insertados) {
        $sesion = Sesion::create([
            'curso_id'      => $curso->id_cursos,
            'fecha'         => $data['fecha'],
            'tipo'          => $data['tipo'],
            'tema'          => $data['tema'] ?? null,
            'observaciones' => $data['observaciones'] ?? null,
        ]);

        if ($sesion->tipo === 'con_clase') {
            $alumnosIds = \DB::table('alumno_curso as ac')
                ->join('users as u', 'u.id', '=', 'ac.user_id')
                ->where('ac.id_cursos', $curso->id_cursos)
                ->where('u.role', 'alumno')
                ->pluck('u.id');

            if ($alumnosIds->isNotEmpty()) {
                $now  = now();
                $hora = $now->format('H:i');

                $rows = $alumnosIds->map(fn ($alumnoId) => [
                    'sesion_id'     => $sesion->id_sesiones,
                    'alumno_id'     => $alumnoId,
                    'estado'        => 'presente',
                    'hora_llegada'  => $hora,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ])->all();

                Asistencia::insert($rows);
                $insertados = count($rows);
            }
        }

        // Guardamos el id en request para usarlo fuera del closure
        request()->merge(['_created_sesion_id' => $sesion->id_sesiones]);
    });

    $sesionId = (int) request('_created_sesion_id');

    return redirect()
        ->route('maestro.sesiones.show', [$curso->id_cursos, $sesionId])
        ->with([
            'created'    => true,
            'insertados' => $insertados,
            'success'    => 'Sesión creada y asistencia inicial registrada.',
        ]);
}




    /**
     * Mostrar la sesión para pasar lista.
     */
    public function show(Request $request, Curso $curso, Sesion $sesion)
{
    // Seguridad: la sesión debe pertenecer al curso
    if ((int) $sesion->curso_id !== (int) $curso->id_cursos) {
        abort(404);
    }

    // Cargar asistencias + alumno
    $sesion->load(['asistencias.alumno']);

    // Ordenar por nombre (igual que “Detalles”)
    $ordenadas = $sesion->asistencias
        ->sortBy(function ($a) {
            $n = $a->alumno->nombre_completo ?? $a->alumno->name ?? '';
            return Str::lower($n);
        }, SORT_NATURAL)
        ->values();

    // Sobrescribir la relación con la colección ordenada
    $sesion->setRelation('asistencias', $ordenadas);

    // Pasar también el modo solo lectura
    $readonly = $request->boolean('view');

    return view('maestro.asistencias.tomar', compact('curso', 'sesion', 'readonly'));
}

public function resultados(\App\Models\Curso $curso)
{

    $user = Auth::user();
    $isAdmin = method_exists($user, 'hasRole')
        ? $user->hasRole('admin')
        : (($user->role ?? null) === 'admin');

    // Si NO es admin, exigir que sea maestro del curso
    if (!$isAdmin && !$curso->maestro()->where('user_id', $user->id)->exists()) {
        abort(403);
    }

    // Sesiones del curso (orden cronológico)
    $sesiones = \App\Models\Sesion::where('curso_id', $curso->id_cursos)
        ->orderBy('fecha')
        ->get(['id_sesiones','fecha','tipo','tema']);

    // Solo contaremos faltas en sesiones "con_clase"
    $sesionesConClase = $sesiones->where('tipo', 'con_clase')->values();
    $sesionIdsConClase = $sesionesConClase->pluck('id_sesiones');

    // Alumnos inscritos
    $alumnos = $curso->alumnos()
        ->select('users.id', 'users.nombre_completo')
        ->orderBy('users.nombre_completo')
        ->get();

    // Asistencias de esas sesiones para esos alumnos
    $asistencias = \App\Models\Asistencia::whereIn('sesion_id', $sesionIdsConClase)
        ->whereIn('alumno_id', $alumnos->pluck('id'))
        ->get(['sesion_id','alumno_id','estado','hora_llegada','comentario']);

    // Mapa rápido: [alumno_id][sesion_id] => asistencia
    $asisMap = [];
    foreach ($asistencias as $a) {
        $asisMap[$a->alumno_id][$a->sesion_id] = [
            'estado'       => $a->estado,
            'hora_llegada' => $a->hora_llegada,
            'comentario'   => $a->comentario,
        ];
    }

    // Contar faltas y determinar elegibilidad a examen (>=3 ausencias => pierde)
    $faltas = [];
    $pierde = [];
    foreach ($alumnos as $al) {
        $count = 0;
        foreach ($sesionesConClase as $s) {
            $estado = $asisMap[$al->id][$s->id_sesiones]['estado'] ?? null;
            if ($estado === 'ausente') $count++;
        }
        $faltas[$al->id] = $count;
        $pierde[$al->id] = $count >= 3; // regla de negocio
    }

    return view('maestro.asistencias.resultados', [
        'curso'             => $curso,
        'sesiones'          => $sesiones,           // mostramos todas (incluye 'sin_clase')
        'sesionesConClase'  => $sesionesConClase,   // para totales/cálculos
        'alumnos'           => $alumnos,
        'asisMap'           => $asisMap,
        'faltas'            => $faltas,
        'pierde'            => $pierde,
    ]);
}

public function destroy(Curso $curso, Sesion $sesion, \Illuminate\Http\Request $request)
{
    // 1) La sesión debe pertenecer al curso de la URL
    if ((int)$sesion->curso_id !== (int)$curso->id_cursos) {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => false, 'message' => 'La sesión no pertenece a este curso.'], 404);
        }
        return back()->with('warning', 'La sesión no pertenece a este curso.');
    }

    // 2) El maestro autenticado debe ser dueño del curso
    if (!$curso->maestro()->where('user_id', auth()->id())->exists()) {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }
        return back()->with('warning', 'No estás autorizado para eliminar esta sesión.');
    }

    try {
        \DB::transaction(function () use ($sesion) {
            \App\Models\Asistencia::where('sesion_id', $sesion->id_sesiones)->delete();
            $sesion->delete();
        });

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true], 200);
        }
        return back()->with('success', 'Sesión eliminada.');
    } catch (\Throwable $e) {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => false, 'message' => 'Error: '.$e->getMessage()], 500);
        }
        return back()->with('error', 'No se pudo eliminar la sesión.');
    }
}


public function update($cursoId, $sesionId, \Illuminate\Http\Request $request)
{
    $data = $request->validate([
        'tipo' => ['required','in:con_clase,sin_clase'], // <- antes decía 'clase'
    ]);

    \DB::table('sesiones')
        ->where('id_sesiones', $sesionId)  // <- nombre correcto de PK
        ->where('curso_id', $cursoId)      // <- nombre correcto de FK
        ->update(array_merge($data, ['updated_at' => now()]));

    return response()->json(['ok' => true]);
}




}

