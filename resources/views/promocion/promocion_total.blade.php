<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl md:text-2xl text-gray-800">
                Promoción total — todos los cursos
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Flash / Validación --}}
            @if(session('status'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3">
                    <strong class="font-semibold">Éxito:</strong> {{ session('status') }}
                </div>
            @endif
            @if(session('warning'))
                <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 text-amber-800 px-4 py-3">
                    <strong class="font-semibold">Aviso:</strong> {{ session('warning') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-4 py-3">
                    <strong class="font-semibold">Error:</strong> {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-4 py-3">
                    <strong class="font-semibold">Validación:</strong>
                    <ul class="list-disc ml-5 mt-2">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl">
                <div class="p-6 space-y-6" x-data="PromoTotalPage()">

                    {{-- ===== FILA SUPERIOR: CONFIG (izq.) + HISTORIAL (der.) ===== --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                        {{-- Configuración compacta --}}
                        <div class="rounded-xl border border-gray-200 p-4">
                            <h3 class="text-base font-semibold text-gray-800">Configuración</h3>

                            <form id="formGlobal" action="{{ $postUrl }}" method="POST" class="mt-3">
                                @csrf
                                <input type="hidden" name="accion" id="accionField" value="simular">
                                <div class="flex items-end gap-3">
                                    <div class="flex-1">
                                        <label class="block text-xs text-gray-600 mb-1">Umbral</label>
                                        <input id="umbral" name="umbral" type="number" step="0.01" min="0" max="100"
                                               value="{{ old('umbral', $umbral) }}"
                                               class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                    <button type="submit" id="btnSimular"
                                            class="rounded-lg bg-amber-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-700 focus:outline-none">
                                        Simular
                                    </button>
                                    <button type="button" id="btnAplicar"
                                            class="rounded-lg bg-emerald-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none">
                                        Aplicar
                                    </button>
                                </div>

                                @if(isset($totales) && !empty($totales))
                                    <div class="mt-3 flex items-center gap-2 flex-wrap">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 text-emerald-700 px-3 py-1 text-xs">
                                            ✅ Aprob: <b>{{ $totales['aprob'] }}</b>
                                        </span>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 text-indigo-700 px-3 py-1 text-xs">
                                            🚚 Movidos: <b>{{ $totales['mov'] }}</b>
                                        </span>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 text-amber-700 px-3 py-1 text-xs">
                                            ⛔ Omit: <b>{{ $totales['omit'] }}</b>
                                        </span>
                                    </div>
                                @endif

                                <p class="mt-2 text-[11px] text-gray-500">
                                    “Simular” no mueve matrículas. “Aplicar” mueve/limpia matrículas y gradúa si corresponde.
                                </p>
                            </form>
                        </div>

                        {{-- === CORRIDAS RECIENTES (Simulaciones / Aplicadas) === --}}
<div class="rounded-xl border border-gray-200 p-4" x-data="CorridasWidget()">
    <div class="flex items-center justify-between gap-2 mb-2">
        <h3 class="text-base font-semibold text-gray-800">Corridas recientes</h3>
        <div class="flex items-center gap-2">
            <select x-model.number="days" class="rounded-lg border px-2 py-1 text-sm">
                <option :value="7">7 días</option>
                <option :value="30">30 días</option>
                <option :value="60">60 días</option>
                <option :value="120">120 días</option>
            </select>
            <button @click="cargar()"
                    class="rounded-lg bg-slate-800 text-white px-3 py-1.5 text-sm hover:bg-slate-900">
                Actualizar
            </button>
        </div>
    </div>

    <template x-if="loading">
        <div class="text-xs text-gray-500">Cargando…</div>
    </template>
    <template x-if="!loading && sims.length===0 && reales.length===0">
        <div class="text-xs text-gray-500">Sin corridas en el período.</div>
    </template>

    {{-- SIMULACIONES --}}
    <div class="mt-3" x-show="sims.length">
        <div class="flex items-center gap-2 mb-2">
            <h4 class="font-semibold text-amber-700">Simulaciones</h4>
            <span class="text-xs px-2 py-0.5 rounded bg-amber-50 text-amber-700" x-text="sims.length + ' corridas'"></span>
        </div>
        <div class="overflow-x-auto rounded-xl border border-gray-200">
            <table class="min-w-[780px] w-full text-sm">
                <thead class="bg-amber-50 text-amber-800">
                <tr>
                    <th class="px-3 py-2 text-left">Fecha</th>
                    <th class="px-3 py-2 text-center">Cursos</th>
                    <th class="px-3 py-2 text-center">Movidos</th>
                    <th class="px-3 py-2 text-center">Aprob</th>
                    <th class="px-3 py-2 text-center">Omit</th>
                    <th class="px-3 py-2 text-right">Acciones</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-amber-100">
                <template x-for="g in sims" :key="g.key">
                    <tr class="bg-amber-50/50">
                        <td class="px-3 py-2" x-text="g.fecha"></td>
                        <td class="px-3 py-2 text-center" x-text="g.cursos"></td>
                        <td class="px-3 py-2 text-center" x-text="g.totales.mov"></td>
                        <td class="px-3 py-2 text-center" x-text="g.totales.aprob"></td>
                        <td class="px-3 py-2 text-center" x-text="g.totales.omit"></td>
                        <td class="px-3 py-2 text-right">
                            <button @click="abrir(g)" class="text-xs rounded-lg bg-amber-600 text-white px-3 py-1.5 hover:bg-amber-700">Ver</button>
                        </td>
                    </tr>
                </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- APLICADAS --}}
    <div class="mt-6" x-show="reales.length">
        <div class="flex items-center gap-2 mb-2">
            <h4 class="font-semibold text-emerald-700">Aplicadas</h4>
            <span class="text-xs px-2 py-0.5 rounded bg-emerald-50 text-emerald-700" x-text="reales.length + ' corridas'"></span>
        </div>
        <div class="overflow-x-auto rounded-xl border border-gray-200">
            <table class="min-w-[780px] w-full text-sm">
                <thead class="bg-emerald-50 text-emerald-800">
                <tr>
                    <th class="px-3 py-2 text-left">Fecha</th>
                    <th class="px-3 py-2 text-center">Cursos</th>
                    <th class="px-3 py-2 text-center">Movidos</th>
                    <th class="px-3 py-2 text-center">Aprob</th>
                    <th class="px-3 py-2 text-center">Omit</th>
                    <th class="px-3 py-2 text-right">Acciones</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-emerald-100">
                <template x-for="g in reales" :key="g.key">
                    <tr class="bg-emerald-50/50">
                        <td class="px-3 py-2" x-text="g.fecha"></td>
                        <td class="px-3 py-2 text-center" x-text="g.cursos"></td>
                        <td class="px-3 py-2 text-center" x-text="g.totales.mov"></td>
                        <td class="px-3 py-2 text-center" x-text="g.totales.aprob"></td>
                        <td class="px-3 py-2 text-center" x-text="g.totales.omit"></td>
                        <td class="px-3 py-2 text-right">
                            <button @click="abrir(g)" class="text-xs rounded-lg bg-emerald-600 text-white px-3 py-1.5 hover:bg-emerald-700">Ver</button>
                        </td>
                    </tr>
                </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal con promociones de la corrida --}}
    <div class="fixed inset-0 z-50" x-show="modal.open" style="display:none; background: rgba(0,0,0,.45);">
        <div class="absolute inset-0 flex items-start justify-center p-4">
            <div class="w-full max-w-4xl bg-white rounded-xl shadow-xl overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 border-b">
                    <div>
                        <h3 class="font-semibold text-gray-900" x-text="modal.title"></h3>
                        <p class="text-xs text-gray-500" x-text="modal.subtitle"></p>
                    </div>
                    <button @click="modal.open=false" class="rounded-lg bg-gray-100 px-3 py-1.5 text-sm hover:bg-gray-200">Cerrar</button>
                </div>
                <div class="p-4">
                    <div class="overflow-x-auto rounded-xl border border-gray-200">
                        <table class="min-w-[900px] w-full text-sm">
                            <thead class="bg-slate-100 text-slate-800">
                            <tr>
                                <th class="px-3 py-2 text-left">Promoción</th>
                                <th class="px-3 py-2 text-left">Origen → Destino</th>
                                <th class="px-3 py-2 text-center">Modo</th>
                                <th class="px-3 py-2 text-center">Movidos</th>
                                <th class="px-3 py-2 text-center">Aprob</th>
                                <th class="px-3 py-2 text-center">Omit</th>
                                <th class="px-3 py-2 text-right">Abrir</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                            <template x-for="p in modal.items" :key="p.prom_id">
                                <tr>
                                    <td class="px-3 py-2">#<span x-text="p.prom_id"></span></td>
                                    <td class="px-3 py-2">
                                        <span x-text="p.origen"></span>
                                        <span x-show="p.destino"> → <span x-text="p.destino"></span></span>
                                        <span x-show="!p.destino"> → <b>Graduación</b></span>
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px]"
                                              :class="p.mover ? 'bg-emerald-600 text-white' : 'bg-amber-500 text-white'">
                                            <span x-text="(p.modo||'').toUpperCase() + ' · ' + (p.mover ? 'APLICADO':'SIMULADO')"></span>
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 text-center" x-text="p.total_movidos"></td>
                                    <td class="px-3 py-2 text-center" x-text="p.total_aprobados"></td>
                                    <td class="px-3 py-2 text-center" x-text="p.total_omitidos"></td>
                                    <td class="px-3 py-2 text-right">
                                        <button @click="$dispatch('abrir-prom', { id: p.prom_id }); modal.open=false;"
                                                class="text-xs rounded-lg bg-indigo-600 text-white px-3 py-1.5 hover:bg-indigo-700">
                                            Ver registros
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

                    </div>

                    {{-- ===== ÍNDICE DE MOVIMIENTOS (corrida actual) ===== --}}
                    @if(($bloques ?? collect())->count())
                        <div class="rounded-xl border border-gray-200 p-4" 
                             x-data="{ expandAll(){ document.querySelectorAll('[data-prom-panel]').forEach(el=>el.__open(true)); }, 
                                       collapseAll(){ document.querySelectorAll('[data-prom-panel]').forEach(el=>el.__open(false)); } }">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-base font-semibold text-gray-800">Índice de movimientos (corrida actual)</h3>
                                <div class="flex gap-2">
                                    <button @click="expandAll()"   class="rounded-lg bg-slate-100 px-3 py-1.5 text-sm hover:bg-slate-200">Expandir todos</button>
                                    <button @click="collapseAll()" class="rounded-lg bg-slate-100 px-3 py-1.5 text-sm hover:bg-slate-200">Contraer todos</button>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($bloques as $b)
                                    @php $r = $b->resumen; @endphp
                                    <button
                                        @click.window="$dispatch('abrir-prom', { id: {{ $r->id }} })"
                                        class="rounded-lg border px-3 py-1.5 text-xs hover:bg-slate-50"
                                        onclick="window.dispatchEvent(new CustomEvent('abrir-prom',{detail:{id:{{ $r->id }}}}))">
                                        #{{ $r->id }} —
                                        {{ $r->curso_origen_titulo ?? ('ID '.$r->curso_origen_id) }}
                                        @if($r->curso_destino_id)
                                            → {{ $r->curso_destino_titulo ?? ('ID '.$r->curso_destino_id) }}
                                        @else
                                            → Graduación
                                        @endif
                                        <span class="ml-1 inline-flex items-center rounded-full px-2 py-0.5 text-[11px]
                                            {{ $r->mover ? 'bg-emerald-600 text-white':'bg-amber-500 text-white' }}">
                                            {{ $r->mover ? 'APLICADO' : 'SIMULADO' }}
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- ===== RESULTADOS POR CURSO (acordeones) ===== --}}
                    <div class="space-y-4" x-data @abrir-prom.window="
                        const id = $event.detail.id;
                        const panel = document.querySelector(`[data-prom-panel='${id}']`);
                        if (panel && panel.__open) { panel.__open(true); panel.scrollIntoView({behavior:'smooth', block:'start'}); }
                    ">
                        @forelse($bloques as $b)
                            @php $r = $b->resumen; $detalle = $b->detalle; @endphp
                            <div id="prom-{{ $r->id }}" class="rounded-xl border border-gray-200">
                                {{-- Header acordeón --}}
                                <div class="flex items-center justify-between px-4 py-3 cursor-pointer select-none"
                                     x-data
                                     x-init="
                                        const root = $el.closest('[id^=prom-]');
                                        const body = root.querySelector('[data-prom-body]');
                                        root.setAttribute('data-prom-panel','{{ $r->id }}');
                                        root.__open = (val)=>{ body.style.display = val ? '' : 'none'; root.classList.toggle('ring-2', val); };
                                        root.__open(false);
                                        $el.addEventListener('click', ()=> root.__open(body.style.display==='none'));
                                     ">
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900">
                                            #{{ $r->id }} — {{ strtoupper($r->modo) }}
                                            <span class="ml-2 inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium
                                                {{ $r->mover ? 'bg-emerald-600 text-white':'bg-amber-500 text-white' }}">
                                                {{ $r->mover ? 'APLICADO' : 'SIMULADO' }}
                                            </span>
                                        </h3>
                                        <div class="text-xs text-gray-600 mt-1">
                                            <span class="font-medium">Fecha:</span> {{ \Illuminate\Support\Carbon::parse($r->created_at)->format('Y-m-d H:i') }}
                                            &middot; Origen: <b>{{ $r->curso_origen_titulo ?? ('ID '.$r->curso_origen_id) }}</b>
                                            @if($r->curso_destino_id)
                                                → <b>{{ $r->curso_destino_titulo ?? ('ID '.$r->curso_destino_id) }}</b>
                                            @else
                                                → <b>Graduación</b>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 text-emerald-700 px-3 py-1 text-xs">
                                            ✅ Aprob: <b>{{ $r->total_aprobados }}</b>
                                        </span>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 text-indigo-700 px-3 py-1 text-xs">
                                            🚚 Movidos: <b>{{ $r->total_movidos }}</b>
                                        </span>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 text-amber-700 px-3 py-1 text-xs">
                                            ⛔ Omit: <b>{{ $r->total_omitidos }}</b>
                                        </span>
                                    </div>
                                </div>

                                {{-- Body acordeón (tabla detalle) --}}
                                <div data-prom-body class="px-4 pb-4" style="display:none">
                                    <div class="mt-3 overflow-x-auto rounded-xl border border-gray-200">
                                        <table class="min-w-[880px] w-full text-sm text-gray-800">
                                            <thead class="bg-indigo-600 text-white sticky top-0 z-10">
                                            <tr>
                                                <th class="px-4 py-3 text-left">#</th>
                                                <th class="px-4 py-3 text-left">ALUMNO</th>
                                                <th class="px-4 py-3 text-center">NOTA</th>
                                                <th class="px-4 py-3 text-center">UMBRAL</th>
                                                <th class="px-4 py-3 text-left">ESTADO</th>
                                                <th class="px-4 py-3 text-left">MENSAJE</th>
                                            </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-100">
                                            @forelse($detalle as $i => $row)
                                                @php
                                                    $badge = [
                                                        'movido' => 'bg-emerald-100 text-emerald-700',
                                                        'ya_en_destino' => 'bg-blue-100 text-blue-700',
                                                        'omitido' => 'bg-amber-100 text-amber-700',
                                                        'error' => 'bg-rose-100 text-rose-700',
                                                    ][$row->estado] ?? 'bg-gray-100 text-gray-700';
                                                @endphp
                                                <tr>
                                                    <td class="px-4 py-2">{{ $i + 1 }}</td>
                                                    <td class="px-4 py-2 font-medium text-gray-900">
                                                        {{ $row->nombre_completo ?? '—' }}
                                                        <span class="text-xs text-gray-500">(#{{ $row->alumno_id }})</span>
                                                    </td>
                                                    <td class="px-4 py-2 text-center">{{ number_format($row->nota_total ?? 0, 2) }}</td>
                                                    <td class="px-4 py-2 text-center">{{ number_format($row->umbral ?? 0, 2) }}</td>
                                                    <td class="px-4 py-2">
                                                        <span class="inline-block px-2 py-0.5 rounded text-xs {{ $badge }}">
                                                            {{ ['movido'=>'Movido','ya_en_destino'=>'Ya en destino','omitido'=>'Omitido','error'=>'Error'][$row->estado] ?? $row->estado }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-2 text-gray-700">{{ $row->mensaje }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">Sin detalle.</td>
                                                </tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-lg border border-slate-200 bg-slate-50 text-slate-700 px-4 py-3">
                                Ejecuta una <b>Simulación</b> o <b>Aplicación</b> para ver resultados por curso.
                            </div>
                        @endforelse
                    </div>

                    <p class="text-xs text-gray-500">
                        * Usa el <b>Índice de movimientos</b> para abrir rápido cada curso de la corrida. En <b>Historial</b> puedes buscar por fecha y abrir corridas anteriores.
                    </p>
                </div>
            </div>
        </div>

        @push('scripts')
        <script>
            // ===== Botonera Simular/Aplicar =====
            function PromoTotalPage(){
                const form = document.getElementById('formGlobal');
                const accionFld = document.getElementById('accionField');
                const btnSimular = document.getElementById('btnSimular');
                const btnAplicar = document.getElementById('btnAplicar');

                btnSimular?.addEventListener('click', () => { accionFld.value = 'simular'; });
                btnAplicar?.addEventListener('click', (e) => {
                    e.preventDefault();

                    btnAplicar.disabled = true;           // 👈
                    btnAplicar.textContent = 'Aplicando…';// opcional
                    
                    const umbral = document.getElementById('umbral')?.value || '70';
                    if (!confirm(`¿Aplicar promoción total para TODOS los cursos?\n\nUmbral: ${umbral}\n\nEsto moverá/limpiará matrículas y puede graduar alumnos.`)) return;
                    accionFld.value = 'aplicar';
                    form.submit();
                });
                return {};
            }

            // ===== Historial separado (Simulaciones vs Aplicadas) =====
            function HistorialSplit(){
                return {
                    f: {
                        desde: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().slice(0,10),
                        hasta: new Date().toISOString().slice(0,10),
                    },
                    loading:false,
                    sims: [],
                    reales: [],
                    modal: { open:false, loading:false, title:'', subtitle:'', items:[] },

                    async buscar(){
                        this.loading = true;
                        this.sims = [];
                        this.reales = [];
                        try{
                            const params = new URLSearchParams({ desde: this.f.desde || '', hasta: this.f.hasta || '' });
                            const res = await fetch('{{ route('admin.promocion.historial.fecha') }}?'+params.toString(), {
                                headers: {'Accept':'application/json'}
                            });
                            const data = await res.json();
                            const rows = Array.isArray(data) ? data : [];

                            // Normaliza mínimos y arma runKey por minuto + mover
                            for(const r of rows){
                                r._fecha = (r.fecha || r.created_at || '').substring(0,16); // yyyy-mm-dd hh:mm
                                r._origen = r.origen || r.curso_origen_titulo || (r.curso_origen_id ? 'ID '+r.curso_origen_id : '');
                                r._destino = (r.destino !== undefined) ? r.destino
                                           : (r.curso_destino_id ? (r.curso_destino_titulo || 'ID '+r.curso_destino_id) : null);
                                r._mov   = Number(r.total_movidos || 0);
                                r._aprob = Number(r.total_aprobados || 0);
                                r._omit  = Number(r.total_omitidos || 0);
                                r._mover = Number(r.mover || 0);
                                r._modo  = r.modo || '';
                                r._prom  = r.prom_id || r.id;
                                r._runKey = `${r._mover}-${r._fecha}`;
                            }

                            const map = {};
                            for(const r of rows){
                                if(!map[r._runKey]){
                                    map[r._runKey] = {
                                        key: r._runKey, mover: r._mover, fecha: r._fecha,
                                        cursos: 0, totales: { mov:0, aprob:0, omit:0 }, items: []
                                    };
                                }
                                map[r._runKey].cursos += 1;
                                map[r._runKey].totales.mov   += r._mov;
                                map[r._runKey].totales.aprob += r._aprob;
                                map[r._runKey].totales.omit  += r._omit;
                                map[r._runKey].items.push({
                                    prom_id: r._prom,
                                    origen: r._origen,
                                    destino: r._destino,
                                    mover: r._mover,
                                    modo: r._modo,
                                    total_movidos: r._mov,
                                    total_aprobados: r._aprob,
                                    total_omitidos: r._omit,
                                });
                            }

                            const all = Object.values(map).sort((a,b)=> (a.fecha<b.fecha?1:-1));
                            this.sims   = all.filter(g => g.mover===0);
                            this.reales = all.filter(g => g.mover===1);

                        }catch(e){
                            alert('No se pudo cargar el historial: '+(e?.message||e));
                        }finally{
                            this.loading = false;
                        }
                    },

                    abrirGrupo(tipo, runKey){
                        const col = (tipo==='sim') ? this.sims : this.reales;
                        const g = col.find(x => x.key===runKey);
                        if(!g){ return; }
                        this.modal.open = true;
                        this.modal.loading = false;
                        this.modal.title = (tipo==='sim' ? 'Corrida de simulación' : 'Corrida aplicada');
                        this.modal.subtitle = `${g.fecha} · Cursos: ${g.cursos} · Movidos: ${g.totales.mov} · Aprob: ${g.totales.aprob} · Omit: ${g.totales.omit}`;
                        this.modal.items = g.items;
                    }
                }
            }

            function CorridasWidget(){
    return {
        days: 60,
        loading: false,
        sims: [],
        reales: [],
        modal: { open:false, title:'', subtitle:'', items:[] },
        async cargar(){
            this.loading = true; this.sims=[]; this.reales=[];
            try{
                const url = `{{ route('admin.promocion.corridas') }}?days=${this.days}&limit=300`;
                const res = await fetch(url, { headers:{'Accept':'application/json'} });
                const all = await res.json();
                this.sims   = (all||[]).filter(x => Number(x.mover)===0);
                this.reales = (all||[]).filter(x => Number(x.mover)===1);
            }catch(e){
                alert('No se pudo cargar el historial de corridas: '+(e?.message||e));
            }finally{
                this.loading = false;
            }
        },
        abrir(g){
            this.modal.open = true;
            this.modal.title = (g.mover ? 'Corrida aplicada' : 'Corrida de simulación');
            this.modal.subtitle = `${g.fecha} · Cursos: ${g.cursos} · Movidos: ${g.totales.mov} · Aprob: ${g.totales.aprob} · Omit: ${g.totales.omit}`;
            this.modal.items = g.items || [];
        },
        init(){ this.cargar(); }
    }
}
            
        </script>
        @endpush
    </div>
</x-app-layout>
