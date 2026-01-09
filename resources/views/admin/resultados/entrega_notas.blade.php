{{-- resources/views/admin/resultados/entrega_notas.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl md:text-2xl text-gray-800">
                Notas por Curso 
            </h2><br>

            <span class="text-sm text-gray-600">
                Alumnos matriculados (global): <b>{{ $alumnosMatriculados }}</b>
            </span><br>

        </div>

    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Filtros --}}
            <form method="GET" class="bg-white rounded shadow p-4 flex flex-wrap gap-3 items-end">
                <div class="w-full sm:w-72 min-w-0">
                    <label class="text-sm font-medium block mb-1">Curso</label>
                    <select name="curso_id"
                        class="w-full min-w-0 max-w-full border rounded px-2 py-2 text-sm sm:text-base">
                        <option value="">— Todos —</option>
                        @foreach($cursos as $c)
                        @php
                        $labelCompleto = trim(($c->titulo ?? '') . (isset($c->nombre) ? ' — ' . $c->nombre : ''));
                        @endphp
                        <option value="{{ $c->id_cursos }}"
                            {{ (string)$cursoId === (string)$c->id_cursos ? 'selected' : '' }}
                            title="{{ $labelCompleto }}">
                            {{ \Illuminate\Support\Str::limit($labelCompleto, 40) }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium block mb-1">Umbral (aprobación)</label>
                    <input type="number" step="0.01" name="umbral" value="{{ $umbral }}"
                        class="border rounded px-2 py-1 w-28">
                </div>
                <div>
                    <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded">
                        Filtrar
                    </button>
                </div>
            </form>

            {{-- Resultado por curso --}}
            @forelse($byCurso as $cid => $pack)
            @php
            $info = $pack['curso'];
            $st = $stats[$cid] ?? ['matriculados'=>0,'aprobados'=>0,'reprobados'=>0];
            @endphp

            <div class="bg-white rounded shadow overflow-hidden">
                <div class="px-4 py-3 border-b flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <div class="text-sm text-gray-500">{{ $info['titulo'] }}</div>
                        <div class="text-lg font-semibold">{{ $info['nombre'] }}</div>
                    </div>
                    <div class="text-sm text-gray-700 flex flex-wrap gap-3">
                        <span>Matriculados: <b>{{ $st['matriculados'] }}</b></span>
                        <span class="text-green-700">Aprobados: <b>{{ $st['aprobados'] }}</b></span>
                        <span class="text-red-700">Reprobados: <b>{{ $st['reprobados'] }}</b></span>
                        <span class="text-gray-500">Umbral: {{ number_format($umbral,2) }}</span>
                    </div>
                </div>

                <div class="p-4 overflow-x-auto">
                    <table class="min-w-[900px] w-full text-sm border">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="border px-2 py-1 text-left">#</th>
                                <th class="border px-2 py-1 text-left">Alumno</th>
                                <th class="border px-2 py-1 text-center">Σ Tareas</th>
                                <th class="border px-2 py-1 text-center">Σ Evaluaciones</th>
                                <th class="border px-2 py-1 text-center">Σ Examen</th>
                                <th class="border px-2 py-1 text-center">Total</th>
                                <th class="border px-2 py-1 text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pack['alumnos'] as $i => $a)
                            <tr class="border-b">
                                <td class="border px-2 py-1">{{ $i + 1 }}</td>
                                <td class="border px-2 py-1">{{ $a['nombre'] }}</td>
                                <td class="border px-2 py-1 text-center">{{ number_format($a['sum_tareas'], 2) }}</td>
                                <td class="border px-2 py-1 text-center">{{ number_format($a['sum_evals'], 2) }}</td>
                                <td class="border px-2 py-1 text-center">{{ number_format($a['sum_examen'], 2) }}</td>
                                <td class="border px-2 py-1 text-center font-semibold">
                                    {{ number_format($a['total'], 2) }}</td>
                                <td class="border px-2 py-1 text-center">
                                    @if($a['aprobado'])
                                    <span
                                        class="inline-block px-2 py-0.5 rounded bg-green-100 text-green-700">Aprobado</span>
                                    @else
                                    <span
                                        class="inline-block px-2 py-0.5 rounded bg-red-100 text-red-700">Reprobado</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-3 py-4 text-center text-gray-500">
                                    No hay alumnos para este curso.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @empty
            <div class="bg-white rounded shadow p-6 text-center text-gray-600">
                No hay datos para mostrar con los filtros actuales.
            </div>
            @endforelse

        </div>
    </div>
</x-app-layout>