<x-app-layout>
    @php
    $nombreCurso = $curso->titulo ?? $curso->nombre ?? $curso->nombre_curso ?? ('Curso #'.$curso->id_cursos);
    $totalClases = $sesionesConClase->count();
    @endphp

    <x-slot name="header">
        <div class="space-y-2">
            <h2 class="font-semibold text-xl md:text-2xl text-gray-900">
                Resultados de asistencia — {{ $nombreCurso }}
            </h2>
            <div class="flex flex-wrap items-center gap-2 text-sm">
                <span class="inline-flex items-center gap-2 rounded-full bg-indigo-50 text-indigo-700 px-3 py-1.5">
                    <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
                    Total de sesiones con clase: <b>{{ $totalClases }}</b>
                </span>
                <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 text-amber-700 px-3 py-1.5">
                    <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                    Regla: <b>3 ausencias</b> ⇒ <span class="font-semibold">pierde derecho a examen</span>
                </span>
                @if($totalClases === 0)
                <span class="inline-flex items-center gap-2 rounded-full bg-red-50 text-red-700 px-3 py-1.5">
                    <span class="h-2 w-2 rounded-full bg-red-500"></span>
                    Aún no hay sesiones con clase
                </span>
                @endif
            </div>

        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- CARD --}}
            <div class="bg-white/90 backdrop-blur overflow-hidden shadow-sm sm:rounded-2xl ring-1 ring-gray-200">
                <div class="p-4 sm:p-6">

                    {{-- WRAPPER con overflow + bordes redondeados visibles --}}
                    <div class="relative overflow-x-auto rounded-xl ring-1 ring-gray-200">
                        <table class="w-full text-[15px] text-gray-800 align-middle">
                            {{-- HEADER pegajoso, con gradiente y esquinas redondeadas --}}
                            <thead class="sticky top-0 z-20">
                                <tr class="bg-purple-600 text-white">
                                    <th
                                        class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-xs sticky left-0 z-30 bg-purple-600">
                                        #
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-xs sticky left-14 z-30 bg-purple-600">
                                        Alumno
                                    </th>
                                    @foreach($sesiones as $s)
                                    <th class="px-3 py-3 text-center font-semibold uppercase tracking-wide text-[11px]"
                                        title="{{ $s->tema ? 'Tema: '.$s->tema : '' }}">
                                        {{ \Carbon\Carbon::parse($s->fecha)->isoFormat('DD/MM') }}
                                        @if($s->tipo === 'sin_clase')
                                        <span class="block text-[10px] text-indigo-100/90">sin clase</span>
                                        @endif
                                    </th>
                                    @endforeach
                                    <th class="px-4 py-3 text-center font-semibold uppercase tracking-wide text-xs">
                                        Faltas
                                    </th>
                                    <th class="px-4 py-3 text-center font-semibold uppercase tracking-wide text-xs">
                                        Examen
                                    </th>
                                </tr>
                            </thead>


                            {{-- BODY con zebra + hover --}}
                            <tbody class="divide-y divide-gray-200">
                                @forelse($alumnos as $i => $al)
                                @php
                                $f = $faltas[$al->id] ?? 0;
                                $pierdeExamen = $pierde[$al->id] ?? false;
                                @endphp
                                <tr class="odd:bg-white even:bg-slate-50 hover:bg-slate-100 transition-colors">
                                    {{-- # (sticky) - ancho fijo para alinear la segunda columna --}}
                                    <td class="px-4 py-3 sticky left-0 bg-inherit z-10 text-gray-900 font-medium w-14">
                                        {{ $i + 1 }}
                                    </td>

                                    {{-- Alumno (sticky) --}}
                                    <td
                                        class="px-4 py-3 sticky left-14 bg-inherit z-10 min-w-[16rem] text-gray-900 font-medium">
                                        {{ $al->nombre_completo }}
                                    </td>

                                    {{-- Celdas por sesión --}}
                                    @foreach($sesiones as $s)
                                    @php
                                    if ($s->tipo === 'sin_clase') {
                                    $badge = ['—', 'bg-gray-100 text-gray-400'];
                                    } else {
                                    $estado = $asisMap[$al->id][$s->id_sesiones]['estado'] ?? null;
                                    switch ($estado) {
                                    case 'presente': $badge = ['P','bg-green-100 text-green-700']; break;
                                    case 'tarde': $badge = ['T','bg-yellow-100 text-yellow-700']; break;
                                    case 'justificado': $badge = ['J','bg-blue-100 text-blue-700']; break;
                                    case 'ausente': $badge = ['A','bg-red-100 text-red-700']; break;
                                    default: $badge = ['·','bg-gray-100 text-gray-500'];
                                    }
                                    }
                                    @endphp
                                    <td class="px-2 py-2 text-center align-middle">
                                        <span
                                            class="inline-block min-w-[1.75rem] rounded-md px-2 py-1 text-xs font-semibold {{ $badge[1] }}">
                                            {{ $badge[0] }}
                                        </span>
                                    </td>
                                    @endforeach

                                    {{-- Totales / estado examen --}}
                                    <td class="px-4 py-3 text-center font-semibold text-gray-900">
                                        {{ $f }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($pierdeExamen)
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold bg-red-100 text-red-700">
                                            ❌ Pierde derecho
                                        </span>
                                        @else
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold bg-green-100 text-green-700">
                                            ✅ Habilitado
                                        </span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ 2 + $sesiones->count() + 2 }}"
                                        class="px-4 py-6 text-center text-gray-500">
                                        No hay alumnos inscritos en este curso.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Leyenda --}}
                    <div class="mt-5 text-xs text-gray-600">
                        <p class="mb-1"><b>Leyenda:</b>
                            <span class="inline-block rounded px-2 py-0.5 mx-1 bg-green-100 text-green-700">P =</span>
                            Presente,
                            <span class="inline-block rounded px-2 py-0.5 mx-1 bg-yellow-100 text-yellow-700">T =</span>
                            Tarde,
                            <span class="inline-block rounded px-2 py-0.5 mx-1 bg-blue-100 text-blue-700">J =</span>
                            Justificado,
                            <span class="inline-block rounded px-2 py-0.5 mx-1 bg-red-100 text-red-700">A =</span>
                            Ausente,
                            <span class="inline-block rounded px-2 py-0.5 mx-1 bg-gray-100 text-gray-500">—</span> Sin
                            clase
                        </p>
                        <p>Solo se cuentan como <b>faltas</b> las sesiones con clase marcadas <b>Ausente</b>.</p>
                    </div>

                    {{-- Botón volver (mobile-friendly) --}}
                    <div class="mt-6">
                        <button type="button" onclick="history.back()"
                            class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg ring-1 ring-gray-300">
                            ← Atrás
                        </button>
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>