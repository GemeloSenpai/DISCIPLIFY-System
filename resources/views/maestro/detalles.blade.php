<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl md:text-2xl text-gray-800">
                Curso: {{ $curso->nombre ?? $curso->titulo }}
            </h2>

            @php
            $user = Auth::user();
            $isAdmin = method_exists($user, 'hasRole')
            ? $user->hasRole('admin')
            : (($user->role ?? null) === 'admin');
            @endphp
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">

                    <h3 class="text-lg font-semibold mb-3">Mentor(es):</h3>
                    <ul class="list-disc list-inside mb-6 text-gray-800">
                        @foreach($maestros as $maestro)
                        <li>{{ $maestro->name }} — {{ $maestro->email }}</li>
                        @endforeach
                    </ul>

                    {{-- ACCIONES: todos con mismo tamaño/altura y con color sólido --}}
                    <div class="mb-6">
                        @php
                        // Clase base para todos los botones/enlaces
                        $btn = 'w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 h-10
                        text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-offset-2';

                        // Asegura $rutaResultados si aún no existe
                        if (!isset($rutaResultados)) {
                        $rutaResultados = (auth()->user()->role === 'admin')
                        ? 'admin.asistencias.resultados'
                        : 'maestro.asistencias.resultados';
                        }
                        @endphp

                        <div class="flex flex-col sm:flex-row justify-center gap-2 sm:gap-3">
                            {{-- Sesiones --}}
                            <a href="{{ $isAdmin ? route('admin.sesiones.index', $curso->id_cursos)
                          : route('maestro.sesiones.index', $curso->id_cursos) }}"
                                class="{{ $btn }} bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-400"
                                title="Sesiones (pasar lista)" aria-label="Sesiones (pasar lista)">
                                <span class="text-lg" aria-hidden="true">🗓️</span>
                                <span>Sesiones (pasar lista)</span>
                            </a>

                            @unless($isAdmin)
                            {{-- Crear tarea (modal) --}}
                            <a href="#" onclick="event.preventDefault();" data-open-modal="#modalCrearTarea"
                                class="{{ $btn }} bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-400"
                                title="Crear tarea" aria-label="Crear tarea">
                                <span class="text-lg" aria-hidden="true">➕📝</span>
                                <span>Crear tarea</span>
                            </a>
                            @endunless

                            {{-- Ver tareas (modal) --}}
                            <a href="#" onclick="event.preventDefault();" data-open-modal="#modalVerTareas"
                                class="{{ $btn }} bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-slate-400"
                                title="Ver tareas" aria-label="Ver tareas">
                                <span class="text-lg" aria-hidden="true">👀📝</span>
                                <span>Ver tareas</span>
                            </a>

                            {{-- Resultados de asistencia --}}
                            <a href="{{ route($rutaResultados, $curso) }}"
                                class="{{ $btn }} bg-purple-600 text-white hover:bg-purple-700 focus:ring-purple-400"
                                title="Resultados de asistencia" aria-label="Resultados de asistencia">
                                <span class="text-lg" aria-hidden="true">📊</span>
                                <span>Resultados de asistencia</span>
                            </a>
                        </div>

                    </div>

                    {{-- ===================== MODAL CREAR TAREA (UI mejorada) ===================== --}}
                    <div id="modalCrearTarea"
                        class="fixed inset-0 bg-black/50 backdrop-blur-sm flex justify-center items-center hidden z-50 p-3">
                        <div class="bg-white rounded-xl w-full max-w-md shadow-xl" role="dialog" aria-modal="true"
                            aria-labelledby="modalCrearTareaTitle">
                            {{-- Header --}}
                            <div class="flex items-center justify-between px-4 py-3 border-b">
                                <h2 id="modalCrearTareaTitle" class="text-base font-semibold">✏️ Nueva tarea</h2>
                                <button type="button" data-close-modal="#modalCrearTarea"
                                    class="inline-flex items-center justify-center h-9 w-9 rounded-lg bg-gray-100 hover:bg-gray-200"
                                    title="Cerrar" aria-label="Cerrar">✖️</button>
                            </div>

                            {{-- Body (scrollable en móvil) --}}
                            <form id="formCrearTarea" class="px-4 py-4">
                                @csrf
                                <input type="hidden" name="id_cursos" value="{{ $curso->id_cursos }}">

                                <label class="block text-sm font-medium mb-1">Título</label>
                                <input type="text" name="titulo" required
                                    class="w-full border rounded-lg px-3 py-2 mb-3 focus:outline-none focus:ring-2 focus:ring-emerald-400"
                                    placeholder="Ej. Ensayo capítulo 1">

                                <label class="block text-sm font-medium mb-1">Descripción</label>
                                <textarea name="descripcion" required rows="3"
                                    class="w-full border rounded-lg px-3 py-2 mb-3 focus:outline-none focus:ring-2 focus:ring-emerald-400"
                                    placeholder="Instrucciones de la tarea..."></textarea>

                                <label class="block text-sm font-medium mb-1">Fecha de entrega (opcional)</label>
                                <input type="date" name="fecha_entrega"
                                    class="w-full border rounded-lg px-3 py-2 mb-4 focus:outline-none focus:ring-2 focus:ring-emerald-400">

                                {{-- Footer --}}
                                <div class="flex items-center justify-end gap-2">
                                    {{-- mantiene tu id para que siga funcionando tu listener --}}
                                    <button type="button" id="btnCancelarModal" data-close-modal="#modalCrearTarea"
                                        class="inline-flex items-center justify-center h-9 px-3 rounded-lg bg-gray-500 text-white text-sm hover:bg-gray-600">
                                        Cancelar
                                    </button>
                                    <button type="submit"
                                        class="inline-flex items-center justify-center h-9 px-3 rounded-lg bg-emerald-600 text-white text-sm hover:bg-emerald-700">
                                        Guardar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- ====================== MODAL VER TAREAS (UI mejorada) ====================== --}}
                    <div id="modalVerTareas"
                        class="fixed inset-0 bg-black/50 backdrop-blur-sm flex justify-center items-center hidden z-50 p-3">
                        <div class="bg-white rounded-xl w-full max-w-3xl shadow-xl" role="dialog" aria-modal="true"
                            aria-labelledby="modalVerTareasTitle">
                            {{-- Header --}}
                            <div class="flex items-center justify-between px-4 py-3 border-b">
                                <h2 id="modalVerTareasTitle" class="text-base font-semibold">📋 Tareas del curso</h2>
                                <button type="button" data-close-modal="#modalVerTareas"
                                    class="inline-flex items-center justify-center h-9 w-9 rounded-lg bg-gray-100 hover:bg-gray-200"
                                    title="Cerrar" aria-label="Cerrar">✖️</button>
                            </div>

                            {{-- Tabla (scroll + header sticky + compacta) --}}
                            <div class="px-2 sm:px-4 py-3">
                                <div class="w-full ring-1 ring-indigo-200 rounded-lg overflow-x-auto overflow-y-auto max-h-[70vh]"
                                    style="-webkit-overflow-scrolling: touch; overscroll-behavior: contain;">
                                    <table class="min-w-full text-sm text-gray-800 align-middle">
                                        <thead class="bg-indigo-600 text-white sticky top-0 z-10">
                                            <tr>
                                                <th
                                                    class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-xs w-12">
                                                    #</th>
                                                <th
                                                    class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-xs">
                                                    Título</th>
                                                <th
                                                    class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-xs whitespace-nowrap">
                                                    Fecha entrega</th>
                                                @unless($isAdmin)
                                                <th
                                                    class="px-3 py-2 text-left font-semibold uppercase tracking-wide text-xs">
                                                    Acciones</th>
                                                @endunless
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            @forelse($tareas as $idx => $t)
                                            <tr class="odd:bg-white even:bg-gray-50 hover:bg-gray-100">
                                                <td class="px-3 py-2">{{ $idx + 1 }}</td>
                                                <td class="px-3 py-2">
                                                    <div class="truncate max-w-[18rem]" title="{{ $t->titulo }}">
                                                        {{ $t->titulo }}</div>
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    {{ $t->fecha_entrega ? \Carbon\Carbon::parse($t->fecha_entrega)->format('d/m/Y') : '—' }}
                                                </td>

                                                @unless($isAdmin)
                                                <td class="px-3 py-2">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        {{-- Editar (botón pequeño, color amber) --}}
                                                        <button type="button"
                                                            class="inline-flex items-center justify-center h-9 w-9 rounded-lg bg-amber-500 text-white hover:bg-amber-600"
                                                            data-edit-tarea title="Editar" aria-label="Editar"
                                                            data-id="{{ $t->id_tareas }}"
                                                            data-update-url="{{ route('maestro.tareas.update', $t->id_tareas) }}"
                                                            data-titulo="{{ $t->titulo }}"
                                                            data-descripcion="{{ $t->descripcion }}"
                                                            data-fecha="{{ $t->fecha_entrega ?? '' }}">✏️</button>

                                                        {{-- Eliminar (botón pequeño, rojo) --}}
                                                        <button type="button"
                                                            class="inline-flex items-center justify-center h-9 w-9 rounded-lg bg-red-600 text-white hover:bg-red-700"
                                                            data-delete-tarea title="Eliminar" aria-label="Eliminar"
                                                            data-id="{{ $t->id_tareas }}" data-num="{{ $idx + 1 }}"
                                                            data-destroy-url="{{ route('maestro.tareas.destroy', $t->id_tareas) }}">🗑️</button>
                                                    </div>
                                                </td>
                                                @endunless
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="4" class="px-4 py-6 text-center text-gray-500">Sin tareas.
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Footer --}}
                                <div class="mt-4 flex justify-end">
                                    <button type="button" data-close-modal="#modalVerTareas"
                                        class="inline-flex items-center justify-center h-9 px-3 rounded-lg bg-gray-500 text-white text-sm hover:bg-gray-600">
                                        Cerrar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- MODAL EDITAR TAREA --}}
                    <div id="modalEditarTarea"
                        class="fixed inset-0 bg-black/50 backdrop-blur-sm flex justify-center items-center hidden z-50 p-3">
                        <div class="bg-white p-6 rounded-lg w-full max-w-md shadow-lg">
                            <h2 class="text-xl font-semibold mb-4">Editar Tarea</h2>
                            <form id="formEditarTarea" data-update-url="">
                                @csrf
                                <input type="hidden" name="tarea_id" id="edit_tarea_id">

                                <div class="mb-3">
                                    <label class="block text-sm font-medium">Título</label>
                                    <input type="text" name="titulo" id="edit_titulo"
                                        class="w-full border px-3 py-2 rounded" required>
                                </div>

                                <div class="mb-3">
                                    <label class="block text-sm font-medium">Descripción</label>
                                    <textarea name="descripcion" id="edit_descripcion"
                                        class="w-full border px-3 py-2 rounded" required></textarea>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium">Fecha de Entrega (opcional)</label>
                                    <input type="date" name="fecha_entrega" id="edit_fecha"
                                        class="w-full border px-3 py-2 rounded">
                                </div>

                                <div class="flex flex-col sm:flex-row justify-end gap-2">
                                    <button type="button" data-close-modal="#modalEditarTarea"
                                        class="w-full sm:w-auto bg-gray-500 px-4 py-2 h-10 rounded text-white hover:bg-gray-600">Cancelar</button>
                                    <button type="submit"
                                        class="w-full sm:w-auto bg-emerald-600 px-4 py-2 h-10 rounded text-white hover:bg-emerald-700">Guardar</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- ALUMNOS --}}
                    <h3 class="text-lg font-semibold mt-8 mb-3">Progreso de Entregas:</h3>
                    <div class="w-full overflow-x-auto rounded-lg shadow-sm">
                        <table class="min-w-full text-sm text-gray-800 align-middle">
                            <thead class="bg-indigo-600 text-white sticky top-0">
                                <tr class="text-center">
                                    <th class="px-4 py-2 text-left font-semibold uppercase tracking-wide text-xs">#</th>
                                    <th class="px-4 py-2 text-left font-semibold uppercase tracking-wide text-xs">Nombre
                                        Completo</th>
                                    <th class="px-4 py-2 text-center font-semibold uppercase tracking-wide text-xs">
                                        Tareas entregadas</th>
                                    <th class="px-4 py-2 text-center font-semibold uppercase tracking-wide text-xs">
                                        Progreso</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @php $n = 1; @endphp
                                @foreach($alumnos as $alumno)
                                @php
                                $ent = (int) ($conteos[$alumno->id] ?? 0);
                                $tot = max((int) $totalTareas, 1);
                                $pct = (int) round(($ent / $tot) * 100);
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2">{{ $n++ }}</td>
                                    <td class="px-4 py-2">{{ $alumno->nombre_completo }}</td>
                                    <td class="px-4 py-2 text-center">{{ $ent }} de {{ $totalTareas }}</td>
                                    <td class="px-4 py-2">
                                        <div class="flex items-center gap-2 justify-center sm:justify-start">
                                            <div class="w-40 bg-gray-200 rounded-full h-2.5">
                                                <div class="h-2.5 rounded-full bg-indigo-600"
                                                    style="width: {{ $pct }}%"></div>
                                            </div>
                                            <span class="text-xs text-gray-600">{{ $pct }}%</span>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach

                                @if($alumnos->isEmpty())
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-500">No hay alumnos
                                        inscritos.</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    (function() {
        const $ = (sel, ctx = document) => ctx.querySelector(sel);
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        // Helpers de notificación centrada (usa los mismos de Sesiones)
        const toast = (title, icon = 'success', opts = {}) => {
            if (window.twinsNotify) {
                return window.twinsNotify(Object.assign({
                    title,
                    icon,
                    timer: 1400
                }, opts));
            }
            // Fallback muy simple
            alert(title);
            return Promise.resolve({
                isConfirmed: true
            });
        };

        const confirmDlg = (title, text = '') => {
            if (window.twinsNotify) {
                return window.twinsNotify({
                    title,
                    text,
                    icon: 'question',
                    showConfirmButton: true,
                    confirmButtonText: 'Sí, continuar',
                    showCancelButton: true,
                    cancelButtonText: 'Cancelar',
                });
            }
            // Fallback nativo
            return Promise.resolve({
                isConfirmed: confirm(title + (text ? '\n\n' + text : ''))
            });
        };

        // Fallback visual (tu flash original)
        function flash(msg, ok = true) {
            const cont = document.getElementById('flash') || document.body;
            const box = document.createElement('div');
            box.className =
                'max-w-7xl mx-auto sm:px-6 lg:px-8 mt-3 mb-4 rounded border px-4 py-3 ' +
                (ok ? 'bg-green-100 border-green-300 text-green-800' :
                    'bg-red-100 border-red-300 text-red-800');
            box.innerHTML = `<strong class="font-semibold">${ ok ? 'Éxito' : 'Aviso' }:</strong> ${msg}`;
            cont.prepend(box);
            setTimeout(() => box.remove(), 5000);
        }

        // Abrir/Cerrar modales
        document.addEventListener('click', (e) => {
            const opener = e.target.closest('[data-open-modal]');
            if (opener) {
                const sel = opener.getAttribute('data-open-modal');
                document.querySelector(sel)?.classList.remove('hidden');
            }
            const closer = e.target.closest('[data-close-modal]');
            if (closer) {
                const sel = closer.getAttribute('data-close-modal');
                document.querySelector(sel)?.classList.add('hidden');
            }
        });

        // Cerrar por backdrop y ESC
        ['#modalCrearTarea', '#modalVerTareas', '#modalEditarTarea'].forEach(id => {
            const m = $(id);
            m?.addEventListener('click', (ev) => {
                if (ev.target === m) m.classList.add('hidden');
            });
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape')['#modalCrearTarea', '#modalVerTareas', '#modalEditarTarea']
                .forEach(id => $(id)?.classList.add('hidden'));
        });

        // -------- Crear tarea
        (function() {
            const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            // --- Crear tarea (AJAX + toasts)
            const formCrear = document.getElementById('formCrearTarea');
            formCrear?.addEventListener('submit', async (e) => {
                e.preventDefault();

                const modal = document.getElementById('modalCrearTarea');
                const fd = new FormData(formCrear);
                const btn = formCrear.querySelector('button[type="submit"]');

                btn && (btn.disabled = true, btn.textContent = 'Guardando…');

                try {
                    const res = await fetch("{{ route('maestro.tareas.store') }}", {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': CSRF,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: fd,
                        credentials: 'same-origin'
                    });

                    const ct = res.headers.get('content-type') || '';
                    let data = null;
                    if (ct.includes('application/json')) {
                        data = await res.json();
                    }

                    if (res.status === 201 && data?.success) {
                        window.twinsNotify?.({
                            title: `Tarea #${data.numero} creada`,
                            icon: 'success',
                            timer: 1400
                        });
                        modal?.classList.add('hidden');
                        formCrear.reset();
                        setTimeout(() => location.reload(), 300);
                        return;
                    }

                    // 422 Validación
                    if (res.status === 422) {
                        const mensajes = data?.errors ?
                            Object.values(data.errors).flat().join('<br>') :
                            'Revisa los campos.';
                        window.twinsNotify?.({
                            title: 'No se pudo crear',
                            html: mensajes,
                            icon: 'error'
                        });
                        return;
                    }

                    // 403 / 419 / 500 y otros
                    window.twinsNotify?.({
                        title: 'No se pudo crear la tarea',
                        text: data?.message ?? `Error ${res.status}`,
                        icon: 'error'
                    });

                } catch (err) {
                    console.error(err);
                    window.twinsNotify?.({
                        title: 'Error de red',
                        text: 'Verifica tu conexión.',
                        icon: 'error'
                    });
                } finally {
                    btn && (btn.disabled = false, btn.textContent = 'Guardar');
                }
            });

            // --- (Opcional) Toasters en EDITAR/ELIMINAR ya los tienes, pero por si acaso:
            document.getElementById('formEditarTarea')?.addEventListener('submit', () => {
                window.twinsNotify?.({
                    title: 'Guardando cambios…',
                    icon: 'info',
                    timer: 900
                });
            });
            document.addEventListener('click', (e) => {
                const del = e.target.closest('[data-delete-tarea]');
                if (!del) return;
                e.preventDefault();
                window.twinsNotify?.({
                    title: `¿Eliminar la Tarea #${del.dataset.num}?`,
                    icon: 'warning',
                    showConfirmButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    showCancelButton: true,
                    cancelButtonText: 'Cancelar'
                }).then(r => {
                    if (r.isConfirmed) {
                        // dispara el flujo que ya tienes (fetch DELETE)
                        del.setAttribute('data-confirmed', '1');
                        del.click();
                    }
                });
            }, {
                capture: true
            });
        })();

        // -------- EDITAR: abrir modal precargado
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-edit-tarea]');
            if (!btn) return;

            $('#edit_tarea_id').value = btn.dataset.id;
            $('#edit_titulo').value = btn.dataset.titulo || '';
            $('#edit_descripcion').value = btn.dataset.descripcion || '';
            $('#edit_fecha').value = btn.dataset.fecha || '';

            const formEdit = document.getElementById('formEditarTarea');
            formEdit.dataset.updateUrl = btn.dataset.updateUrl;

            document.getElementById('modalEditarTarea')?.classList.remove('hidden');
        });

        // -------- EDITAR: submit (PUT)
        document.getElementById('formEditarTarea')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const url = form.dataset.updateUrl;
            if (!url) return;

            const fd = new FormData(form);
            const params = new URLSearchParams();
            params.append('_method', 'PUT');
            params.append('titulo', fd.get('titulo'));
            params.append('descripcion', fd.get('descripcion'));
            if (fd.get('fecha_entrega')) params.append('fecha_entrega', fd.get('fecha_entrega'));

            let ok = false;
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: params.toString(),
                    credentials: 'same-origin'
                });
                ok = res.ok;
            } catch (err) {
                ok = false;
                console.error(err);
            }

            if (ok) {
                sessionStorage.setItem('__toast__', JSON.stringify({
                    title: 'Tarea actualizada',
                    icon: 'success'
                }));
                location.reload();
            } else {
                toast('No se pudo actualizar la tarea', 'error');
            }
        });

        // -------- ELIMINAR (confirmación centrada + toast)
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('[data-delete-tarea]');
            if (!btn) return;

            const num = btn.dataset.num;
            const url = btn.dataset.destroyUrl;
            if (!url) return;

            const r = await confirmDlg(`¿Eliminar la Tarea #${num}?`,
                'Se eliminarán sus calificaciones.');
            if (!r.isConfirmed) return;

            let ok = false;
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: new URLSearchParams({
                        _method: 'DELETE'
                    }).toString(),
                    credentials: 'same-origin'
                });
                ok = res.ok;
            } catch (err) {
                ok = false;
                console.error(err);
            }

            if (ok) {
                sessionStorage.setItem('__toast__', JSON.stringify({
                    title: `Tarea #${num} eliminada`,
                    icon: 'success'
                }));
                location.reload();
            } else {
                toast('No se pudo eliminar la tarea', 'error');
            }
        });

        // Botón cancelar (crear)
        document.getElementById('btnCancelarModal')?.addEventListener('click', () => {
            document.getElementById('modalCrearTarea')?.classList.add('hidden');
        });

        // -------- Mostrar toast pendiente tras recarga
        document.addEventListener('DOMContentLoaded', function() {
            // Prioriza toast centrado
            const buf = sessionStorage.getItem('__toast__');
            if (buf) {
                try {
                    const {
                        title,
                        icon
                    } = JSON.parse(buf);
                    toast(title || 'Operación realizada', icon || 'success');
                } catch (_) {}
                sessionStorage.removeItem('__toast__');
                return;
            }

            // Fallback: tu flash anterior
            const msg = sessionStorage.getItem('flash_msg');
            if (msg) {
                flash(msg, true);
                sessionStorage.removeItem('flash_msg');
            }
        });
    })();
    </script>
    @endpush

</x-app-layout>