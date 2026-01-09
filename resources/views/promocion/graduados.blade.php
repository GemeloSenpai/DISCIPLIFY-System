<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl md:text-2xl text-gray-800">
                Graduados — por promoción
            </h2>
            <a href="{{ route('admin.promocion.total') }}"
               class="text-sm rounded-lg bg-slate-800 text-white px-3 py-1.5 hover:bg-slate-900">← Volver a Promoción total</a>
        </div>
    </x-slot>

    <div class="py-6" x-data="GraduadosPage()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Filtro: SOLO por promoción --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                    <div class="md:col-span-2">
                        <label class="block text-xs text-gray-600 mb-1">Promoción</label>
                        <select name="prom_id"
                                class="w-full rounded-lg border px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="">— Selecciona una promoción —</option>
                            @foreach($promos as $p)
                                @php
                                    $label = "#{$p->id} · ".\Illuminate\Support\Carbon::parse($p->created_at)->format('Y-m-d H:i');
                                    if ($p->curso) { $label .= " · $p->curso"; }
                                    $label .= " · movidos: {$p->total_movidos}";
                                @endphp
                                <option value="{{ $p->id }}" @selected(($prom_id ?? null) == $p->id)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <button class="rounded-lg bg-slate-800 text-white px-4 py-2 text-sm hover:bg-slate-900 w-full">
                            Buscar
                        </button>
                    </div>
                </form>
            </div>

            {{-- Tabla --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="overflow-x-auto rounded-xl border border-gray-200">
                    <table class="min-w-[980px] w-full text-sm">
                        <thead class="bg-slate-100 text-slate-800">
                        <tr>
                            <th class="px-3 py-2 text-left">#</th>
                            <th class="px-3 py-2 text-left">Alumno</th>
                            <th class="px-3 py-2 text-center">ID</th>
                            <th class="px-3 py-2 text-center">Año</th>
                            <th class="px-3 py-2 text-center">Promedio total</th>
                            <th class="px-3 py-2 text-left">Estado</th>
                            <th class="px-3 py-2 text-left">Curso final</th>
                            <th class="px-3 py-2 text-left">Fecha</th>
                            <th class="px-3 py-2 text-right">Acciones</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                        @forelse($graduados as $i => $g)
                            @php
                                $pt = (float)($g->promedio_total ?? 0);
                                if ($pt >= 91) {
                                    $estadoLbl = 'Excelencia';
                                    $estadoCls = 'bg-emerald-100 text-emerald-700';
                                } elseif ($pt >= 81) {
                                    $estadoLbl = 'Perfecto';
                                    $estadoCls = 'bg-blue-100 text-blue-700';
                                } elseif ($pt >= 70) {
                                    $estadoLbl = 'Muy bueno';
                                    $estadoCls = 'bg-indigo-100 text-indigo-700';
                                } else {
                                    $estadoLbl = '—';
                                    $estadoCls = 'bg-gray-100 text-gray-700';
                                }
                            @endphp
                            <tr>
                                <td class="px-3 py-2">{{ $graduados->firstItem() + $i }}</td>
                                <td class="px-3 py-2">
                                    <span class="font-medium">{{ $g->nombre_completo }}</span>
                                </td>
                                <td class="px-3 py-2 text-center">#{{ $g->alumno_id }}</td>
                                <td class="px-3 py-2 text-center">{{ $g->anio }}</td>
                                <td class="px-3 py-2 text-center font-semibold {{ $pt>=91 ? 'text-emerald-700' : 'text-slate-700' }}">
                                    {{ number_format($pt, 2) }}
                                </td>
                                <td class="px-3 py-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs {{ $estadoCls }}">{{ $estadoLbl }}</span>
                                </td>
                                <td class="px-3 py-2">{{ $g->curso_origen_titulo ?? ('ID '.$g->curso_origen_id) }}</td>
                                <td class="px-3 py-2">{{ \Illuminate\Support\Carbon::parse($g->fecha_graduacion)->format('Y-m-d') }}</td>
                                <td class="px-3 py-2 text-right">
                                    <button @click="verNotas({{ $g->alumno_id }}, `{{ addslashes($g->nombre_completo) }}`)"
                                            class="text-xs rounded-lg bg-slate-800 text-white px-3 py-1.5 hover:bg-slate-900">
                                        Ver
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-3 py-6 text-center text-gray-500" colspan="9">
                                    @if(!$prom_id)
                                        Selecciona una promoción para ver sus graduados.
                                    @else
                                        Sin registros para esta promoción.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $graduados->appends(request()->query())->links() }}
                </div>
            </div>

            <p class="text-xs text-gray-500">
                * “Promedio total” = promedio de la nota final por curso del alumno. Clasificación:
                <b>Muy bueno</b> (≥70 y &lt;81), <b>Perfecto</b> (≥81 y &lt;91), <b>Excelencia</b> (≥91).
            </p>
        </div>

        {{-- Modal Detalle Notas por Alumno --}}
        <div class="fixed inset-0 z-50" x-show="modal.open" style="display:none; background: rgba(0,0,0,.45);">
            <div class="absolute inset-0 flex items-start justify-center p-4">
                <div class="w-full max-w-3xl bg-white rounded-xl shadow-xl overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-3 border-b">
                        <div>
                            <h3 class="font-semibold text-gray-900">Detalle de notas por curso</h3>
                            <p class="text-xs text-gray-500" x-text="modal.title"></p>
                        </div>
                        <button @click="modal.open=false" class="rounded-lg bg-gray-100 px-3 py-1.5 text-sm hover:bg-gray-200">Cerrar</button>
                    </div>
                    <div class="p-4">
                        <template x-if="modal.loading">
                            <div class="text-sm text-gray-500">Cargando…</div>
                        </template>
                        <template x-if="!modal.loading">
                            <div class="space-y-3">
                                <div class="overflow-x-auto rounded-xl border border-gray-200">
                                    <table class="min-w-[680px] w-full text-sm">
                                        <thead class="bg-slate-100 text-slate-800">
                                        <tr>
                                            <th class="px-3 py-2 text-left">Curso</th>
                                            <th class="px-3 py-2 text-center">Nota final</th>
                                        </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                        <template x-for="r in modal.rows" :key="r.id_cursos">
                                            <tr>
                                                <td class="px-3 py-2" x-text="r.curso_titulo ?? ('ID '+r.id_cursos)"></td>
                                                <td class="px-3 py-2 text-center" x-text="Number(r.nota_total||0).toFixed(2)"></td>
                                            </tr>
                                        </template>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-sm">
                                    <span class="font-semibold">Promedio total:</span>
                                    <span x-text="Number(modal.promedio||0).toFixed(2)"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function GraduadosPage(){
            return {
                modal: { open:false, loading:false, title:'', rows:[], promedio:0 },
                async verNotas(alumnoId, nombre){
                    this.modal.open = true;
                    this.modal.loading = true;
                    this.modal.title = `Alumno: ${nombre} (#${alumnoId})`;
                    this.modal.rows = [];
                    this.modal.promedio = 0;
                    try{
                        const res = await fetch(`{{ route('admin.promocion.graduados.detalle') }}?alumno_id=${alumnoId}`, {
                            headers: {'Accept':'application/json'}
                        });
                        const json = await res.json();
                        this.modal.rows = json.cursos || [];
                        this.modal.promedio = json.promedio_total || 0;
                    }catch(e){
                        alert('No se pudo cargar el detalle: '+(e?.message||e));
                    }finally{
                        this.modal.loading = false;
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
