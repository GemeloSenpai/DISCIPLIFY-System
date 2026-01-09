<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\User;
use App\Models\Entrega;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;



class AdminController extends Controller
{
    public function cursos()
    {
        $cursos = Curso::withCount('alumnos')->get();

        return view('admin.mostrar-cursos', compact('cursos'));
    }

    public function dashboard()
    {
        $cursos = Curso::with(['maestro', 'alumnos'])
                    ->withCount(['alumnos', 'tareas'])
                    ->orderByDesc('created_at')
                    ->take(7)
                    ->get();

        return view('admin.dashboard', compact('cursos'));
    }

    public function showCurso(Curso $curso)
    {
        $curso->loadCount('tareas');

        $maestros = $curso->maestro;

        $alumnos = $curso->alumnos()
            ->with(['entregas']) // para saber por tarea_id
            ->withCount(['entregas' => function ($query) {
                $query->where('entregada', true);
            }])
            ->get();

        $tareas = $curso->tareas()->orderBy('id_tareas')->get(); // ← importante

        return view('admin.mostrar-cursos', compact('curso', 'maestros', 'alumnos', 'tareas'));
    }


    public function actualizarEntrega(Request $request)
    {
        $request->validate([
            'alumno_id' => 'required|exists:users,id',
            'tarea_id' => 'required|exists:tareas,id_tareas',
            'entregada' => 'required|boolean',
        ]);

        $entrega = \App\Models\Entrega::firstOrCreate(
            [
                'alumno_id' => $request->alumno_id,
                'tarea_id' => $request->tarea_id,
            ],
            ['fecha_entrega' => now()]
        );

        $entrega->entregada = $request->entregada;
        $entrega->fecha_entrega = now();
        $entrega->save();

        return response()->json(['success' => true]);
    }

    public function gestionarCursos()
    {
        $cursos = Curso::all(); // o con relaciones si ya las usas
        $maestros = User::where('role', 'maestro')->get();

        // user_id => id_cursos (pivot actual de cada maestro si alguno)
        $ocupados = DB::table('curso_maestro')->pluck('id_cursos', 'user_id'); 

        return view('admin.cursos.gestionar-cursos', compact('cursos','maestros','ocupados'));

    }

    // Funciones para la vista de curso
    

    public function create()
    {
        return redirect()->route('admin.cursos.gestionar')->with('success', 'Curso creado correctamente.');

    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
        ]);

        Curso::create([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
        ]);

        return redirect()->route('admin.cursos.gestionar')->with('success', 'Curso creado correctamente.');
    }

    public function edit($id)
    {
        $curso = Curso::findOrFail($id);
        return view('admin.cursos.edit', compact('curso'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'titulo' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $curso = Curso::findOrFail($id);
        $curso->update([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
        ]);

        return redirect()->route('admin.cursos.gestionar')->with('success', 'Curso actualizado correctamente.');
    }

    public function destroy($id)
    {
        $curso = Curso::findOrFail($id);
        $curso->delete();

        return redirect()->route('admin.cursos.gestionar')->with('success', 'Curso eliminado correctamente.');
    }


    // Asignar y quitar docentes
    public function indexCursos()
    {
        $cursos = Curso::all();
        $maestros = User::where('role', 'maestro')->get();

        return view('admin.gestionar-cursos', compact('cursos', 'maestros'));
    }

    public function asignarMaestros(Request $request, \App\Models\Curso $curso)
{
    $request->validate([
        'maestros'   => ['array'],
        'maestros.*' => ['exists:users,id'],
    ]);

    $ids = $request->input('maestros', []); // array (puede venir vacío)
    $curso->maestros()->sync($ids);

    $nombres = \App\Models\User::whereIn('id', $ids)->pluck('nombre_completo')->filter()->all();
    $lista   = $nombres ? implode(', ', $nombres) : '—sin docentes—';

    return back()->with('success', "Mentores {$lista} asignados al curso correctamente.");
}


    public function quitarMaestro(Request $request, Curso $curso)
    {
        $request->validate([
            'maestro_id' => 'required|exists:users,id',
        ]);

        $curso->maestros()->detach($request->maestro_id);

        return redirect()->back()->with('success', 'Maestro eliminado del curso.');
    }

   public function vistaUsuarios()
{
    return view('admin.crud-users');
}

// CRUD PARA USERS
public function buscarUsuarios(Request $request)
{
    $q = trim((string) $request->query('query', ''));

    $usuarios = User::query()
        // Curso por alumno
        ->leftJoin('alumno_curso as ac', 'ac.user_id', '=', 'users.id')
        ->leftJoin('cursos as ca', 'ca.id_cursos', '=', 'ac.id_cursos')

        // Curso por maestro (pivot curso_maestro)
        ->leftJoin('curso_maestro as cm', 'cm.user_id', '=', 'users.id')
        ->leftJoin('cursos as c2', 'c2.id_cursos', '=', 'cm.id_cursos')

        // (opcional) si además usas maestro_id directo en cursos:
        // ->leftJoin('cursos as c3', 'c3.maestro_id', '=', 'users.id')

        ->when($q !== '', function ($query) use ($q) {
            $query->where(function ($w) use ($q) {
                $w->where('users.nombre_completo', 'like', "%{$q}%")
                  ->orWhere('users.name', 'like', "%{$q}%")
                  ->orWhere('users.email', 'like', "%{$q}%")
                  ->orWhere('users.DNI', 'like', "%{$q}%")
                  ->orWhere('users.telefono', 'like', "%{$q}%");
            });
        })
        ->select([
            'users.id',
            'users.nombre_completo',
            'users.DNI',
            'users.fecha_nacimiento',
            'users.telefono',
            'users.role',
            'users.estado',
            DB::raw('COALESCE(ca.titulo, c2.titulo) as curso'), // alumno o maestro
        ])
        ->orderByDesc('users.id')
        ->limit(200)
        ->get();

    return response()->json($usuarios);
}

public function createUser()
{
    // Listado de cursos para el selector
    $cursos = Curso::select('id_cursos','titulo')->orderBy('titulo')->get();

    return view('admin.create-user', compact('cursos'));
}

public function storeUser(Request $request)
{
    $data = $request->validate([
        'nombre_completo'   => ['nullable','string','max:255'],
        'name'              => ['nullable','string','max:255'],
        'email'             => ['required','email','max:255','unique:users,email'],
        'password'          => ['required','string','min:6'],
        'role'              => ['required', Rule::in(['admin','maestro','alumno'])],
        'estado'            => ['required', Rule::in(['activo','inactivo'])],
        'DNI'               => ['nullable','string','max:50'],
        'telefono'          => ['nullable','string','max:50'],
        'fecha_nacimiento'  => ['nullable','date'],
        'direccion'         => ['nullable','string'],
        'curso_id'          => ['required_if:role,alumno,maestro','nullable','exists:cursos,id_cursos'],
    ], [
        'curso_id.required_if' => 'Debes seleccionar un curso para alumnos o maestros.',
    ]);

    return DB::transaction(function () use ($data) {
        $user = User::create([
            'nombre_completo'  => $data['nombre_completo'] ?? null,
            'name'             => $data['name'] ?? '',
            'email'            => $data['email'],
            'password'         => Hash::make($data['password']),
            'role'             => $data['role'],
            'estado'           => $data['estado'],
            'DNI'              => $data['DNI'] ?? null,
            'telefono'         => $data['telefono'] ?? null,
            'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            'direccion'        => $data['direccion'] ?? null,
        ]);

        $cursoNombre = null;

        if (in_array($data['role'], ['alumno','maestro']) && !empty($data['curso_id'])) {
            $now = Carbon::now();

            // Buscar nombre del curso
            $curso = Curso::find($data['curso_id']);
            $cursoNombre = $curso ? $curso->titulo : null;

            if ($data['role'] === 'alumno') {
                DB::table('alumno_curso')->updateOrInsert(
                    ['user_id' => $user->id],
                    ['id_cursos' => $data['curso_id'], 'created_at' => $now, 'updated_at' => $now]
                );
            } else {
                DB::table('curso_maestro')->updateOrInsert(
                    ['user_id' => $user->id],
                    ['id_cursos' => $data['curso_id'], 'created_at' => $now, 'updated_at' => $now]
                );
            }
        }

        $mensaje = "Usuario {$user->nombre_completo} creado correctamente";
        if ($cursoNombre) {
            $mensaje .= " y asignado al curso {$cursoNombre}";
        }

        return redirect()
            ->route('admin.usuarios.index')
            ->with('success', $mensaje);
    });
}

public function editUser(User $user)
{
    $cursos = Curso::select('id_cursos','titulo')->orderBy('titulo')->get();

    // curso actual (si es alumno o maestro)
    $cursoAlumno  = DB::table('alumno_curso')->where('user_id', $user->id)->value('id_cursos');
    $cursoMaestro = DB::table('curso_maestro')->where('user_id', $user->id)->value('id_cursos');

    $cursoActual = $cursoAlumno ?? $cursoMaestro; // null si admin

    return view('admin.edit-user', compact('user','cursos','cursoActual'));
}

public function updateUser(Request $request, User $user)
{
    $data = $request->validate([
        'nombre_completo'   => ['nullable','string','max:255'],
        'name'              => ['nullable','string','max:255'],
        'email'             => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
        'password'          => ['nullable','string','min:6'],
        'role'              => ['required', Rule::in(['admin','maestro','alumno'])],
        'estado'            => ['required', Rule::in(['activo','inactivo'])],
        'DNI'               => ['nullable','string','max:50'],
        'telefono'          => ['nullable','string','max:50'],
        'fecha_nacimiento'  => ['nullable','date'],
        'direccion'         => ['nullable','string'],
        'curso_id'          => ['required_if:role,alumno,maestro','nullable','exists:cursos,id_cursos'],
    ], [
        'curso_id.required_if' => 'Debes seleccionar un curso para alumnos o maestros.',
    ]);

    return DB::transaction(function () use ($data, $user) {

        // Actualizar usuario
        $user->fill([
            'nombre_completo'  => $data['nombre_completo'] ?? null,
            'name'             => $data['name'] ?? '',
            'email'            => $data['email'],
            'role'             => $data['role'],
            'estado'           => $data['estado'],
            'DNI'              => $data['DNI'] ?? null,
            'telefono'         => $data['telefono'] ?? null,
            'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            'direccion'        => $data['direccion'] ?? null,
        ]);

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        // Limpiar pivotes primero (por si cambia de rol)
        DB::table('alumno_curso')->where('user_id', $user->id)->delete();
        DB::table('curso_maestro')->where('user_id', $user->id)->delete();

        // Re-asignar pivote según rol
        if (in_array($data['role'], ['alumno','maestro']) && !empty($data['curso_id'])) {
            $now = Carbon::now();

            if ($data['role'] === 'alumno') {
                DB::table('alumno_curso')->updateOrInsert(
                    ['user_id' => $user->id],
                    ['id_cursos' => $data['curso_id'], 'created_at' => $now, 'updated_at' => $now]
                );
            } else {
                DB::table('curso_maestro')->updateOrInsert(
                    ['user_id' => $user->id],
                    ['id_cursos' => $data['curso_id'], 'created_at' => $now, 'updated_at' => $now]
                );
            }
        }

        $cursoNombre = null;
        if (!empty($data['curso_id'])) {
            $cursoNombre = optional(Curso::find($data['curso_id']))->titulo;
        }

        $msg = "Usuario {$user->nombre_completo} actualizado correctamente";
        if ($cursoNombre) {
            $msg .= " y asignado al curso {$cursoNombre}";
        }

        return redirect()->route('admin.usuarios.index')->with('success', $msg);
    });
}

public function destroyUser(User $user)
{
    try {
        // Evitar que te borres a ti mismo (opcional)
        if (auth()->id() === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes eliminar tu propio usuario.'
            ], 400);
        }

        DB::transaction(function () use ($user) {
            // Borra pivotes primero
            DB::table('alumno_curso')->where('user_id', $user->id)->delete();
            DB::table('curso_maestro')->where('user_id', $user->id)->delete();

            // Si tienes otras tablas que apunten a users (ej. entregas.alumno_id)
            // y la FK NO está con onDelete('cascade'),
            // debes borrarlas manualmente aquí:
            // DB::table('entregas')->where('alumno_id', $user->id)->delete();

            $user->delete();
        });

        return response()->json([
            'success' => true,
            'message' => "Usuario {$user->nombre_completo} eliminado correctamente."
        ]);
    } catch (\Throwable $e) {
        // Log real para ver la causa en storage/logs/laravel.log
        \Log::error('Eliminar usuario falló', [
            'user_id' => $user->id,
            'error'   => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'No se pudo eliminar el usuario.',
            'error'   => $e->getMessage(), // útil para depurar vía Network
        ], 500);
    }
}

public function showUser(User $user)
{
    // Curso por alumno (pivot)
    $cursoAlumnoId  = DB::table('alumno_curso')->where('user_id', $user->id)->value('id_cursos');

    // Curso por maestro (pivot)
    $cursoMaestroId = DB::table('curso_maestro')->where('user_id', $user->id)->value('id_cursos');

    $cursoId = $cursoAlumnoId ?? $cursoMaestroId;
    $curso   = $cursoId ? Curso::select('id_cursos','titulo')->find($cursoId) : null;

    // Nota final (si tienes la tabla/calculo de calificaciones)
    $notaFinal = null;
    try {
        // Si ya tienes el modelo Calificacion configurado:
        // $notaFinal = \App\Models\Calificacion::where('alumno_id', $user->id)
        //     ->where('id_cursos', $cursoId)
        //     ->value('nota_final');

        // O vía DB directo (si no tienes el modelo):
        if ($cursoId) {
            $notaFinal = DB::table('calificaciones')
                ->where('alumno_id', $user->id)
                ->where('id_cursos', $cursoId)
                ->value('nota_final');
        }
    } catch (\Throwable $e) {
        // Si la tabla no existe o aún no has creado calificaciones,
        // simplemente dejamos $notaFinal = null
        \Log::info('calificaciones no disponible o sin datos: '.$e->getMessage());
    }

    // Estado (aprobado / reprobado) solo si hay nota
    $estadoNota = null;
    if (!is_null($notaFinal)) {
        $estadoNota = $notaFinal >= 70 ? 'Aprobado' : 'Reprobado';
    }

    return view('admin.ver-users', compact('user', 'curso', 'notaFinal', 'estadoNota'));
}


public function suggestEmail(Request $request)
{
    $alias  = trim($request->input('alias', 'usuario'));
    $domain = trim($request->input('domain', ''));

    if ($domain === '') {
        $domain = Str::of(auth()->user()->email ?? 'example.com')->after('@');
    }

    // Base: "nombre.apellido" -> todo minúsculas, puntos en vez de espacios
    $base = Str::of($alias)->lower()->ascii()->replaceMatches('/[^a-z0-9]+/i', '.')->trim('.');

    if ($base === '') {
        $base = 'usuario';
    }

    $candidate = "{$base}@{$domain}";
    $i = 1;
    // Garantizar unicidad
    while (User::where('email', $candidate)->exists()) {
        $candidate = "{$base}{$i}@{$domain}";
        $i++;
        if ($i > 9999) break; // seguridad
    }

    return response()->json(['ok' => true, 'email' => $candidate]);
}

public function checkEmail(Request $request)
{
    $email = trim($request->query('email', ''));
    if ($email === '') {
        return response()->json(['ok' => true, 'exists' => false]);
    }

    $exists = \App\Models\User::where('email', $email)->exists();
    return response()->json(['ok' => true, 'exists' => $exists]);
}

}