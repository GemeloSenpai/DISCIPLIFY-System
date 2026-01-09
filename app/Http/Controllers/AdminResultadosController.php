<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminResultadosController extends Controller
{
    /**
     * Vista Entrega de Notas (por curso):
     * - Agrupa alumnos por curso
     * - Suma tareas + evaluaciones + examen
     * - Marca aprobado/reprobado con umbral (default 70)
     * Filtros opcionales por curso (?curso_id=) y umbral (?umbral=).
     */
    public function entregaNotas(Request $request)
    {
        $colCursoNombre = Schema::hasColumn('cursos','nombre') ? 'nombre' : 'nombre_curso';
        $umbral  = (float) ($request->query('umbral', 70));
        $cursoId = $request->query('curso_id'); // opcional

        // Para combo de cursos
        $cursos = DB::table('cursos')
        ->select('id_cursos', 'titulo', DB::raw('titulo AS nombre')) // alias "nombre" = titulo
        ->orderBy('titulo')
        ->get();


                // Base: alumnos inscritos en cursos (alumno_curso)
                $q = DB::table('alumno_curso as ac')
                    ->join('users as u', 'u.id', '=', 'ac.user_id')
                    ->join('cursos as cu', 'cu.id_cursos', '=', 'ac.id_cursos')
                    ->leftJoin('calificaciones as c', function ($join) {
                        $join->on('c.alumno_id', '=', 'ac.user_id')
                            ->on('c.id_cursos', '=', 'ac.id_cursos');
                    })
                    ->when($cursoId, fn($qq) => $qq->where('ac.id_cursos', $cursoId))
                    ->groupBy('ac.id_cursos', 'cu.titulo', 'u.id', 'u.nombre_completo')
                    ->orderBy('cu.titulo')->orderBy('u.nombre_completo')
                    ->selectRaw("
                        ac.id_cursos,
                        cu.titulo AS curso_nombre,   -- usamos titulo para ambos alias
                        cu.titulo AS curso_titulo,
                        u.id      AS alumno_id,
                        u.nombre_completo,
                        SUM(CASE WHEN c.tipo = 'tarea'      AND c.nota IS NOT NULL THEN c.nota ELSE 0 END) AS sum_tareas,
                        SUM(CASE WHEN c.tipo = 'evaluacion' AND c.nota IS NOT NULL THEN c.nota ELSE 0 END) AS sum_evals,
                        SUM(CASE WHEN c.tipo = 'examen'     AND c.nota IS NOT NULL THEN c.nota ELSE 0 END) AS sum_examen
                    ");
                    
        $rows = $q->get();

        // Armar estructura agrupada por curso
        $byCurso = [];
        foreach ($rows as $r) {
            $total = (float)$r->sum_tareas + (float)$r->sum_evals + (float)$r->sum_examen;
            $aprobado = $total >= $umbral;

            if (!isset($byCurso[$r->id_cursos])) {
                $byCurso[$r->id_cursos] = [
                    'curso'   => [
                        'id'     => $r->id_cursos,
                        'nombre' => $r->curso_nombre,
                        'titulo' => $r->curso_titulo,
                    ],
                    'alumnos' => [],
                ];
            }

            $byCurso[$r->id_cursos]['alumnos'][] = [
                'alumno_id'      => $r->alumno_id,
                'nombre'         => $r->nombre_completo,
                'sum_tareas'     => (float)$r->sum_tareas,
                'sum_evals'      => (float)$r->sum_evals,
                'sum_examen'     => (float)$r->sum_examen,
                'total'          => $total,
                'aprobado'       => $aprobado,
            ];
        }

        // Estadísticas por curso (aprobados/reprobados/matriculados)
        $stats = [];
        foreach ($byCurso as $cid => $pack) {
            $ap = 0; $rp = 0;
            foreach ($pack['alumnos'] as $a) {
                $a['aprobado'] ? $ap++ : $rp++;
            }
            $stats[$cid] = [
                'matriculados' => count($pack['alumnos']),
                'aprobados'    => $ap,
                'reprobados'   => $rp,
            ];
        }

        // Para la tarjetita del dashboard (si quisieras usar aquí estos números)
        $alumnosMatriculados = DB::table('alumno_curso')->distinct('user_id')->count('user_id');

        return view('admin.resultados.entrega_notas', [
            'umbral'               => $umbral,
            'cursoId'              => $cursoId,
            'cursos'               => $cursos,
            'byCurso'              => $byCurso,
            'stats'                => $stats,
            'alumnosMatriculados'  => $alumnosMatriculados,
        ]);
    }
}