<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PromocionController extends Controller
{
    public function __construct()
    {
        // Mantén solo admin (tus rutas ya usan role:admin)
        $this->middleware(['auth', 'role:admin']);
        // Si quisieras permitir maestros: $this->middleware(['auth']);
    }

    public function index()
    {
        $cursos = \App\Models\Curso::orderBy('titulo')->get(['id_cursos','titulo']);
        return view('promocion.selector', compact('cursos'));
    }

    /** Form de promoción manual entre cursos (sin graduar). */
    public function show(Curso $curso, Request $request)
    {
        $user = Auth::user();
        $isAdmin = method_exists($user, 'hasRole')
            ? $user->hasRole('admin')
            : (($user->role ?? null) === 'admin');

        if (!$isAdmin) {
            $esDelMaestro = $curso->maestro()->where('user_id', auth()->id())->exists();
            if (!$esDelMaestro) abort(403);
        }

        // Alumnos (1 fila por alumno)
        $alumnos = $curso->alumnos()
            ->select('users.id', 'users.nombre_completo')
            ->distinct()
            ->orderBy('users.nombre_completo')
            ->get();

        // Suma de notas para UI
        $raw = DB::table('calificaciones')
            ->where('id_cursos', $curso->id_cursos)
            ->select('alumno_id', DB::raw('SUM(COALESCE(nota,0)) as total'))
            ->groupBy('alumno_id')
            ->pluck('total','alumno_id');

        $umbral = 70;

        $rows = $alumnos->map(function ($a) use ($raw, $umbral) {
            $total = (float) ($raw[$a->id] ?? 0);
            return [
                'id'     => $a->id,
                'nombre' => $a->nombre_completo,
                'total'  => $total,
                'ok'     => $total >= $umbral,
            ];
        })->values()->all();

        $cursosDestino = Curso::orderBy('titulo')->get(['id_cursos','titulo']);
        $postUrl = route('admin.cursos.promocion.run', $curso->id_cursos);

        return view('promocion.index', [
            'curso'         => $curso,
            'rows'          => $rows,
            'cursosDestino' => $cursosDestino,
            'postUrl'       => $postUrl,
            'umbral'        => $umbral,
        ]);
    }

    /** Ejecutar promoción manual entre cursos (NO gradúa). */
    public function promote(Curso $curso, Request $request)
    {
        $data = $request->validate([
            'curso_destino_id' => ['required','integer','exists:cursos,id_cursos'],
            'alumnos'          => ['nullable','array'],
            'alumnos.*'        => ['integer'],
            'mover'            => ['nullable','boolean'],
        ], [], [
            'curso_destino_id' => 'curso destino',
            'alumnos'          => 'alumnos seleccionados',
        ]);

        $destino   = (int) $data['curso_destino_id'];
        $seleccion = collect($data['alumnos'] ?? [])->filter()->unique()->map(fn($v)=>(int)$v)->values();
        $mover     = $request->boolean('mover');

        if ($destino === (int) $curso->id_cursos) {
            return back()->with('error', 'El curso destino no puede ser el mismo que el curso origen.');
        }
        if ($seleccion->isEmpty()) {
            return back()->with('warning', 'No seleccionaste alumnos.');
        }

        // Validar que todos son del curso origen
        $alumnosOrigen = $curso->alumnos()->select('users.id')->distinct()->pluck('users.id')->map(fn($v)=>(int)$v)->all();
        $setOrigen = array_flip($alumnosOrigen);

        // Timestamps opcionales
        $acHasCreated  = Schema::hasColumn('alumno_curso', 'created_at');
        $acHasUpdated  = Schema::hasColumn('alumno_curso', 'updated_at');
        $movHasCreated = Schema::hasColumn('movimientos_alumno', 'created_at');
        $movHasUpdated = Schema::hasColumn('movimientos_alumno', 'updated_at');

        $inscritosDestino = 0;
        $yaEstaban        = 0;
        $movidos          = 0;
        $saltados         = 0;

        DB::beginTransaction();
        try {
            foreach ($seleccion as $alumnoId) {
                if (!isset($setOrigen[$alumnoId])) { $saltados++; continue; }

                // 1) Inscribir en destino si no está
                $insertData = [
                    'user_id'   => $alumnoId,
                    'id_cursos' => $destino,
                ];
                if ($acHasCreated) $insertData['created_at'] = now();
                if ($acHasUpdated) $insertData['updated_at'] = now();

                $ins = DB::table('alumno_curso')->insertOrIgnore($insertData);
                if ($ins > 0) { $inscritosDestino++; } else { $yaEstaban++; }

                // 2) Si es traslado real, quitar del origen
                if ($mover) {
                    $del = DB::table('alumno_curso')
                        ->where('user_id', $alumnoId)
                        ->where('id_cursos', $curso->id_cursos)
                        ->delete();
                    if ($del) $movidos++;
                }

                // 3) Registrar historial — usar 'promocion' (compatibilidad ENUM/longitud)
                $movData = [
                    'alumno_id'        => $alumnoId,
                    'tipo'             => 'promocion', // <<— antes era 'promocion_manual' y truncaba
                    'curso_origen_id'  => $curso->id_cursos,
                    'curso_destino_id' => $destino,
                    'promocion_id'     => null,
                    'observaciones'    => $mover
                        ? 'Promoción manual: traslado (quitado de origen)'
                        : 'Promoción manual: copia (queda en ambos)',
                ];
                if ($movHasCreated) $movData['created_at'] = now();
                if ($movHasUpdated) $movData['updated_at'] = now();

                DB::table('movimientos_alumno')->insert($movData);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Error al promover: '.$e->getMessage());
        }

        $msg = "Promoción realizada. "
             . "Inscritos en destino: {$inscritosDestino}. "
             . "Ya estaban en destino: {$yaEstaban}. "
             . ($mover ? "Quitados del origen: {$movidos}. " : "")
             . "Omitidos: {$saltados}.";

        return back()->with('status', $msg);
    }
}
