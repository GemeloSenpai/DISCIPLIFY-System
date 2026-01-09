<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl md:text-2xl text-gray-800">
                Boleta de calificaciones — {{ $alumno->nombre_completo }} (#{{ $alumno->id }})
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ url()->previous() ?: route('admin.dashboard') }}"
                    class="text-sm rounded-lg bg-slate-800 text-black px-3 py-1.5 hover:bg-slate-900">← Volver</a>

            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Encabezado simple de boleta (si existe) --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap items-center justify-between gap-3">
                <div class="text-sm text-gray-700">
                    <div><b>Alumno:</b> {{ $alumno->nombre_completo }} <span class="text-gray-500">(#{{ $alumno->id }})</span></div>
                    @if($boleta)
                        <div class="mt-0.5"><b>Boleta:</b> #{{ $boleta->id }} · <b>Emisión:</b> {{ \Illuminate\Support\Carbon::parse($boleta->fecha_emision ?? $boleta->created_at)->format('Y-m-d') }}</div>
                        @if(!empty($boleta->etiqueta))
                            <div class="mt-0.5"><b>Etiqueta:</b> {{ $boleta->etiqueta }}</div>
                        @endif
                        @if(!empty($boleta->observaciones))
                            <div class="mt-0.5"><b>Observaciones:</b> {{ $boleta->observaciones }}</div>
                        @endif
                    @else
                        <div class="mt-0.5 text-gray-500">Sin boleta curada; se muestran notas calculadas si existen.</div>
                    @endif
                </div>

                <div class="text-right">
                    <div class="text-sm">
                        <span class="font-semibold">Promedio:</span>
                        <span class="ml-1">{{ $promedio !== null ? number_format($promedio,2) : '—' }}</span>
                    </div>
                    <div class="mt-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs {{ $clasCls }}">{{ $clasLbl }}</span>
                    </div>
                </div>
            </div>

            {{-- Tabla de cursos / notas --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="overflow-x-auto rounded-xl border border-gray-200">
                    <table class="min-w-[860px] w-full text-sm">
                        <thead class="bg-slate-100 text-slate-800">
                            <tr>
                                <th class="px-3 py-2 text-left">Curso</th>
                                <th class="px-3 py-2 text-center">Nota</th>
                                <th class="px-3 py-2 text-left">Estado</th>
                                <th class="px-3 py-2 text-left">Fuente</th>
                                <th class="px-3 py-2 text-left">Etiqueta</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($filas as $r)
                                <tr>
                                    <td class="px-3 py-2">{{ $r['curso_titulo'] }}</td>
                                    <td class="px-3 py-2 text-center">
                                        @if($r['nota'] === null)
                                            <span class="text-gray-500">—</span>
                                        @else
                                            <span class="font-semibold">{{ number_format($r['nota'], 2) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs {{ $r['estado_cls'] }}">{{ $r['estado_lbl'] }}</span>
                                    </td>
                                    <td class="px-3 py-2">
                                        @if($r['fuente']==='manual')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] bg-amber-100 text-amber-800">Curada</span>
                                        @elseif($r['fuente']==='calculada')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] bg-indigo-100 text-indigo-800">Calculada</span>
                                        @else
                                            <span class="text-gray-400 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2">
                                        {{ $r['etiqueta'] ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <p class="mt-3 text-[11px] text-gray-500">
                    • Si no hay nota para un curso: <b>Por cursar</b> (inscrito sin nota) o <b>No cursado</b> (sin inscripción).<br>
                    • El <b>Promedio</b> solo contabiliza cursos con nota. La clasificación sigue estas reglas: Muy bueno (≥70 y &lt;81), Perfecto (≥81 y &lt;91), Excelencia (≥91), Reprobado (&lt;70).
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
