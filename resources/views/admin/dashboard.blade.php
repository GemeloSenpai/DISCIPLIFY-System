<x-app-layout>
    <x-slot name="header">
        @php
            // Ya lo tenías; lo mantenemos aquí para mostrarlo en las "píldoras"
            $matriculadosGlobal = \Illuminate\Support\Facades\DB::table('alumno_curso')
                ->distinct('user_id')->count('user_id');

            // Totales derivados del $cursos que ya recibes en la vista
            $totalCursos   = $cursos->count();
            $totalAlumnosI = (int) $cursos->sum('alumnos_count'); // inscripciones (puede repetir alumnos)
            $totalTareas   = (int) $cursos->sum('tareas_count');  // total de tareas creadas
        @endphp

        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Panel de Administración') }}
            </h2>

            {{-- Métricas rápidas en píldoras, consistentes y mobile-first --}}
            <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center gap-2 bg-purple-50 text-purple-700 border border-purple-200 px-3 py-1.5 rounded-full text-sm">
                    📚 <span class="font-medium">Cursos:</span> <span class="font-semibold">{{ $totalCursos }}</span>
                </span>
                <span class="inline-flex items-center gap-2 bg-emerald-50 text-emerald-700 border border-emerald-200 px-3 py-1.5 rounded-full text-sm">
                    📝 <span class="font-medium">Tareas totales:</span> <span class="font-semibold">{{ $totalTareas }}</span>
                </span>
                <span class="inline-flex items-center gap-2 bg-amber-50 text-amber-700 border border-amber-200 px-3 py-1.5 rounded-full text-sm">
                    👨‍🎓 <span class="font-medium">Inscripciones:</span> <span class="font-semibold">{{ $totalAlumnosI }}</span>
                </span>
            </div>

            <a href="{{ route('admin.resultados') }}"
               class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                📊 Ver resultados
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Contenido del dashboard -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Resumen General</h3>

                    <!-- Sección de Cursos -->
                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-2">
                            <h4 class="font-medium text-gray-700">Cursos activos</h4>
                            <span class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-sm">
                                {{ $cursos->count() }} total
                            </span>
                        </div>

                        @php
                            // Orden “curado” que ya tenías
                            $ordenCursos = [
                                'Prediscipulado',
                                'Prediscipulado #2',
                                'Proverbios',
                                'Corintios',
                                'Realidades de la Cruz',
                                'Madurez',
                                'Enviados',
                            ];
                            $cursosOrdenados = $cursos->sortBy(function($curso) use ($ordenCursos) {
                                $pos = array_search($curso->titulo, $ordenCursos);
                                return $pos === false ? PHP_INT_MAX : $pos;
                            });
                        @endphp

                        @if($cursos->isEmpty())
                            <div class="rounded-xl border border-dashed p-8 text-center text-gray-600">
                                Aún no hay cursos activos.
                            </div>
                        @else
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($cursosOrdenados as $curso)
                                    <div class="border rounded-xl shadow p-4 bg-white hover:shadow-md transition">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center">
                                                📘
                                            </div>
                                            <h5 class="text-lg font-semibold text-indigo-700 truncate" title="{{ $curso->titulo }}">
                                                {{ $curso->titulo }}
                                            </h5>
                                        </div>

                                        {{-- Mentores --}}
                                        <div class="mt-3 text-sm text-gray-700">
                                            <p class="font-medium mb-1">🧑‍🏫 Mentor(es):</p>
                                            @if($curso->maestro && count($curso->maestro))
                                                <ul class="list-disc ml-5 space-y-0.5">
                                                    @foreach($curso->maestro as $m)
                                                        <li>{{ $m->name }}</li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <p class="text-gray-500">—</p>
                                            @endif
                                        </div>

                                        {{-- Métricas del curso --}}
                                        <div class="mt-3 grid grid-cols-3 gap-2 text-sm">
                                            <div class="flex items-center gap-1 text-gray-700">
                                                👨‍🎓 <span class="font-medium">Alumnos:</span>
                                                <span>{{ (int) ($curso->alumnos_count ?? 0) }}</span>
                                            </div>
                                            <div class="flex items-center gap-1 text-gray-700">
                                                📝 <span class="font-medium">Tareas:</span>
                                                <span>{{ (int) ($curso->tareas_count ?? 0) }}</span>
                                            </div>
                                            <div class="flex items-center gap-1 text-gray-700">
                                                🆔 <span class="font-medium">ID:</span>
                                                <span>{{ $curso->id_cursos }}</span>
                                            </div>
                                        </div>

                                        {{-- Acciones (1 fila, scroll-x en móvil como en el panel de mentor) --}}
                                        <div class="mt-4">
                                            <div class="flex flex-nowrap items-center gap-2 overflow-x-auto pb-1 -mx-1 px-1 scroll-smooth" style="-webkit-overflow-scrolling: touch;">
                                                <a href="{{ url('/admin/cursos/'.$curso->id_cursos.'/detalles') }}"
                                                   class="shrink-0 whitespace-nowrap inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                                   title="Ver detalles del curso">
                                                    🔎 Ver detalles
                                                </a>
                                                {{-- (Opcional) Más acciones de admin a futuro:
                                                <a href="#" class="shrink-0 whitespace-nowrap inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">⚙️ Gestionar</a>
                                                --}}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
