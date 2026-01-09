<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight text-center">
            {{ __('Panel del Mentor') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Resumen General</h3>

                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-2">
                            <h4 class="font-medium text-gray-700">Tus cursos</h4>
                            <span class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-sm">
                                {{ $cursos->count() }} asignado(s)
                            </span>
                        </div>

                        @if($cursos->isEmpty())
                        <div class="rounded-xl border border-dashed p-8 text-center text-gray-600">
                            Aún no tienes cursos asignados.
                        </div>
                        @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($cursos as $c)
                            @php
                            $alumnos = (int) ($c->alumnos_count ?? 0);
                            $tareas = (int) ($c->tareas_count ?? 0);
                            $real = (int) ($c->entregas_realizadas ?? 0);
                            $esper = max($alumnos * $tareas, 0);
                            $pct = $esper > 0 ? floor(($real / $esper) * 100) : 0;
                            $pct = min(100, $pct);
                            @endphp

                            <div class="border rounded-xl shadow p-4 bg-white hover:shadow-md transition">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="h-10 w-10 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center">
                                        <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                                            <path
                                                d="M2.5 5.5A1.5 1.5 0 0 1 4 4h12a1.5 1.5 0 0 1 1.5 1.5v9A1.5 1.5 0 0 1 16 16H4A1.5 1.5 0 0 1 2.5 14.5v-9ZM4 5.5V7h12V5.5H4Zm0 3V14h12V8.5H4Z" />
                                        </svg>
                                    </div>
                                    <h5 class="text-lg font-semibold text-indigo-700 truncate"
                                        title="{{ $c->titulo ?? ('Curso #'.$c->id_cursos) }}">
                                        {{ $c->titulo ?? ('Curso #'.$c->id_cursos) }}
                                    </h5>
                                </div>

                                <div class="mt-3 grid grid-cols-3 gap-2 text-sm">
                                    <div class="flex items-center gap-1 text-gray-700">
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M10 2a4 4 0 1 1 0 8 4 4 0 0 1 0-8ZM3 16a7 7 0 0 1 14 0v1H3v-1Z" />
                                        </svg>
                                        <span class="font-medium">Alumnos:</span>
                                        <span>{{ $alumnos }}</span>
                                    </div>
                                    <div class="flex items-center gap-1 text-gray-700">
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path
                                                d="M4 3.5A1.5 1.5 0 0 1 5.5 2h9A1.5 1.5 0 0 1 16 3.5V5H4V3.5ZM4 6.5V15A1.5 1.5 0 0 0 5.5 16.5h9A1.5 1.5 0 0 0 16 15V6.5H4Z" />
                                        </svg>
                                        <span class="font-medium">Tareas:</span>
                                        <span>{{ $tareas }}</span>
                                    </div>
                                    <div class="flex items-center gap-1 text-gray-700">
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path
                                                d="M16.7 5.3a1 1 0 0 1 0 1.4l-7.5 7.5a1 1 0 0 1-1.4 0L3.3 9.7a1 1 0 1 1 1.4-1.4l3.1 3.1 6.8-6.8a1 1 0 0 1 1.4 0z" />
                                        </svg>
                                        <span class="font-medium">Entregas:</span>
                                        <span>{{ $real }}/{{ $esper }}</span>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <div class="flex items-center justify-between text-sm mb-1">
                                        <span class="text-gray-600 inline-flex items-center gap-1">
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path
                                                    d="M2 10a8 8 0 1 1 16 0A8 8 0 0 1 2 10Zm9-4.5a.75.75 0 0 0-1.5 0V10c0 .2.08.39.22.53l2.5 2.5a.75.75 0 1 0 1.06-1.06l-2.28-2.28V5.5Z" />
                                            </svg>
                                            Progreso de entregas
                                        </span>
                                        <span class="text-gray-800 font-medium">{{ $pct }}%</span>
                                    </div>
                                    <div class="h-2.5 w-full bg-gray-200 rounded-full overflow-hidden">
                                        <div class="h-2.5 bg-emerald-500 rounded-full" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>

                                {{-- Acciones (siempre en 1 fila con scroll horizontal en móviles) --}}
                                <div class="mt-4">
                                    <div class="flex flex-nowrap items-center gap-2 overflow-x-auto pb-1 -mx-1 px-1
                scroll-smooth" style="-webkit-overflow-scrolling: touch;">
                                        <a href="{{ route('maestro.cursos.show', $c->id_cursos) }}"
                                            class="shrink-0 whitespace-nowrap inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                            
                                            Detalles
                                        </a>

                                        <a href="{{ route('maestro.sesiones.index', $c->id_cursos) }}"
                                            class="shrink-0 whitespace-nowrap inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                                            
                                            Sesiones
                                        </a>

                                        <a href="{{ route('maestro.calificaciones.cuadro', $c->id_cursos) }}"
                                            class="shrink-0 whitespace-nowrap inline-flex items-center gap-2 rounded-lg bg-purple-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                                            
                                            Acumulados
                                        </a>
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