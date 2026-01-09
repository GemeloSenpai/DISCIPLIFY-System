<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;


class PromoNewController extends Controller
{
    /**
     * GET /promocion/total
     * Muestra formulario (cursos con alumnos) y, si viene prom_id, muestra resultados de esa corrida.
     */
    // GET /admin/promocion/total
    public function promocionTotal(Request $request)
    {
        // ?prom_ids=1,2,3 tras correr el SP global
        $promIds = collect(explode(',', (string)$request->query('prom_ids')))
            ->filter()->map(fn($v) => (int)$v)->values();

        $bloques = collect();
        $totales = ['aprob'=>0,'mov'=>0,'omit'=>0];

        if ($promIds->count()) {
            $resumenes = DB::table('promociones as p')
                ->leftJoin('cursos as co','co.id_cursos','=','p.curso_origen_id')
                ->leftJoin('cursos as cd','cd.id_cursos','=','p.curso_destino_id')
                ->select(
                    'p.*',
                    'co.titulo as curso_origen_titulo',
                    'cd.titulo as curso_destino_titulo'
                )
                ->whereIn('p.id', $promIds)
                ->orderBy('p.curso_origen_id')
                ->get()->keyBy('id');

            foreach ($promIds as $pid) {
                $resumen = $resumenes->get($pid);
                if (!$resumen) continue;

                $detalle = DB::table('promociones_detalle as d')
                    ->leftJoin('users as u', 'u.id', '=', 'd.alumno_id')
                    ->select('d.alumno_id','u.nombre_completo','d.estado','d.nota_total','d.umbral','d.mensaje')
                    ->where('d.promocion_id', $pid)
                    ->orderByRaw("CASE d.estado WHEN 'movido' THEN 0 WHEN 'ya_en_destino' THEN 1 WHEN 'omitido' THEN 2 ELSE 3 END")
                    ->orderByDesc('d.nota_total')
                    ->orderBy('u.nombre_completo')
                    ->get();

                $bloques->push((object)[ 'resumen'=>$resumen, 'detalle'=>$detalle ]);

                $totales['aprob'] += (int)($resumen->total_aprobados ?? 0);
                $totales['mov']   += (int)($resumen->total_movidos ?? 0);
                $totales['omit']  += (int)($resumen->total_omitidos ?? 0);
            }
        }

        return view('promocion.promocion_total', [
            'postUrl' => route('admin.promocion.total.run'),
            'bloques' => $bloques,
            'totales' => $totales,
            'umbral'  => $request->old('umbral', 70),
        ]);
    }

    // POST /admin/promocion/total/run  (SP global)
// POST /admin/promocion/total/run  (SP global)
    public function promocionTotalRun(Request $request)
    {
        $request->validate([
            'umbral' => 'nullable|numeric|min:0|max:100',
            'accion' => 'required|in:simular,aplicar',
        ], [], [
            'umbral' => 'umbral',
            'accion' => 'acción',
        ]);

        $umbral = $request->filled('umbral') ? (float)$request->umbral : 70.0;
        $mover  = $request->accion === 'aplicar' ? 1 : 0;
        $userId = auth()->id();

        try {
            // ✅ Llamamos al wrapper que SÍ existe
            // Debe devolver filas con al menos: { prom_id, curso_id, modo, mover, total_aprobados, total_movidos, total_omitidos }
            $rows = DB::select('CALL sp_promocion_masivo(?, ?, ?)', [
                $umbral, $userId, $mover
            ]);

            // Tomamos los IDs de promociones creadas para pintar los bloques por curso
            $ids = collect($rows)->map(function($r){
                if (property_exists($r,'prom_id')) return (int)$r->prom_id;
                if (property_exists($r,'promocion_id')) return (int)$r->promocion_id;
                return null;
            })->filter()->values();

            if ($ids->isEmpty()) {
                // Respaldo: tomar las últimas promociones del usuario
                $ids = DB::table('promociones')
                    ->where('realizado_por', $userId)
                    ->orderByDesc('id')
                    ->limit(10)->pluck('id');
            }

            $msg = $mover ? 'Promoción total aplicada.' : 'Simulación total ejecutada.';
            return redirect()
                ->route('admin.promocion.total', ['prom_ids' => $ids->implode(',')])
                ->with('status', $msg);

        } catch (\Illuminate\Database\QueryException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Error inesperado: '.$e->getMessage());
        }
    }



    public function promocionDetalle(Request $request)
    {
        $request->validate([
            'prom_id' => 'required|integer|exists:promociones,id',
        ]);

        $promId = (int) $request->query('prom_id');

        $resumen = DB::table('promociones as p')
            ->leftJoin('cursos as co', 'co.id_cursos', '=', 'p.curso_origen_id')
            ->leftJoin('cursos as cd', 'cd.id_cursos', '=', 'p.curso_destino_id')
            ->select('p.*', 'co.titulo as curso_origen_titulo', 'cd.titulo as curso_destino_titulo')
            ->where('p.id', $promId)->first();

        // Verificación por estado:
        // - movido / ya_en_destino: existe alumno_curso(destino)
        // - omitido: sigue en origen (si origen ≠ NULL)
        // - graduacion (destino NULL y estado=movido): existe fila en graduados
        $detalle = DB::table('promociones_detalle as d')
            ->leftJoin('users as u', 'u.id', '=', 'd.alumno_id')
            ->leftJoin('promociones as p', 'p.id', '=', 'd.promocion_id')
            ->selectRaw("
                d.alumno_id,
                u.nombre_completo,
                d.estado,
                d.nota_total,
                d.umbral,
                d.mensaje,
                d.curso_origen_id,
                d.curso_destino_id,
                CASE
                  WHEN p.modo = 'total' AND d.estado IN ('movido','ya_en_destino')
                    THEN EXISTS (SELECT 1 FROM alumno_curso ac WHERE ac.user_id = d.alumno_id AND ac.id_cursos = d.curso_destino_id)
                  WHEN p.modo = 'total' AND d.estado = 'omitido'
                    THEN EXISTS (SELECT 1 FROM alumno_curso ac WHERE ac.user_id = d.alumno_id AND ac.id_cursos = d.curso_origen_id)
                  WHEN p.modo = 'graduacion' AND d.estado = 'movido'
                    THEN EXISTS (SELECT 1 FROM graduados g WHERE g.alumno_id = d.alumno_id AND g.curso_final_id = d.curso_origen_id)
                  ELSE 0
                END AS verificado
            ")
            ->where('d.promocion_id', $promId)
            ->orderByRaw("FIELD(d.estado, 'movido','ya_en_destino','omitido','error'), d.nota_total DESC, u.nombre_completo")
            ->get()
            ->map(function($r){
                $r->verif_info = $r->verificado ? 'OK' : 'Pendiente/No coincide';
                return $r;
            });

        return response()->json([
            'resumen' => $resumen,
            'detalle' => $detalle,
        ]);
    }


    /**
     * GET /promocion/total/historial?curso_id=...
     * Últimas N promociones de ese curso (para pintar un panel lateral).
     */
    public function historial(Request $request)
    {
        $request->validate([
            'curso_id' => 'required|integer|exists:cursos,id_cursos',
        ]);

        $cursoId = (int) $request->query('curso_id');

        $historial = DB::table('promociones as p')
            ->leftJoin('users as u', 'u.id', '=', 'p.realizado_por')
            ->select(
                'p.id as prom_id',
                'p.modo',
                'p.mover',
                'p.total_aprobados',
                'p.total_movidos',
                'p.total_omitidos',
                'p.created_at',
                'u.nombre_completo as realizado_por'
            )
            ->where('p.curso_origen_id', $cursoId)
            ->orderByDesc('p.id')
            ->limit(10)
            ->get();

        return response()->json($historial);
    }

/**
     * POST /promocion/total/masivo
     * Ejecuta sp_promocion_masivo: simular (mover=0) o aplicar (mover=1) para TODOS los cursos.
     */
    public function promocionMasivoRun(Request $request)
    {
        $request->validate([
            'umbral' => 'nullable|numeric|min:0|max:100',
            'accion' => 'required|in:simular,aplicar',
        ], [], [
            'umbral' => 'umbral',
            'accion' => 'acción',
        ]);

        $umbral = $request->filled('umbral') ? (float)$request->input('umbral') : 70.0;
        $mover  = $request->input('accion') === 'aplicar' ? 1 : 0;
        $userId = auth()->id();

        try {
            // El wrapper devuelve un resultset con {curso_id, prom_id, modo, mover, totales}
            $rows = DB::select('CALL sp_promocion_masivo(?, ?, ?)', [$umbral, $userId, $mover]);

            if (!$rows) {
                return redirect()->route('admin.promocion.total')->with('warning', 'No había cursos con alumnos para procesar.');
            }

            // Enriquecer con títulos y cargar detalles por prom_id
            $cursoIds = collect($rows)->pluck('curso_id')->unique()->all();
            $cursos = DB::table('cursos')->whereIn('id_cursos', $cursoIds)->pluck('titulo','id_cursos');

            $batch = [];
            $promIds = [];
            foreach ($rows as $r) {
                $batch[] = [
                    'curso_id'        => $r->curso_id,
                    'curso_titulo'    => $cursos[$r->curso_id] ?? ('Curso #'.$r->curso_id),
                    'prom_id'         => $r->prom_id,
                    'modo'            => $r->modo,
                    'mover'           => (int)$r->mover,
                    'total_aprobados' => (int)$r->total_aprobados,
                    'total_movidos'   => (int)$r->total_movidos,
                    'total_omitidos'  => (int)$r->total_omitidos,
                ];
                $promIds[] = $r->prom_id;
            }

            // Cargar detalle por cada prom_id
            $detalle = DB::table('promociones_detalle as d')
                ->leftJoin('users as u', 'u.id', '=', 'd.alumno_id')
                ->select('d.promocion_id','d.alumno_id','u.nombre_completo','d.estado','d.nota_total','d.umbral','d.mensaje')
                ->whereIn('d.promocion_id', $promIds)
                ->orderByRaw("CASE d.estado WHEN 'movido' THEN 0 WHEN 'ya_en_destino' THEN 1 WHEN 'omitido' THEN 2 ELSE 3 END")
                ->orderByDesc('d.nota_total')
                ->orderBy('u.nombre_completo')
                ->get()
                ->groupBy('promocion_id')
                ->toArray();

            // Volcar a sesión para que la vista los muestre
            return redirect()
                ->route('admin.promocion.total')
                ->with('status', $mover ? 'Promoción masiva aplicada correctamente.' : 'Simulación masiva ejecutada correctamente.')
                ->with('batch', $batch)
                ->with('batchDetalle', $detalle);

        } catch (\Illuminate\Database\QueryException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Error inesperado: ' . $e->getMessage());
        }
    }
 /**
     * GET /admin/promocion/historial-fecha?desde=YYYY-MM-DD&hasta=YYYY-MM-DD&curso_id=
     * Devuelve JSON resumido para la tabla lateral de historial.
     */
    public function historialPorFecha(Request $request)
    {
        $request->validate([
            'desde'    => 'nullable|date',
            'hasta'    => 'nullable|date',
            'curso_id' => 'nullable|integer|exists:cursos,id_cursos',
        ]);

        $desde = $request->filled('desde') ? Carbon::parse($request->query('desde'))->startOfDay() : Carbon::now()->subMonths(3)->startOfDay();
        $hasta = $request->filled('hasta') ? Carbon::parse($request->query('hasta'))->endOfDay()   : Carbon::now()->endOfDay();
        $cursoId = $request->query('curso_id');

        $q = DB::table('promociones as p')
            ->leftJoin('cursos as co', 'co.id_cursos', '=', 'p.curso_origen_id')
            ->leftJoin('cursos as cd', 'cd.id_cursos', '=', 'p.curso_destino_id')
            ->select(
                'p.id as prom_id','p.created_at',
                'p.modo','p.mover',
                'p.total_aprobados','p.total_movidos','p.total_omitidos',
                'co.titulo as origen_titulo','cd.titulo as destino_titulo',
                'p.curso_origen_id','p.curso_destino_id'
            )
            ->whereBetween('p.created_at', [$desde, $hasta])
            ->orderByDesc('p.id');

        if ($cursoId) {
            $q->where('p.curso_origen_id', (int)$cursoId);
        }

        $rows = $q->limit(200)->get()->map(function($r){
            return [
                'prom_id'         => (int)$r->prom_id,
                'fecha'           => Carbon::parse($r->created_at)->format('Y-m-d H:i'),
                'modo'            => $r->modo,
                'mover'           => (int)$r->mover,
                'total_aprobados' => (int)$r->total_aprobados,
                'total_movidos'   => (int)$r->total_movidos,
                'total_omitidos'  => (int)$r->total_omitidos,
                'origen'          => $r->origen_titulo ?: ('ID '.$r->curso_origen_id),
                'destino'         => $r->destino_titulo ?: null,
            ];
        });

        return response()->json($rows);
    }

    /**
     * GET /admin/promocion/graduados
     * Vista HTML con tabla de graduados por rango de fechas/curso origen.
     */
    public function graduadosIndex(Request $request)
{
    $request->validate([
        'prom_id' => 'nullable|integer|exists:promociones,id',
    ]);

    $prom_id = $request->query('prom_id');

    // SOLO promociones APLICADAS (mover=1) y de GRADUACIÓN
    $promos = DB::table('promociones as p')
        ->leftJoin('cursos as co','co.id_cursos','=','p.curso_origen_id')
        ->select(
            'p.id',
            'p.created_at',
            'p.total_movidos',
            DB::raw('co.titulo as curso')
        )
        ->where('p.modo', 'graduacion')
        ->where('p.mover', 1) // 👈 solo aplicadas
        ->orderByDesc('p.id')
        ->limit(300)
        ->get();

    // Detectar si existe la vista vw_notas_curso
    $hasView = DB::table('information_schema.VIEWS')
        ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
        ->where('TABLE_NAME', 'vw_notas_curso')
        ->exists();

    // Subconsulta: promedio total por alumno
    if ($hasView) {
        $promSub = DB::table('vw_notas_curso as v')
            ->selectRaw('v.alumno_id, AVG(COALESCE(v.nota_total,0)) AS promedio_total')
            ->groupBy('v.alumno_id');
    } else {
        $promSub = DB::query()->fromSub(function($q){
            $q->from('calificaciones as c')
              ->selectRaw('c.alumno_id, c.id_cursos, COALESCE(SUM(c.nota),0) as nota_total')
              ->groupBy('c.alumno_id','c.id_cursos');
        }, 'nc')->selectRaw('alumno_id, AVG(nota_total) AS promedio_total')
          ->groupBy('alumno_id');
    }

    if ($prom_id) {
        // ✅ SOLO graduados reales: INNER JOIN a `graduados` + p.mover=1
        $q = DB::table('promociones_detalle as d')
            ->join('promociones as p','p.id','=','d.promocion_id')
            ->join('users as u','u.id','=','d.alumno_id')
            ->leftJoin('cursos as co','co.id_cursos','=','d.curso_origen_id')
            ->join('graduados as g', function($j){
                $j->on('g.alumno_id','=','d.alumno_id')
                  ->on('g.curso_final_id','=','d.curso_origen_id');
            })
            ->leftJoinSub($promSub, 'prom', function($j){
                $j->on('prom.alumno_id','=','d.alumno_id');
            })
            ->where('d.promocion_id', $prom_id)
            ->where('p.modo','graduacion')
            ->where('p.mover', 1)          // 👈 solo aplicadas
            ->where('d.estado','movido')
            ->select([
                'd.alumno_id',
                'u.nombre_completo',
                DB::raw('d.curso_origen_id as curso_origen_id'),
                DB::raw('co.titulo as curso_origen_titulo'),
                DB::raw('COALESCE(prom.promedio_total, 0) as promedio_total'),
                DB::raw('g.fecha_graduacion as fecha_graduacion'), // 👈 sin fallback a p.created_at
                DB::raw('YEAR(g.fecha_graduacion) as anio'),
            ])
            ->orderByDesc('g.fecha_graduacion');

        $graduados = $q->paginate(20)->appends($request->query());
    } else {
        $graduados = new \Illuminate\Pagination\LengthAwarePaginator(
            [], 0, 20, \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );
    }

    return view('promocion.graduados', [
        'promos'    => $promos,
        'prom_id'   => $prom_id,
        'graduados' => $graduados,
    ]);
}



/**
     * GET /admin/promocion/graduados/detalle?alumno_id=...
     * Devuelve JSON: cursos (id, titulo, nota_total) y promedio_total.
     */
    public function graduadoDetalleAlumno(Request $request)
    {
        $request->validate([
            'alumno_id' => 'required|integer|exists:users,id',
        ]);

        $alumnoId = (int)$request->query('alumno_id');

        $hasView = DB::table('information_schema.VIEWS')
            ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
            ->where('TABLE_NAME', 'vw_notas_curso')
            ->exists();

        if ($hasView) {
            $rows = DB::table('vw_notas_curso as v')
                ->leftJoin('cursos as c','c.id_cursos','=','v.id_cursos')
                ->where('v.alumno_id', $alumnoId)
                ->select('v.id_cursos','c.titulo as curso_titulo', DB::raw('COALESCE(v.nota_total,0) as nota_total'))
                ->orderBy('c.id_cursos')
                ->get();
        } else {
            // ⚠️ Fallback NUEVO: listar cursos/nota desde calificaciones (no desde alumno_curso)
            $rows = DB::table('calificaciones as cal')
                ->leftJoin('cursos as c','c.id_cursos','=','cal.id_cursos')
                ->where('cal.alumno_id', $alumnoId)
                ->groupBy('cal.alumno_id','cal.id_cursos','c.titulo')
                ->select('cal.id_cursos','c.titulo as curso_titulo', DB::raw('COALESCE(SUM(cal.nota),0) as nota_total'))
                ->orderBy('cal.id_cursos')
                ->get();
        }

        $promedio = (float) collect($rows)->avg(fn($r)=> (float)$r->nota_total);

        return response()->json([
            'alumno_id'     => $alumnoId,
            'cursos'        => $rows,
            'promedio_total'=> $promedio,
        ]);
    }


// GET /admin/promocion/corridas?days=60&limit=300
public function corridasRecientes(Request $request)
{
    $days  = (int) $request->query('days', 60);
    $limit = (int) $request->query('limit', 300);

    $desde = \Illuminate\Support\Carbon::now()->subDays($days)->startOfDay();

    $rows = \DB::table('promociones as p')
        ->leftJoin('cursos as co','co.id_cursos','=','p.curso_origen_id')
        ->leftJoin('cursos as cd','cd.id_cursos','=','p.curso_destino_id')
        ->select(
            'p.id as prom_id','p.created_at','p.modo','p.mover',
            'p.total_aprobados','p.total_movidos','p.total_omitidos',
            'p.realizado_por',
            'co.titulo as origen_titulo','cd.titulo as destino_titulo',
            'p.curso_origen_id','p.curso_destino_id'
        )
        ->where('p.created_at','>=',$desde)
        ->orderByDesc('p.id')
        ->limit($limit)
        ->get();

    // Agrupamos por "corrida": mismo usuario, mismo mover (0/1) y misma marca de minuto
    $groups = [];
    foreach ($rows as $r) {
        $fechaMin = \Illuminate\Support\Carbon::parse($r->created_at)->format('Y-m-d H:i'); // minuto
        $key = $r->mover.'|'.$fechaMin.'|'.($r->realizado_por ?? 0);

        if (!isset($groups[$key])) {
            $groups[$key] = [
                'key'     => $key,
                'mover'   => (int)$r->mover,
                'fecha'   => $fechaMin,
                'usuario' => (int)($r->realizado_por ?? 0),
                'cursos'  => 0,
                'totales' => ['mov'=>0,'aprob'=>0,'omit'=>0],
                'items'   => [],
            ];
        }

        $groups[$key]['cursos'] += 1;
        $groups[$key]['totales']['mov']   += (int)$r->total_movidos;
        $groups[$key]['totales']['aprob'] += (int)$r->total_aprobados;
        $groups[$key]['totales']['omit']  += (int)$r->total_omitidos;

        $groups[$key]['items'][] = [
            'prom_id'         => (int)$r->prom_id,
            'modo'            => $r->modo,
            'mover'           => (int)$r->mover,
            'total_movidos'   => (int)$r->total_movidos,
            'total_aprobados' => (int)$r->total_aprobados,
            'total_omitidos'  => (int)$r->total_omitidos,
            'origen'          => $r->origen_titulo ?: ('ID '.$r->curso_origen_id),
            'destino'         => $r->curso_destino_id ? ($r->destino_titulo ?: ('ID '.$r->curso_destino_id)) : null,
        ];
    }

    // Ordena más reciente primero
    $out = array_values($groups);
    usort($out, fn($a,$b) => strcmp($b['fecha'],$a['fecha']));

    return response()->json($out);
}


}
