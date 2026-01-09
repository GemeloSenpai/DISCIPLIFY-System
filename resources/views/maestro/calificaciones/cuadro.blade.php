<x-app-layout>
    @php
    // Fallback por si no te pasan cursoId (de la URL)
    $cursoId = $cursoId ?? request()->route('id_cursos');
    @endphp

    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Cuadro de acumulados de ') . (($curso->titulo ?? null) ?: ('Curso #' . $cursoId)) }}
            </h2>

            @if(!empty($mentores))
            <div class="mt-1 text-sm text-gray-600">
                <span class="font-semibold">Mentores a cargo:</span>
                {{ implode(', ', $mentores) }}
            </div>
            @endif

        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full w-full mx-auto px-2 sm:px-4 lg:px-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg w-full">
                <div class="p-4 sm:p-6 text-gray-900">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base sm:text-lg font-semibold">Alumnos del curso</h3>
                        <div id="status" class="text-sm"></div>
                    </div>

                    <style>
                    .overflow-x-auto {
                        -webkit-overflow-scrolling: touch;
                    }
                    </style>


                    <div class="w-full overflow-x-auto overflow-y-hidden ring-1 ring-indigo-200 rounded-lg">
                        <table class="w-full min-w-max text-sm align-middle">
                            {{-- ================= ENCABEZADO ================= --}}
                            <thead class="bg-indigo-600 text-white sticky top-0 z-10">
                                @php
                                $totalCols = 15;
                                $titulos = $tareas->pluck('titulo')->toArray();
                                for ($i = count($titulos) + 1; $i <= $totalCols; $i++) { $titulos[]="Tarea $i" ; }
                                    @endphp <tr>
                                    {{-- # --}}
                                    <th
                                        class="px-2 py-3 text-left font-semibold uppercase tracking-wide text-[11px] sm:text-xs w-16 min-w-16">
                                        #
                                    </th>

                                    {{-- Alumno (antes tenía: sticky left-16 z-40 bg-indigo-600 ...) --}}
                                    <th
                                        class="px-3 sm:px-4 py-3 text-left font-semibold uppercase tracking-wide text-[11px] sm:text-xs bg-indigo-600 w-72 min-w-72">
                                        <span class="block truncate">Alumno</span>
                                    </th>

                                    {{-- Teléfono (antes tenía: sticky left-[352px] z-40 bg-indigo-600 ...) --}}
                                    <th
                                        class="px-3 sm:px-4 py-3 text-left font-semibold uppercase tracking-wide text-[11px] sm:text-xs bg-indigo-600 w-40 min-w-40">
                                        Teléfono
                                    </th>

                                    {{-- 15 tareas con nombre vertical: "N. Título" --}}
                                    @for ($i = 1; $i <= $totalCols; $i++) <th class="p-0 align-bottom text-center">
                                        <div class="h-28 flex items-center justify-center px-1">
                                            <span
                                                class="block text-[10px] sm:text-xs font-medium tracking-wide leading-none [writing-mode:vertical-rl]"
                                                title="{{ $i }}. {{ $titulos[$i-1] }}">
                                                {{ $i }}. {{ $titulos[$i-1] }}
                                            </span>
                                        </div>
                                        </th>
                                        @endfor

                                        {{-- Evaluaciones + Examen + Total --}}
                                        @foreach (['Eval 1','Eval 2','Eval 3','Eval 4','Examen','Total'] as $lab)
                                        <th
                                            class="px-2 py-3 text-center font-semibold uppercase tracking-wide text-[11px] sm:text-xs">
                                            {{ $lab }}
                                        </th>
                                        @endforeach
                                        </tr>
                            </thead>

                            {{-- ================= CUERPO ================= --}}
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($alumnos as $idx => $al)
                                @php
                                $a = $byAlumno[$al->id] ?? ['tareas'=>[],'evals'=>[],'examen'=>null];
                                $sumTareas = 0; for ($j=1; $j<=15; $j++) { $sumTareas +=floatval($a['tareas'][$j] ?? 0);
                                    } $sumEval=0; for ($j=1; $j<=4; $j++) { $sumEval +=floatval($a['evals'][$j] ?? 0); }
                                    $examen=floatval($a['examen'] ?? 0); $total=$sumTareas + $sumEval + $examen; @endphp
                                    <tr>
                                    {{-- # --}}
                                    <td class="px-2 py-2 w-16 min-w-16">{{ $idx + 1 }}</td>

                                    {{-- Alumno (antes: sticky left-16 bg-white z-30 ...) --}}
                                    <td class="px-3 sm:px-4 py-2 bg-white w-72 min-w-72">
                                        <div class="truncate" title="{{ $al->nombre_completo ?? '(sin nombre)' }}">
                                            {{ $al->nombre_completo ?? '(sin nombre)' }}
                                        </div>
                                    </td>

                                    {{-- Teléfono (antes: sticky left-[352px] bg-white z-30 ...) --}}
                                    <td class="px-3 sm:px-4 py-2 bg-white w-40 min-w-40">
                                        <div class="truncate" title="{{ $al->telefono ?? '' }}">
                                            {{ $al->telefono ?? '' }}
                                        </div>
                                    </td>

                                    {{-- 15 tareas como inputs (0–100) --}}
                                    @for ($i = 1; $i <= 15; $i++) @php $val=$a['tareas'][$i] ?? null;
                                        $tareaObj=$tareas[$i-1] ?? null; $tareaId=$tareaObj->id_tareas ?? null;
                                        $disabled = $tareaId ? '' : 'disabled';
                                        @endphp
                                        <td class="p-1 text-center">
                                            <input type="number" min="0" step="0.01"
                                                class="w-20 border rounded p-1 text-center celda {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                data-tipo="tarea" data-numero="{{ $i }}" data-alumno="{{ $al->id }}"
                                                data-curso="{{ $cursoId }}" data-tarea-id="{{ $tareaId ?? '' }}"
                                                value="{{ $val === null ? '' : $val }}" {{ $disabled }}
                                                title="{{ $tareaObj?->titulo ? ($i.'. '.$tareaObj->titulo) : ('Tarea '.$i) }}" />
                                        </td>
                                        @endfor

                                        {{-- 4 evaluaciones --}}
                                        @for ($i = 1; $i <= 4; $i++) @php $val=$a['evals'][$i] ?? null; @endphp <td
                                            class="p-1 text-center">
                                            <input type="number" min="0" max="100" step="0.01"
                                                class="w-20 border rounded p-1 text-center celda" data-tipo="evaluacion"
                                                data-numero="{{ $i }}" data-alumno="{{ $al->id }}"
                                                data-curso="{{ $cursoId }}" value="{{ $val === null ? '' : $val }}"
                                                title="Evaluación {{ $i }}" />
                                            </td>
                                            @endfor

                                            {{-- Examen --}}
                                            @php $valEx = $a['examen'] ?? null; @endphp
                                            <td class="p-1 text-center">
                                                <input type="number" min="0" max="100" step="0.01"
                                                    class="w-20 border rounded p-1 text-center celda" data-tipo="examen"
                                                    data-numero="1" data-alumno="{{ $al->id }}"
                                                    data-curso="{{ $cursoId }}"
                                                    value="{{ $valEx === null ? '' : $valEx }}" title="Examen" />
                                            </td>

                                            {{-- Total --}}
                                            <td class="px-2 py-2 text-center font-semibold">
                                                {{ number_format($total, 2) }}
                                            </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="25" class="p-4 text-center text-gray-500">
                                                    No hay alumnos inscritos para este curso.
                                                </td>
                                            </tr>
                                            @endforelse
                            </tbody>
                        </table>
                    </div>


                    <div class="mt-4 text-xs text-gray-500">
                        <p>
                            <strong>Nota:</strong> Total = <em>#Tareas entregadas</em> + Σ<em>Evaluaciones</em> +
                            <em>Examen</em>.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- === Script original (sin cambios de lógica) === --}}
    <script>
    const CSRF = '{{ csrf_token() }}';
    const URL_UPSERT = "{{ route('maestro.calificaciones.upsert') }}";
    let dirty = false;

    function clampNota(v) {
        if (v === '' || v === null || isNaN(v)) return null;
        const n = Number(v);
        return n < 0 ? 0 : n;
    }

    function recalcFila(tr) {
        const getVal = (inp) => {
            const v = clampNota(inp.value);
            return v === null ? 0 : v;
        };
        let sumTareas = 0,
            sumEvals = 0,
            examen = 0;
        tr.querySelectorAll('input.celda[data-tipo="tarea"]').forEach(i => sumTareas += getVal(i));
        tr.querySelectorAll('input.celda[data-tipo="evaluacion"]').forEach(i => sumEvals += getVal(i));
        const ex = tr.querySelector('input.celda[data-tipo="examen"]');
        if (ex) examen = getVal(ex);
        const total = (sumTareas + sumEvals + examen).toFixed(2);
        const totalTd = tr.querySelector('td:last-child');
        if (totalTd) totalTd.textContent = total;
    }

    async function guardarCelda(input) {
        const tipo = input.dataset.tipo;
        const numero = parseInt(input.dataset.numero, 10);
        const alumno = parseInt(input.dataset.alumno, 10);
        const curso = parseInt(input.dataset.curso, 10);
        const tareaId = input.dataset.tareaId ? parseInt(input.dataset.tareaId, 10) : null;

        if (tipo === 'tarea' && (!tareaId || Number.isNaN(tareaId))) {
            input.value = '';
            return;
        }

        const nota = clampNota(input.value);
        const body = {
            alumno_id: alumno,
            id_cursos: curso,
            tipo,
            numero,
            nota
        };
        if (tipo === 'tarea') body.tarea_id = tareaId;

        input.disabled = true;
        const res = await fetch(URL_UPSERT, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF
            },
            body: JSON.stringify(body),
            credentials: 'same-origin'
        });

        const statusEl = document.getElementById('status');
        if (res.status >= 200 && res.status < 300) {
            dirty = false;
            recalcFila(input.closest('tr'));
            if (statusEl) {
                statusEl.textContent = 'Guardado ✓';
                statusEl.className = 'text-sm text-green-600';
                setTimeout(() => {
                    statusEl.textContent = '';
                }, 1500);
            }
        } else {
            if (statusEl) {
                statusEl.textContent = 'Error al guardar';
                statusEl.className = 'text-sm text-red-600';
                setTimeout(() => {
                    statusEl.textContent = '';
                }, 2000);
            }
            console.warn('No OK', res.status, await res.text());
        }
        input.disabled = false;
    }

    document.querySelectorAll('input.celda').forEach(inp => {
        inp.addEventListener('input', () => {
            dirty = true;
        });
        inp.addEventListener('change', (e) => {
            const n = clampNota(e.target.value);
            e.target.value = (n === null ? '' : n);
            guardarCelda(e.target);
        });
    });

    window.addEventListener('beforeunload', function(e) {
        if (dirty) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
    </script>
</x-app-layout>