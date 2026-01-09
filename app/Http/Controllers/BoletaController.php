<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Curso;
use App\Models\User;

class BoletaController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /** Boleta por alumno: lista todos los cursos y muestra nota (manual o calculada) o estado Por cursar/No cursado */
    public function show(int $alumnoId)
    {
        // 1) Alumno
        $alumno = DB::table('users')->where('id', $alumnoId)->first();
        if (!$alumno) abort(404);

        // 2) Todos los cursos
        $cursos = Curso::orderBy('titulo')->get(['id_cursos','titulo']);

        // 3) Set de cursos en los que está/estuvo inscrito el alumno (para decidir "Por cursar" vs "No cursado")
        $inscripciones = DB::table('alumno_curso')
            ->where('user_id', $alumnoId)
            ->pluck('id_cursos')
            ->map(fn($v)=>(int)$v)
            ->all();
        $inscSet = array_flip($inscripciones); // acceso O(1)

        // 4) Notas calculadas por curso (preferimos vista vw_notas_curso si existe; si no, sumamos calificaciones)
        $hasView = (bool) (DB::selectOne("
            SELECT EXISTS(
                SELECT * FROM information_schema.VIEWS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'vw_notas_curso'
            ) AS e
        ")->e ?? 0);

        if ($hasView) {
            $calc = DB::table('vw_notas_curso')
                ->where('alumno_id', $alumnoId)
                ->select('id_cursos', 'nota_total')
                ->pluck('nota_total', 'id_cursos'); // [curso_id => nota]
        } else {
            $calc = DB::table('calificaciones')
                ->where('alumno_id', $alumnoId)
                ->select('id_cursos', DB::raw('SUM(COALESCE(nota,0)) as nota_total'))
                ->groupBy('id_cursos')
                ->pluck('nota_total', 'id_cursos');
        }

        // 5) Última boleta curada e items (si existen) => tiene prioridad sobre calculada
        $boleta = DB::table('boletas')
            ->where('alumno_id', $alumnoId)
            ->orderByDesc('id')
            ->first();

        $curadas = [];
        if ($boleta) {
            $items = DB::table('boleta_items')
                ->where('boleta_id', $boleta->id)
                ->get();
            foreach ($items as $it) {
                // clave por curso_id; campos disponibles: nota, etiqueta (ajusta si tu migra usa otros nombres)
                $curadas[(int)$it->curso_id] = [
                    'nota'    => is_null($it->nota) ? null : (float)$it->nota,
                    'etiqueta'=> $it->etiqueta ?? null,
                ];
            }
        }

        // 6) Unificamos: por cada curso => nota (curada > calculada) o null; estado y fuente
        $filas = [];
        $sum = 0.0; $cnt = 0;

        foreach ($cursos as $c) {
            $cursoId = (int)$c->id_cursos;

            $nota = null;
            $fuente = null;
            $etiqueta = null;

            if (isset($curadas[$cursoId]) && $curadas[$cursoId]['nota'] !== null) {
                $nota     = $curadas[$cursoId]['nota'];
                $etiqueta = $curadas[$cursoId]['etiqueta'];
                $fuente   = 'manual';
            } elseif (isset($calc[$cursoId])) {
                $nota   = (float) $calc[$cursoId];
                $fuente = 'calculada';
            }

            // Estado visual por nota / inscripción
            if ($nota === null) {
                $estadoLbl = isset($inscSet[$cursoId]) ? 'Por cursar' : 'No cursado';
                $estadoCls = 'bg-slate-100 text-slate-700';
            } else {
                if ($nota >= 91)      { $estadoLbl = 'Excelencia'; $estadoCls = 'bg-emerald-100 text-emerald-700'; }
                elseif ($nota >= 81)  { $estadoLbl = 'Perfecto';   $estadoCls = 'bg-blue-100 text-blue-700'; }
                elseif ($nota >= 70)  { $estadoLbl = 'Muy bueno';  $estadoCls = 'bg-indigo-100 text-indigo-700'; }
                else                  { $estadoLbl = 'Reprobado';  $estadoCls = 'bg-rose-100 text-rose-700'; }

                // promedio SOLO con cursos que tienen nota
                $sum += $nota; $cnt++;
            }

            $filas[] = [
                'curso_id'     => $cursoId,
                'curso_titulo' => $c->titulo,
                'nota'         => $nota,       // null si no hay
                'fuente'       => $fuente,     // 'manual' | 'calculada' | null
                'etiqueta'     => $etiqueta,   // etiqueta libre del item curado
                'estado_lbl'   => $estadoLbl,
                'estado_cls'   => $estadoCls,
                'inscrito'     => isset($inscSet[$cursoId]),
            ];
        }

        $promedio = $cnt > 0 ? round($sum / $cnt, 2) : null;

        // 7) Clasificación por promedio (si existe)
        $clasLbl = '—'; $clasCls = 'bg-gray-100 text-gray-700';
        if ($promedio !== null) {
            if ($promedio >= 91)      { $clasLbl = 'Excelencia'; $clasCls = 'bg-emerald-100 text-emerald-700'; }
            elseif ($promedio >= 81)  { $clasLbl = 'Perfecto';   $clasCls = 'bg-blue-100 text-blue-700'; }
            elseif ($promedio >= 70)  { $clasLbl = 'Muy bueno';  $clasCls = 'bg-indigo-100 text-indigo-700'; }
            else                      { $clasLbl = 'Reprobado';  $clasCls = 'bg-rose-100 text-rose-700'; }
        }

        return view('admin.resultados.boleta', [
            'alumno'   => $alumno,
            'boleta'   => $boleta,     // puede ser null (si aún no hay curada)
            'filas'    => $filas,      // cursos + nota/estado/fuente
            'promedio' => $promedio,   // solo cursos con nota
            'clasLbl'  => $clasLbl,
            'clasCls'  => $clasCls,
        ]);
    }

    /** Guardar/actualizar boleta general del alumno. */
    public function save(int $alumnoId, Request $request)
    {
        $request->validate([
            'fecha_emision'            => ['nullable','date'],
            'observaciones'            => ['nullable','string'],
            'items'                    => ['nullable','array'],
            'items.*.curso_id'         => ['nullable','integer'],
            'items.*.curso_titulo'     => ['nullable','string','max:255'],
            'items.*.nota_nombre'      => ['nullable','string','max:120'],
            'items.*.nota_valor'       => ['nullable','string'], // lo normalizamos abajo
        ],[],[
            'fecha_emision' => 'fecha de emisión',
        ]);

        // Alumno existe
        $alumno = DB::table('users')->select('id')->where('id', $alumnoId)->first();
        abort_unless($alumno, 404, 'Alumno no encontrado');

        // Normalizar líneas: dejamos solo las que tengan curso_titulo o nota_valor
        $rawItems = collect($request->input('items', []))->values()->map(function($it, $idx){
            $titulo = trim((string)($it['curso_titulo'] ?? ''));
            $nota   = trim((string)($it['nota_valor'] ?? ''));
            // reemplazar coma por punto
            $nota   = $nota === '' ? null : str_replace(',', '.', $nota);
            $notaF  = is_null($nota) ? null : (is_numeric($nota) ? round((float)$nota,2) : null);

            return [
                'curso_id'     => $it['curso_id'] ? (int)$it['curso_id'] : null,
                'curso_titulo' => $titulo,
                'nota_nombre'  => trim((string)($it['nota_nombre'] ?? '')) ?: null,
                'nota_valor'   => $notaF, // null = no cuenta
                'orden'        => $idx,
            ];
        })->filter(function($it){
            // línea útil si hay título o hay alguna nota/etiqueta
            return ($it['curso_titulo'] !== '') || !is_null($it['nota_valor']) || !empty($it['nota_nombre']);
        })->values();

        DB::beginTransaction();
        try {
            // upsert boleta (1 por alumno)
            $boleta = DB::table('boletas')->where('alumno_id', $alumnoId)->first();
            $fecha  = $request->input('fecha_emision') ?: now()->toDateString();
            $obs    = $request->input('observaciones');

            if ($boleta) {
                DB::table('boletas')->where('id', $boleta->id)->update([
                    'fecha_emision' => $fecha,
                    'observaciones' => $obs,
                    'updated_at'    => now(),
                ]);
                $boletaId = $boleta->id;

                // reemplazamos ítems
                DB::table('boleta_items')->where('boleta_id', $boletaId)->delete();
            } else {
                $boletaId = DB::table('boletas')->insertGetId([
                    'alumno_id'     => $alumnoId,
                    'fecha_emision' => $fecha,
                    'observaciones' => $obs,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            // insertar items
            foreach ($rawItems as $it) {
                DB::table('boleta_items')->insert([
                    'boleta_id'             => $boletaId,
                    'curso_id'              => $it['curso_id'],
                    'curso_titulo_snapshot' => $it['curso_titulo'] ?: '(sin título)',
                    'nota_nombre'           => $it['nota_nombre'],
                    'nota_valor'            => $it['nota_valor'],
                    'orden'                 => $it['orden'],
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'No se pudo guardar la boleta: '.$e->getMessage())->withInput();
        }

        return back()->with('status', 'Boleta guardada correctamente.');
    }

    /** Promedio excluyendo notas null. */
    private function calcPromedio($items): float
    {
        $vals = collect($items)->pluck('nota_valor')->filter(fn($v)=> !is_null($v))->map(fn($v)=>(float)$v);
        if ($vals->count() === 0) return 0.0;
        return round($vals->avg(), 2);
    }

    /** Clasificación y clase CSS. */
    private function clasificacion(float $prom): array
    {
        if ($prom >= 91) return ['Excelencia', 'bg-emerald-100 text-emerald-700'];
        if ($prom >= 81) return ['Perfecto',   'bg-blue-100 text-blue-700'];
        if ($prom >= 70) return ['Muy bueno',  'bg-indigo-100 text-indigo-700'];
        return ['Reprobado', 'bg-rose-100 text-rose-700'];
    }

    /** Selector de alumno para boleta. Si viene ?alumno_id=, redirige a la boleta. */
 public function index(Request $request)
    {
        $q        = trim((string) $request->input('q', ''));
        $dni      = trim((string) $request->input('dni', ''));
        $cursoId  = $request->filled('curso_id') ? (int) $request->input('curso_id') : null;

        // Para el combo de cursos
        $cursos = Curso::orderBy('titulo')->get(['id_cursos','titulo']);

        // Solo buscamos si el usuario mandó algún filtro
        $results = null;

        if ($q !== '' || $dni !== '' || $cursoId) {
            $query = DB::table('users as u')
                ->leftJoin('alumno_curso as ac', 'ac.user_id', '=', 'u.id')
                ->leftJoin('cursos as c', 'c.id_cursos', '=', 'ac.id_cursos')
                ->select(
                    'u.id',
                    'u.nombre_completo',
                    'u.email',
                    // IMPORTANTE: si tu columna se llama 'cedula' o 'documento', cámbiala aquí.
                    // Este bloque intenta no romper si 'dni' no existe:
                    DB::raw(Schema::hasColumn('users', 'dni') ? 'u.dni as dni' : 'NULL as dni'),
                    DB::raw('GROUP_CONCAT(DISTINCT c.titulo ORDER BY c.titulo SEPARATOR ", ") as cursos')
                )
                ->groupBy('u.id', 'u.nombre_completo', 'u.email')
                ->orderBy('u.nombre_completo');

            if ($q !== '') {
                // Si es numérico, asumimos que busca por ID de usuario
                if (ctype_digit($q)) {
                    $query->where('u.id', (int) $q);
                } else {
                    $query->where('u.nombre_completo', 'like', '%'.$q.'%');
                }
            }

            if ($dni !== '' && Schema::hasColumn('users', 'dni')) {
                $query->where('u.dni', 'like', '%'.$dni.'%');
            }

            if ($cursoId) {
                $query->where('ac.id_cursos', $cursoId);
            }

            $results = $query->paginate(25)->withQueryString();
        }

        return view('admin.resultados.boleta_index', [
            'cursos'   => $cursos,
            'results'  => $results,  // null hasta que envías filtros
            'q'        => $q,
            'dni'      => $dni,
            'cursoId'  => $cursoId,
        ]);
    }

}
