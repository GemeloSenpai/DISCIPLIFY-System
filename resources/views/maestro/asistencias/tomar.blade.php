{{-- resources/views/maestro/asistencias/tomar.blade.php --}}
<x-app-layout>
    @php
        // A prueba de balas: si no viene del controlador, lo tomamos del query ?view=1
        $readonly = ($readonly ?? null) !== null ? (bool)$readonly : request()->boolean('view');
    @endphp

    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl md:text-2xl">
                Asistencia — {{ $curso->titulo }} — {{ \Carbon\Carbon::parse($sesion->fecha)->isoFormat('DD/MM/YYYY') }}
            </h2>

            <div class="flex items-center gap-2">
                @if($readonly)
                    {{-- EDITAR --}}
                    <a href="{{ route('maestro.sesiones.show', [$curso->id_cursos, $sesion->id_sesiones]) }}"
                       class="inline-flex items-center gap-2 rounded-lg border px-3.5 py-2 text-sm font-medium hover:bg-gray-50"
                       title="Editar" aria-label="Editar">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M3 14.25V17h2.75l8.1-8.1-2.75-2.75L3 14.25Zm10.6-9.1 2.25 2.25a.75.75 0 0 0 0-1.06L14.66 4.1a.75.75 0 0 0-1.06 0Z"/>
                        </svg>
                        <span class="hidden sm:inline">Editar</span>
                    </a>
                @else
                    {{-- VER (solo lectura) --}}
                    <a href="{{ route('maestro.sesiones.show', [$curso->id_cursos, $sesion->id_sesiones, 'view' => 1]) }}"
                       class="inline-flex items-center gap-2 rounded-lg border px-3.5 py-2 text-sm font-medium hover:bg-gray-50"
                       title="Ver lista" aria-label="Ver lista">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 3.5c4.167 0 7.5 2.917 9 6.5-1.5 3.583-4.833 6.5-9 6.5S2.5 13.583 1 10C2.5 6.417 5.833 3.5 10 3.5Zm0 2c-2.9 0-5.417 1.9-6.75 4.5 1.333 2.6 3.85 4.5 6.75 4.5s5.417-1.9 6.75-4.5C15.417 7.4 12.9 5.5 10 5.5Zm0 1.75A2.75 2.75 0 1 1 7.25 10 2.75 2.75 0 0 1 10 7.25Z"/>
                        </svg>
                        <span class="hidden sm:inline">Ver lista</span>
                    </a>

                    {{-- Pasar lista (informativo) --}}
                    <button type="button"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
                            x-data
                            @click="window.twinsNotify?.({ title: 'Asistencia tomada', icon: 'success' })"
                            title="Pasar lista" aria-label="Pasar lista">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M16.5 3.75a.75.75 0 0 1 .75.75v11a.75.75 0 0 1-.75.75h-13a.75.75 0 0 1-.75-.75v-11a.75.75 0 0 1 .75-.75h13ZM6.5 2a.75.75 0 0 1 .75.75V3.5h5.5v-.75a.75.75 0 0 1 1.5 0V3.5h1.75A2.25 2.25 0 0 1 18.25 5v11A2.25 2.25 0 0 1 16 18.25H4A2.25 2.25 0 0 1 1.75 16V5A2.25 2.25 0 0 1 4 2.75H5.75V2.5A.75.75 0 0 1 6.5 2Zm7.03 6.28a.75.75 0 0 0-1.06-1.06L9 10.69 7.53 9.22a.75.75 0 0 0-1.06 1.06L8.47 12.28a.75.75 0 0 0 1.06 0l4-4Z"/>
                        </svg>
                        <span class="hidden sm:inline">Pasar lista</span>
                    </button>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto p-4 space-y-4">
        {{-- Banners informativos (flash) --}}
        @php $ins = (int) session('insertados', 0); @endphp

        @if(session('created'))
            <div class="p-3 rounded border border-emerald-200 bg-emerald-50 text-emerald-800 flex items-center gap-2">
                <span class="text-lg">✅</span>
                <span>
                    Sesión creada y asistencia inicial registrada.
                    @if($ins > 0)
                        — <b>{{ $ins }}</b> alumno{{ $ins === 1 ? '' : 's' }} marcad{{ $ins === 1 ? 'o' : 'os' }} presente.
                    @endif
                    Puedes editar cualquier estado o comentario.
                </span>
            </div>
        @endif

        @if(session('exists'))
            <div class="p-3 rounded border border-indigo-200 bg-indigo-50 text-indigo-800 flex items-center gap-2">
                <span class="text-lg">ℹ️</span>
                <span>
                    Ya existía una sesión para esa fecha. Se abrió la existente.
                    @if($ins > 0)
                        Se añadieron <b>{{ $ins }}</b> asistencias faltantes como <b>presente</b>.
                    @endif
                </span>
            </div>
        @endif

        @if($sesion->tipo === 'sin_clase')
            <div class="p-3 rounded border border-amber-200 bg-amber-50 text-amber-800 text-sm">
                Esta sesión fue marcada como <b>sin clase</b>.
            </div>
        @endif

        {{-- Tabla con encabezado morado y borde indigo --}}
        <div class="bg-white rounded-lg shadow overflow-x-auto ring-1 ring-indigo-200">
            <table class="min-w-full text-sm align-middle">
                <thead class="bg-indigo-600 text-white sticky top-0 z-10">
                    <tr>
                        <th class="px-3 sm:px-4 py-3 text-left font-semibold uppercase tracking-wide text-[11px] sm:text-xs">#</th>
                        <th class="px-3 sm:px-4 py-3 text-left font-semibold uppercase tracking-wide text-[11px] sm:text-xs">Alumno</th>
                        <th class="px-3 sm:px-4 py-3 text-left font-semibold uppercase tracking-wide text-[11px] sm:text-xs">Estado</th>
                        <th class="px-3 sm:px-4 py-3 text-left font-semibold uppercase tracking-wide text-[11px] sm:text-xs">Hora llegada</th>
                        <th class="px-3 sm:px-4 py-3 text-left font-semibold uppercase tracking-wide text-[11px] sm:text-xs">Comentario</th>
                        @if(!$readonly)
                            <th class="px-3 sm:px-4 py-3 text-center font-semibold uppercase tracking-wide text-[11px] sm:text-xs">Guardado</th>
                        @endif
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @foreach($sesion->asistencias as $a)
                        @php
                            $estado = $a->estado;

                            // Clases del badge de estado
                            $badgeClasses = 'bg-gray-100 text-gray-500';
                            if ($estado === 'presente')    $badgeClasses = 'bg-green-100 text-green-700';
                            if ($estado === 'tarde')       $badgeClasses = 'bg-yellow-100 text-yellow-700';
                            if ($estado === 'justificado') $badgeClasses = 'bg-blue-100 text-blue-700';
                            if ($estado === 'ausente')     $badgeClasses = 'bg-red-100 text-red-700';

                            $hora       = $a->hora_llegada ? substr($a->hora_llegada, 0, 5) : '—';
                            $coment     = $a->comentario ?: '—';
                            $canEditHora = in_array($estado, ['presente','tarde']);
                        @endphp

                        <tr class="hover:bg-gray-50"
                            data-id="{{ $a->id_asistencias }}"
                            data-action="{{ route('maestro.asistencias.update', $a->id_asistencias) }}">

                            {{-- # (orden de lista) --}}
                            <td class="px-3 sm:px-4 py-3 text-gray-900 whitespace-nowrap">
                                {{ $loop->iteration }}
                            </td>

                            {{-- Alumno --}}
                            <td class="px-3 sm:px-4 py-3 text-gray-900">
                                {{ $a->alumno->nombre_completo ?? $a->alumno->name }}
                            </td>

                            {{-- Estado --}}
                            <td class="px-3 sm:px-4 py-3">
                                @if($readonly)
                                    <span class="inline-block rounded px-2 py-1 text-xs font-semibold {{ $badgeClasses }}">
                                        {{ ucfirst($estado) }}
                                    </span>
                                @else
                                    <select class="estado border-gray-300 rounded px-2 py-1 text-[13px] sm:text-sm min-w-[9.5rem]">
                                        @foreach(['presente','tarde','ausente','justificado'] as $opt)
                                            <option value="{{ $opt }}" {{ $estado === $opt ? 'selected' : '' }}>
                                                {{ ucfirst($opt) }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                            </td>

                            {{-- Hora --}}
                            <td class="px-3 sm:px-4 py-3">
                                @if($readonly)
                                    <span class="text-gray-800">{{ $hora }}</span>
                                @else
                                    <input type="time"
                                           class="hora border-gray-300 rounded px-2 py-1 text-[13px] sm:text-sm"
                                           value="{{ $a->hora_llegada ? substr($a->hora_llegada, 0, 5) : '' }}"
                                           {{ $canEditHora ? '' : 'disabled' }}>
                                @endif
                            </td>

                            {{-- Comentario --}}
                            <td class="px-3 sm:px-4 py-3">
                                @if($readonly)
                                    <span class="text-gray-800 break-words">{{ $coment }}</span>
                                @else
                                    <input type="text"
                                           class="comentario w-full border-gray-300 rounded px-2 py-1 text-[13px] sm:text-sm"
                                           placeholder="Opcional…" value="{{ $a->comentario }}">
                                @endif
                            </td>

                            {{-- Guardado (solo editable) --}}
                            @if(!$readonly)
                                <td class="px-3 sm:px-4 py-3 text-center">
                                    <span class="inline-flex items-center gap-1 text-xs text-emerald-700 bg-emerald-50 px-2 py-1 rounded hidden" data-badge="saved">✔ Actualizado</span>
                                    <span class="inline-flex items-center gap-1 text-xs text-red-700 bg-red-50 px-2 py-1 rounded hidden" data-badge="error">✖ Error</span>
                                    <span class="inline-flex items-center gap-1 text-xs text-slate-600 bg-slate-50 px-2 py-1 rounded hidden" data-badge="saving">⏳ Guardando…</span>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <p class="text-xs text-gray-500">
            @if($readonly)
                Vista de solo lectura (no se guardan cambios).
            @else
                Los cambios se guardan automáticamente.
            @endif
        </p>
    </div>

    {{-- JS autosave (solo en modo editable) --}}
    @if(!$readonly)
        @push('scripts')
            <script>
                (function () {
                    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
                    const rowTimers = new Map();

                    const showBadge = (tr, which) => {
                        tr.querySelectorAll('[data-badge]').forEach(el => el.classList.add('hidden'));
                        const el = tr.querySelector(`[data-badge="${which}"]`);
                        if (el) el.classList.remove('hidden');
                    };

                    const highlight = (el, ok = true) => {
                        el.classList.remove('ring-2', 'ring-red-400', 'ring-emerald-400');
                        el.classList.add('ring-2', ok ? 'ring-emerald-400' : 'ring-red-400');
                        setTimeout(() => el.classList.remove('ring-2', 'ring-red-400', 'ring-emerald-400'), 900);
                    };

                    const enableDisableHora = (tr) => {
                        const estado = tr.querySelector('.estado')?.value;
                        const inp = tr.querySelector('.hora');
                        if (!inp) return;
                        const canEdit = (estado === 'presente' || estado === 'tarde');
                        inp.disabled = !canEdit;
                        if (!canEdit) inp.value = '';
                    };

                    const payloadFromRow = (tr) => {
                        const estado = tr.querySelector('.estado')?.value || '';
                        const hora   = tr.querySelector('.hora')?.value || '';
                        const comm   = tr.querySelector('.comentario')?.value || '';

                        const p = new URLSearchParams();
                        p.append('_method', 'PUT');
                        p.append('estado', estado);
                        if ((estado === 'presente' || estado === 'tarde') && hora !== '') p.append('hora_llegada', hora);
                        if (comm !== '') p.append('comentario', comm);
                        return p.toString();
                    };

                    const doSave = async (tr, changedEl) => {
                        showBadge(tr, 'saving');
                        try {
                            const res = await fetch(tr.dataset.action, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': CSRF,
                                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                                },
                                body: payloadFromRow(tr)
                            });
                            if (!res.ok) throw new Error('HTTP ' + res.status);
                            showBadge(tr, 'saved');
                            if (changedEl) highlight(changedEl, true);
                            setTimeout(() => showBadge(tr, ''), 1000);
                        } catch (err) {
                            console.error(err);
                            showBadge(tr, 'error');
                            if (changedEl) highlight(changedEl, false);
                            window.twinsNotify?.({ title: 'No se pudo guardar', icon: 'error' });
                        }
                    };

                    const scheduleSave = (tr, changedEl, wait = 350) => {
                        const id = tr.dataset.id;
                        if (rowTimers.has(id)) clearTimeout(rowTimers.get(id));
                        rowTimers.set(id, setTimeout(() => doSave(tr, changedEl), wait));
                    };

                    // change: estado y hora
                    document.addEventListener('change', (e) => {
                        const tr = e.target.closest('tr[data-id]');
                        if (!tr) return;

                        if (e.target.classList.contains('estado')) {
                            enableDisableHora(tr);
                            scheduleSave(tr, e.target, 0);
                        } else if (e.target.classList.contains('hora')) {
                            scheduleSave(tr, e.target, 0);
                        }
                    });

                    // input: comentario (debounce)
                    document.addEventListener('input', (e) => {
                        if (!e.target.classList.contains('comentario')) return;
                        const tr = e.target.closest('tr[data-id]');
                        if (!tr) return;
                        scheduleSave(tr, e.target, 500);
                    });

                    // Estado inicial
                    document.querySelectorAll('tr[data-id]').forEach(enableDisableHora);
                })();
            </script>
        @endpush
    @endif
</x-app-layout>
