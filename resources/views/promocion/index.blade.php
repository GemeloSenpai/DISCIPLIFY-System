<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl md:text-2xl text-gray-800">
                Promoción de alumnos — {{ $curso->titulo }}
            </h2>
        </div>
    </x-slot>

    @php
        $rows   = collect($rows)->unique('id')->values();
        $total  = $rows->count();
        $aprob  = $rows->where('ok', true)->count();
        $reprob = $rows->where('ok', false)->count();
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl">
                <div class="p-6 space-y-6">

                    {{-- Flash / Validación --}}
                    @if(session('status'))
                        <div class="rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3">
                            <strong class="font-semibold">Éxito:</strong> {{ session('status') }}
                        </div>
                    @endif
                    @if(session('warning'))
                        <div class="rounded-lg border border-amber-200 bg-amber-50 text-amber-800 px-4 py-3">
                            <strong class="font-semibold">Aviso:</strong> {{ session('warning') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-4 py-3">
                            <strong class="font-semibold">Error:</strong> {{ session('error') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-4 py-3">
                            <strong class="font-semibold">Validación:</strong>
                            <ul class="list-disc ml-5 mt-2">
                                @foreach ($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- ========= FORM ========= --}}
                    <form id="form-promocion" method="POST" action="{{ $postUrl }}">
                        @csrf

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                            <div class="lg:col-span-2 space-y-4">
                                <div class="rounded-xl border border-gray-200 p-4">
                                    <h3 class="text-base font-semibold text-gray-800 mb-2">Curso origen</h3>
                                    <div class="flex items-center justify-between flex-wrap gap-2">
                                        <div>
                                            <div class="text-xs text-gray-500">ID: {{ $curso->id_cursos }}</div>
                                            <div class="text-lg font-medium text-gray-900">{{ $curso->titulo }}</div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                Umbral aprobación: <b>{{ $umbral }}</b> puntos.
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 text-indigo-700 px-3 py-1 text-xs">
                                                👥 Total: <b>{{ $total }}</b>
                                            </span>
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 text-emerald-700 px-3 py-1 text-xs">
                                                ✅ Aprob: <b id="chipAprob">{{ $aprob }}</b>
                                            </span>
                                            <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 text-rose-700 px-3 py-1 text-xs">
                                                ⛔ Reprob: <b id="chipReprob">{{ $reprob }}</b>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Filtros rápidos --}}
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                                    <div class="md:col-span-2">
                                        <div class="flex items-stretch gap-2">
                                            <div class="relative flex-1 min-w-0">
                                                <input id="buscar" type="text" placeholder="Buscar por nombre…"
                                                       class="w-full rounded-lg border pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <button id="btnBuscar" type="button"
                                                        class="inline-flex items-center rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                                    Buscar
                                                </button>
                                                <button id="btnLimpiar" type="button"
                                                        class="inline-flex items-center rounded-lg bg-gray-200 px-3.5 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2">
                                                    Limpiar
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <label class="inline-flex items-center gap-2 text-sm">
                                            <input id="solo-aprob" type="checkbox" class="rounded border-gray-300">
                                            <span>Solo aprobados</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- Configuración --}}
                            <div class="rounded-xl border border-gray-200 p-4">
                                <h3 class="text-base font-semibold text-gray-800 mb-2">Configuración</h3>

                                <label class="block text-sm font-medium">Curso destino</label>
                                <select name="curso_destino_id"
                                        class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        required>
                                    <option value="">— Selecciona —</option>
                                    @foreach($cursosDestino as $c)
                                        @if($c->id_cursos != $curso->id_cursos)
                                            <option value="{{ $c->id_cursos }}">{{ $c->titulo }}</option>
                                        @endif
                                    @endforeach
                                </select>

                                <label class="mt-1 inline-flex items-start gap-2 text-sm">
                                    <input type="checkbox" name="mover" value="1" class="mt-1 rounded border-gray-300" checked>
                                    <span>Quitar del curso origen tras promover (traslado real)</span>
                                </label>
                            </div>
                        </div>

                        {{-- Acciones --}}
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-4">
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input id="check-all" type="checkbox" class="rounded border-gray-300">
                                    <span>Seleccionar todos (según filtros)</span>
                                </label>

                                <span class="inline-flex items-center gap-2 rounded-full bg-slate-50 text-slate-700 px-3 py-1 text-xs">
                                    Seleccionados: <b id="chipSel">0</b>
                                </span>
                            </div>

                            <button id="btn-promocionar" type="submit"
                                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                🚀 Promocionar seleccionados
                            </button>
                        </div>

                        {{-- Cards móvil --}}
                        <div id="cards" class="md:hidden space-y-3">
                            @forelse($rows as $r)
                                <div class="rounded-xl border border-gray-200 p-4 flex items-start gap-3" data-row
                                     data-nombre="{{ Str::lower($r['nombre']) }}" data-aprobado="{{ $r['ok'] ? '1' : '0' }}">
                                    <input type="checkbox" name="alumnos[]" class="al-check mt-1 rounded border-gray-300"
                                           value="{{ $r['id'] }}" {{ $r['ok'] ? 'checked' : '' }}>
                                    <div class="grow min-w-0">
                                        <div class="flex items-center justify-between gap-2">
                                            <h4 class="font-medium text-gray-900 truncate">{{ $r['nombre'] }}</h4>
                                            <span class="shrink-0 inline-block px-2 py-0.5 rounded text-xs {{ $r['ok'] ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                                {{ $r['ok'] ? 'Aprobado' : 'Reprobado' }}
                                            </span>
                                        </div>
                                        <div class="mt-1 text-sm text-gray-600">
                                            Total: <b>{{ number_format($r['total'], 2) }}</b>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-gray-500">Sin alumnos.</div>
                            @endforelse
                        </div>

                        {{-- Tabla desktop --}}
                        <div class="hidden md:block">
                            <div class="overflow-x-auto rounded-xl border border-gray-200">
                                <table class="min-w-[720px] w-full text-sm text-gray-800">
                                    <thead class="bg-indigo-600 text-white sticky top-0 z-10">
                                        <tr>
                                            <th class="px-3 py-3 text-center">SEL</th>
                                            <th class="px-4 py-3 text-left">ALUMNO</th>
                                            <th class="px-4 py-3 text-center">TOTAL</th>
                                            <th class="px-4 py-3 text-center">ESTADO</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody" class="bg-white divide-y divide-gray-100">
                                        @forelse($rows as $r)
                                            <tr data-row data-nombre="{{ Str::lower($r['nombre']) }}" data-aprobado="{{ $r['ok'] ? '1' : '0' }}">
                                                <td class="px-3 py-2 text-center">
                                                    <input type="checkbox" name="alumnos[]" class="al-check rounded border-gray-300"
                                                           value="{{ $r['id'] }}" {{ $r['ok'] ? 'checked' : '' }}>
                                                </td>
                                                <td class="px-4 py-2 font-medium text-gray-900">{{ $r['nombre'] }}</td>
                                                <td class="px-4 py-2 text-center">{{ number_format($r['total'], 2) }}</td>
                                                <td class="px-4 py-2 text-center">
                                                    <span class="inline-block px-2 py-0.5 rounded text-xs {{ $r['ok'] ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                                        {{ $r['ok'] ? 'Aprobado' : 'Reprobado' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-4 py-6 text-center text-gray-500">Sin alumnos.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <p class="text-xs text-gray-500 mt-2">
                            * Si marcas “Quitar del curso origen tras promover”, el alumno será trasladado al curso destino.
                            Si no, quedará inscrito en ambos cursos.
                        </p>
                    </form>
                    {{-- ========= /FORM ========= --}}
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    const form      = document.getElementById('form-promocion');
    const buscar    = document.getElementById('buscar');
    const soloAprob = document.getElementById('solo-aprob');
    const chipSel   = document.getElementById('chipSel');
    const cardsWrap = document.getElementById('cards');
    const tbody     = document.getElementById('tbody');

    const $  = (s, el=document)=> el.querySelector(s);
    const $$ = (s, el=document)=> Array.from(el.querySelectorAll(s));

    function isVisibleDeep(el){
        let n = el;
        while(n && n!==document.body){
            const cs = getComputedStyle(n);
            if (cs.display==='none' || cs.visibility==='hidden' || n.classList.contains('hidden')) return false;
            n = n.parentElement;
        }
        return true;
    }
    function visibleChecks(){
        return $$('.al-check').filter(c=>{
            const row = c.closest('[data-row]');
            return row && !row.classList.contains('hidden') && isVisibleDeep(row);
        });
    }
    function uniqueValues(nodes){
        const set = new Set();
        nodes.forEach(n => set.add(String(n.value)));
        return Array.from(set);
    }
    function visibleSelectedValues(){
        return uniqueValues(visibleChecks().filter(c=>c.checked));
    }
    function allSelectedUnique(){
        return uniqueValues($$('.al-check').filter(c=>c.checked));
    }

    function applyFilters(){
        const q = (buscar?.value || '').trim().toLowerCase();
        const onlyOk = !!soloAprob?.checked;

        $$('.al-check').forEach(c=>{
            const row = c.closest('[data-row]');
            const nombre = row?.dataset.nombre || '';
            const ok = row?.dataset.aprobado === '1';
            const show = (!q || nombre.includes(q)) && (!onlyOk || ok);
            row?.classList.toggle('hidden', !show);
        });

        updateSelectedChip();
    }

    function updateSelectedChip(){
        chipSel.textContent = visibleSelectedValues().length;
    }

    // Filtros
    const debounce = (fn, t=250)=>{ let h; return (...a)=>{ clearTimeout(h); h=setTimeout(()=>fn(...a), t); }; };
    document.getElementById('btnBuscar')?.addEventListener('click', applyFilters);
    buscar?.addEventListener('input', debounce(applyFilters, 250));
    soloAprob?.addEventListener('change', applyFilters);
    document.getElementById('btnLimpiar')?.addEventListener('click', ()=>{
        if (buscar) buscar.value='';
        applyFilters(); buscar?.focus();
    });

    // Seleccionar todos (solo visibles)
    document.getElementById('check-all')?.addEventListener('change', (e)=>{
        visibleChecks().forEach(c=>{ c.checked = e.target.checked; });
        updateSelectedChip();
    });

    // Recalcular chip al marcar filas
    document.addEventListener('change', (e)=>{
        if (e.target.classList.contains('al-check')) updateSelectedChip();
    });

    // Validar y normalizar envío
    form?.addEventListener('submit', (e)=>{
        const destino = form.querySelector('select[name="curso_destino_id"]');
        if (!destino?.value) { e.preventDefault(); alert('Selecciona un curso destino'); destino?.focus(); return; }

        const values = allSelectedUnique();
        if (values.length === 0){ e.preventDefault(); alert('No seleccionaste alumnos'); return; }
        if (!confirm(`¿Promover ${values.length} alumno(s) al curso seleccionado?`)) {
            e.preventDefault(); return;
        }

        // Evitar duplicados por mobile/desktop:
        // 1) Deshabilita todos los checkboxes (no se enviarán).
        $$('.al-check').forEach(c=>{ c.disabled = true; });

        // 2) Inserta inputs ocultos únicos
        form.querySelectorAll('input[name="alumnos[]"]').forEach(n=>n.remove());
        values.forEach(v=>{
            const i = document.createElement('input');
            i.type = 'hidden'; i.name = 'alumnos[]'; i.value = v;
            form.appendChild(i);
        });
    });

    // Init
    applyFilters();
    updateSelectedChip();
    </script>
    @endpush
</x-app-layout>
