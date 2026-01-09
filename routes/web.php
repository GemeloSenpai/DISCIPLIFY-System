<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MaestroController;
use App\Http\Controllers\CalificacionesController;
use App\Http\Controllers\SesionController;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\AdminResultadosController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\PromoNewController;
use App\Http\Controllers\BoletaController;


// --- Utilidad rápida para probar conexión DB
Route::get('/db-check', function () {
    try {
        DB::connection()->getPdo();
        return 'OK DB: '.DB::connection()->getDatabaseName();
    } catch (\Throwable $e) {
        return 'ERROR: '.$e->getMessage();
    }
});

// --- Página raíz: redirección según rol
Route::get('/', function () {
    if (Auth::check()) {
        return match (Auth::user()->role) {
            'admin'   => redirect()->route('admin.dashboard'),
            'maestro' => redirect()->route('maestro.dashboard'),
            'alumno'  => redirect()->route('alumno.dashboard'),
            default   => redirect()->route('login'),
        };
    }
    return redirect()->route('login');
});

// --- Perfil (auth)
Route::middleware('auth')->group(function () {
    Route::get('/profile',   [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',[ProfileController::class, 'destroy'])->name('profile.destroy');
});

// --- Alias del middleware de rol (si no estaba registrado)
app('router')->aliasMiddleware('role', \App\Http\Middleware\RoleMiddleware::class);

// ===============================
// ==========  ADMIN  ============
// ===============================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Cursos (admin)
    Route::get('/cursos/gestionar',        [AdminController::class, 'gestionarCursos'])->name('cursos.gestionar');
    Route::get('/cursos/create',           [AdminController::class, 'create'])->name('cursos.create');
    Route::post('/cursos',                 [AdminController::class, 'store'])->name('cursos.store');
    Route::get('/cursos/{curso}/edit',     [AdminController::class, 'edit'])->name('cursos.edit');
    Route::put('/cursos/{curso}',          [AdminController::class, 'update'])->name('cursos.update');
    Route::delete('/cursos/{curso}',       [AdminController::class, 'destroy'])->name('cursos.destroy');

    // Detalles de curso (reutiliza vista del maestro)
    Route::get('/cursos/{id}/detalles', [\App\Http\Controllers\MaestroController::class, 'showCurso'])
        ->name('cursos.show');

    // Asignar / retirar maestros
    Route::post('/cursos/{curso}/maestros', [AdminController::class, 'asignarMaestros'])
        ->name('cursos.maestros.sync');

    // Resultados y sesiones
    Route::get('/resultados', [\App\Http\Controllers\AdminResultadosController::class, 'entregaNotas'])
        ->name('resultados');
    Route::get('/cursos/{curso}/sesiones', [\App\Http\Controllers\SesionController::class, 'index'])
        ->name('sesiones.index');
    Route::get('/cursos/{curso}/asistencias/resultados', [\App\Http\Controllers\SesionController::class, 'resultados'])
        ->name('asistencias.resultados');

    // Promoción (por curso con PromocionController existente)
    Route::get('/promocion', [\App\Http\Controllers\PromocionController::class, 'index'])
        ->name('promocion.index');
    Route::get('/cursos/{curso}/promocion', [\App\Http\Controllers\PromocionController::class, 'show'])
        ->name('cursos.promocion.show');
    Route::post('/cursos/{curso}/promocion', [\App\Http\Controllers\PromocionController::class, 'promote'])
        ->name('cursos.promocion.run');

    // Promoción total / historial / graduados (PromoNewController)
    Route::prefix('promocion')->name('promocion.')->group(function () {
        Route::get('/total',            [PromoNewController::class, 'promocionTotal'])->name('total');
        Route::post('/total/run',       [PromoNewController::class, 'promocionTotalRun'])->name('total.run');

        Route::get('/total/detalle',    [PromoNewController::class, 'promocionDetalle'])->name('total.detalle');
        Route::get('/total/historial',  [PromoNewController::class, 'historial'])->name('total.historial'); // por curso (JSON)
        Route::get('/historial-fecha',  [PromoNewController::class, 'historialPorFecha'])->name('historial.fecha'); // por rango (JSON)

        Route::post('/total/masivo',    [PromoNewController::class, 'promocionMasivoRun'])->name('total.masivo.run');

        // Mantengo este nombre porque tu Blade lo usa: route('admin.promocion.graduados')
        Route::get('/graduados',        [PromoNewController::class, 'graduadosIndex'])->name('graduados');

        // NEW: detalle JSON para el modal
        Route::get('/graduados/detalle', [PromoNewController::class, 'graduadoDetalleAlumno'])->name('graduados.detalle');

        Route::get('/admin/promocion/corridas', [PromoNewController::class, 'corridasRecientes'])->name('corridas');
        
    });

    // ===== Usuarios =====
    Route::prefix('usuarios')->name('usuarios.')->group(function () {
        Route::get('/',            [AdminController::class, 'vistaUsuarios'])->name('index');
        Route::get('/create',      [AdminController::class, 'createUser'])->name('create');
        Route::post('/',           [AdminController::class, 'storeUser'])->name('store');
        Route::get('/buscar',      [AdminController::class, 'buscarUsuarios'])->name('buscar');

        // AJAX email (UserController)
        Route::get('/check-email',   [UserController::class, 'checkEmail'])->name('checkEmail');
        Route::get('/sugerir-email', [UserController::class, 'suggestEmail'])->name('suggestEmail');

        // Con parámetro (sólo números)
        Route::get('/{user}',        [AdminController::class, 'showUser'])->whereNumber('user')->name('show');
        Route::get('/{user}/edit',   [AdminController::class, 'editUser'])->whereNumber('user')->name('edit');
        Route::put('/{user}',        [AdminController::class, 'updateUser'])->whereNumber('user')->name('update');
        Route::delete('/{user}',     [AdminController::class, 'destroyUser'])->whereNumber('user')->name('destroy');
    });

    // ===== Boletas =====
    // Índice/selector (no requiere $alumno)
    Route::get('/resultados/boleta', [BoletaController::class, 'index'])
        ->name('resultados.boleta');

    // Mostrar/editar boleta de un alumno
    Route::get('/resultados/boleta/{alumno}', [BoletaController::class, 'show'])
        ->whereNumber('alumno')->name('resultados.boleta.show');

    // Guardar boleta de un alumno
    Route::post('/resultados/boleta/{alumno}', [BoletaController::class, 'save'])
        ->whereNumber('alumno')->name('resultados.boleta.save');



});


// ===============================
// =========  MAESTRO  ===========
// ===============================
Route::middleware(['auth', 'role:maestro'])->prefix('maestro')->group(function () {

    Route::get('/dashboard',     [MaestroController::class, 'dashboard'])->name('maestro.dashboard');
    Route::get('/cursos',        [MaestroController::class, 'cursos'])->name('maestro.cursos');

    // Vista de detalles del curso (maestro.detalles)
    Route::get('/cursos/{id}/detalles', [MaestroController::class, 'showCurso'])
        ->name('maestro.cursos.show');

    // Calificaciones (cuadro y upsert)
    Route::get('/cursos/{id_cursos}/calificaciones', [CalificacionesController::class, 'cuadro'])
        ->name('maestro.calificaciones.cuadro');
    Route::get('/calificaciones', [CalificacionesController::class, 'desdeSesion'])
        ->name('maestro.calificaciones');
    Route::post('/calificaciones/upsert', [CalificacionesController::class, 'upsert'])
        ->name('maestro.calificaciones.upsert');

    // Sesiones del curso
    Route::get('/cursos/{curso}/sesiones',                [SesionController::class, 'index'])->name('maestro.sesiones.index');
    Route::post('/cursos/{curso}/sesiones',               [SesionController::class, 'store'])->name('maestro.sesiones.store');
    Route::get('/cursos/{curso}/sesiones/{sesion}',       [SesionController::class, 'show'])->name('maestro.sesiones.show');
    Route::delete('/cursos/{curso}/sesiones/{sesion}',    [SesionController::class, 'destroy'])->name('maestro.sesiones.destroy');


    // Asistencias (AJAX + resultados)
    Route::put('/asistencias/{asistencia}', [AsistenciaController::class, 'update'])
        ->name('maestro.asistencias.update');
    Route::get('/cursos/{curso}/asistencias/resultados', [SesionController::class, 'resultados'])
        ->name('maestro.asistencias.resultados');

    // Tareas
    Route::post('/tareas/store', [MaestroController::class, 'storeTarea'])
        ->name('maestro.tareas.store');
    Route::put('/tareas/{tarea:id_tareas}', [MaestroController::class, 'updateTarea'])
        ->name('maestro.tareas.update');
    Route::delete('/tareas/{tarea:id_tareas}', [MaestroController::class, 'destroyTarea'])
        ->name('maestro.tareas.destroy');

    // Entregas (si lo sigues usando)
    Route::post('/entregas/update', [MaestroController::class, 'actualizarEntrega'])
        ->name('maestro.entregas.update');

    // Atajo para ir al curso guardado en sesión
    // Atajo para abrir detalles del curso “actual” o el primero asignado
    // Ver /maestro/curso/actual => toma el primer curso del mentor y va a detalles
Route::get('/maestro/curso/actual', function () {
    $uid = auth()->id();

    $curso = \DB::table('cursos')
        ->join('curso_maestro', 'curso_maestro.id_cursos', '=', 'cursos.id_cursos')
        ->where('curso_maestro.user_id', $uid)
        ->orderBy('cursos.titulo')
        ->select('cursos.id_cursos', 'cursos.titulo') // 👈 evita "ambiguous column"
        ->first();

    if (!$curso) {
        return redirect()->route('maestro.dashboard')
            ->with('warning', 'No tienes cursos asignados.');
    }

    // showCurso ya setea curso_actual en sesión
    return redirect()->route('maestro.cursos.show', $curso->id_cursos);
})->middleware(['auth','role:maestro'])->name('maestro.curso.actual');

    // routes/web.php (bloque maestro)


        // routes/web.php (bloque maestro)
Route::put('/cursos/{curso}/sesiones/{sesion}', [SesionController::class, 'update'])
    ->name('maestro.sesiones.update');


   Route::get('/tareas/{tarea:id_tareas}/pendientes', [MaestroController::class, 'pendientesTarea'])
    ->name('maestro.tareas.pendientes');

});

// ===============================
// =========  ALUMNO  ============
// ===============================
Route::middleware(['auth', 'role:alumno'])->prefix('alumno')->group(function () {
    Route::get('/dashboard', function () {
        return view('alumno.dashboard');
    })->name('alumno.dashboard');
    // ... tus demás rutas de alumno
});

// ===============================
// ==========  LEGAL  ============
// ===============================
Route::view('/privacidad', 'legal.privacy')->name('privacy');
Route::view('/terminos',   'legal.terms')->name('terms');

// Auth scaffolding (Breeze/Fortify/Jetstream)
require __DIR__.'/auth.php';