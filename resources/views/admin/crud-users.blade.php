<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            <h2 class="font-semibold text-xl text-center md:text-2xl text-gray-800">
                Gestión de Usuarios
            </h2>

            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.usuarios.create') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3.5 py-2 text-sm font-semibold text-black shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 active:scale-[0.98]">
                        ➕ Crear usuario
                    </a>
                </div>

                <div
                    class="inline-flex items-center gap-2 rounded-full bg-indigo-50 text-indigo-700 px-3 py-1 text-xs sm:text-sm">
                    <span class="text-lg">👥</span>
                    <span id="chipTotal">0</span>
                    <span>usuario(s)</span>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-4 sm:p-6 rounded-xl shadow-sm">
                @if (session('success'))
                <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
                @endif

                @if ($errors->any())
                <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-lg">
                    <ul class="list-disc pl-6">
                        @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Buscador + filtros en una sola fila responsiva --}}
                <div class="mb-4 grid grid-cols-1 lg:grid-cols-3 gap-3">
                    {{-- Búsqueda + acciones (2/3 en desktop) --}}
                    <div class="lg:col-span-2">
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1">
                                <input id="buscar" type="text"
                                    class="w-full rounded-lg border pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="Buscar por nombre, email o teléfono…">
                            </div>

                            <button id="btnBuscar" type="button"
                                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Buscar
                            </button>

                            <button id="btnLimpiar" type="button"
                                class="inline-flex items-center gap-2 rounded-lg bg-gray-200 px-3.5 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2">
                                Limpiar
                            </button>
                        </div>
                    </div>

                    {{-- Filtros Rol + Estado (siempre lado a lado, incluso en móvil) --}}
                    <div class="flex gap-2 flex-nowrap">
                        <select id="filtroRol"
                            class="flex-1 basis-1/2 min-w-0 rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Rol (todos)</option>
                            <option value="admin">admin</option>
                            <option value="maestro">maestro</option>
                            <option value="alumno">alumno</option>
                        </select>
                        <select id="filtroEstado"
                            class="flex-1 basis-1/2 min-w-0 rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Estado (todos)</option>
                            <option value="activo">activo</option>
                            <option value="inactivo">inactivo</option>
                        </select>
                    </div>
                </div>

                {{-- Tarjetas móviles --}}
                <div id="resultado-cards" class="md:hidden space-y-3"></div>

                {{-- Tabla desktop (sin DNI ni F. Nacimiento) --}}
                <div class="hidden md:block">
                    <div class="overflow-x-auto rounded-xl border border-gray-200">
                        <table class="min-w-[820px] w-full text-sm text-gray-800">
                            <thead class="bg-indigo-600 text-white sticky top-0 z-10">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-xs">ID
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-xs">NOMBRE
                                        COMPLETO</th>
                                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-xs">
                                        TELÉFONO</th>
                                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-xs">ROL
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-xs">ESTADO
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-xs">CURSO
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wide text-xs">
                                        ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody id="resultado" class="divide-y divide-gray-100 bg-white"></tbody>
                        </table>
                    </div>
                </div>

                <div id="emptyState" class="hidden mt-6 text-center text-gray-500">
                    <div class="text-3xl mb-2">🗂️</div>
                    <p>No se encontraron usuarios con ese criterio.</p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    /* ===================== Rutas & helpers ===================== */
    const EDIT_ROUTE_TEMPLATE = "{{ route('admin.usuarios.edit',    ['user' => '___ID___']) }}";
    const DELETE_ROUTE_TEMPLATE = "{{ route('admin.usuarios.destroy', ['user' => '___ID___']) }}";
    const SHOW_ROUTE_TEMPLATE = "{{ route('admin.usuarios.show',    ['user' => '___ID___']) }}";
    const BUSCAR_URL = "{{ route('admin.usuarios.buscar') }}";
    const CSRF_TOKEN = "{{ csrf_token() }}";
    const withId = (tpl, id) => tpl.replace('___ID___', String(id));

    /* ===================== DOM refs ===================== */
    const tbody = document.getElementById('resultado');
    const cards = document.getElementById('resultado-cards');
    const chipTotal = document.getElementById('chipTotal');
    const emptyState = document.getElementById('emptyState');
    const buscarInput = document.getElementById('buscar');
    const filtroRol = document.getElementById('filtroRol');
    const filtroEstado = document.getElementById('filtroEstado');
    const btnBuscar = document.getElementById('btnBuscar');
    const btnLimpiar = document.getElementById('btnLimpiar');

    /* ===================== Toast (fallback si no hay twinsNotify) ===================== */
    function toast(type, title, text = '') {
        if (window.twinsNotify) return window.twinsNotify({
            title,
            text,
            icon: type
        });
        const wrap = document.createElement('div');
        wrap.className = 'fixed left-1/2 -translate-x-1/2 bottom-6 z-[999998] w-[min(95vw,420px)]';
        const color =
            type === 'success' ? 'bg-emerald-600 text-white' :
            type === 'error' ? 'bg-rose-600 text-white' :
            'bg-slate-800 text-white';
        wrap.innerHTML = `<div class="rounded-lg ${color} shadow-lg px-4 py-3 text-sm">
      <div class="font-semibold">${title || ''}</div>${text ? `<div class="opacity-90">${text}</div>` : ''}
    </div>`;
        document.body.appendChild(wrap);
        setTimeout(() => {
            try {
                wrap.remove();
            } catch {}
        }, 2200);
    }

    /* ===================== Confirm como toast-modal ===================== */
    // ——— modal de confirmación con BLUR global y centrado ———
    function confirmToast(message, {
        yesLabel = 'Sí, eliminar',
        noLabel = 'Cancelar'
    } = {}) {
        return new Promise((resolve) => {
            // Elimina cualquier confirm abierto
            const old = document.getElementById('confirm-toast-modal');
            if (old) try {
                old.remove();
            } catch {}

            // Tomamos un contenedor estable para aplicar blur; Jetstream suele tener <main>
            const page = document.querySelector('main') ||
                document.querySelector('.min-h-screen') // fallback Jetstream
                ||
                document.body;

            // Crea modal
            const modal = document.createElement('div');
            modal.id = 'confirm-toast-modal';
            modal.className = 'fixed inset-0 z-[1000000] flex items-center justify-center';

            modal.innerHTML = `
      <div class="absolute inset-0 bg-black/40"></div>
      <div class="relative w-[min(95vw,520px)] px-4">
        <div class="pointer-events-auto rounded-xl border border-amber-200 bg-white shadow-xl ring-1 ring-black/5">
          <div class="p-4">
            <div class="flex items-start gap-3">
              <div class="text-amber-600 text-xl">⚠️</div>
              <div class="grow">
                <p class="font-semibold text-gray-900 text-sm">Confirmar eliminación</p>
                <p class="text-gray-600 text-xs mt-0.5">${message}</p>
                <div class="mt-3 flex items-center gap-2">
                  <button data-act="yes"
                    class="px-3 py-1.5 text-xs font-semibold rounded-md bg-rose-600 text-white hover:bg-rose-700 focus:outline-none">
                    ${yesLabel}
                  </button>
                  <button data-act="no"
                    class="px-3 py-1.5 text-xs font-semibold rounded-md bg-gray-200 text-gray-700 hover:bg-gray-300 focus:outline-none">
                    ${noLabel}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>`;

            // Inserta modal
            document.body.appendChild(modal);

            // Aplica BLUR+scale al contenido y bloquea scroll/interacción
            const prevOverflow = document.documentElement.style.overflow;
            document.documentElement.style.overflow = 'hidden';
            page.style.transition = 'filter .15s ease, transform .15s ease';
            page.style.filter = 'blur(4px) saturate(0.95)';
            page.style.transform = 'scale(.995)';
            page.setAttribute('inert', '');

            // Limpieza
            const cleanup = (val) => {
                page.removeAttribute('inert');
                page.style.filter = '';
                page.style.transform = '';
                page.style.transition = '';
                document.documentElement.style.overflow = prevOverflow || '';
                try {
                    modal.remove();
                } catch {}
                resolve(val);
            };

            // Eventos
            modal.querySelector('[data-act="yes"]').addEventListener('click', () => cleanup(true));
            modal.querySelector('[data-act="no"]').addEventListener('click', () => cleanup(false));
            // Clic en el fondo
            modal.firstElementChild.addEventListener('click', () => cleanup(false));
            // Tecla Escape
            const onKey = (e) => {
                if (e.key === 'Escape') {
                    cleanup(false);
                    window.removeEventListener('keydown', onKey);
                }
            };
            window.addEventListener('keydown', onKey);
        });
    }

    /* ===================== Loaders ===================== */
    function showLoading() {
        tbody.innerHTML = '';
        for (let i = 0; i < 6; i++) {
            tbody.insertAdjacentHTML('beforeend', `
      <tr class="animate-pulse">
        <td class="px-4 py-3"><div class="h-4 bg-gray-200 rounded w-10"></div></td>
        <td class="px-4 py-3"><div class="h-4 bg-gray-200 rounded w-48"></div></td>
        <td class="px-4 py-3"><div class="h-4 bg-gray-200 rounded w-24"></div></td>
        <td class="px-4 py-3"><div class="h-4 bg-gray-200 rounded w-16"></div></td>
        <td class="px-4 py-3"><div class="h-4 bg-gray-200 rounded w-16"></div></td>
        <td class="px-4 py-3"><div class="h-4 bg-gray-200 rounded w-28"></div></td>
        <td class="px-4 py-3"><div class="h-8 bg-gray-200 rounded w-40"></div></td>
      </tr>
    `);
        }
        cards.innerHTML = '';
        for (let i = 0; i < 3; i++) {
            cards.insertAdjacentHTML('beforeend', `
      <div class="rounded-xl border border-gray-200 p-4 shadow-sm animate-pulse">
        <div class="h-5 bg-gray-200 rounded w-1/3 mb-2"></div>
        <div class="h-4 bg-gray-200 rounded w-3/4 mb-1"></div>
        <div class="h-4 bg-gray-200 rounded w-1/2 mb-3"></div>
        <div class="h-8 bg-gray-200 rounded w-full"></div>
      </div>
    `);
        }
        emptyState.classList.add('hidden');
    }

    /* ===================== Badges ===================== */
    function roleBadge(role) {
        switch ((role || '').toLowerCase()) {
            case 'admin':
                return '<span class="inline-flex items-center gap-1 rounded-full bg-purple-100 text-purple-700 px-2 py-0.5 text-xs">🛡️ admin</span>';
            case 'maestro':
                return '<span class="inline-flex items-center gap-1 rounded-full bg-indigo-100 text-indigo-700 px-2 py-0.5 text-xs">📘 maestro</span>';
            default:
                return '<span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 text-emerald-700 px-2 py-0.5 text-xs">🎓 alumno</span>';
        }
    }

    function estadoBadge(estado) {
        return (String(estado).toLowerCase() === 'inactivo') ?
            '<span class="inline-flex items-center gap-1 rounded-full bg-rose-100 text-rose-700 px-2 py-0.5 text-xs">⛔ inactivo</span>' :
            '<span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 text-emerald-700 px-2 py-0.5 text-xs">✅ activo</span>';
    }

    /* ===================== Pintar resultados (sin onclick inline) ===================== */
    function pintar(data) {
        const rol = filtroRol.value.trim().toLowerCase();
        const est = filtroEstado.value.trim().toLowerCase();
        const filtrados = data.filter(u => {
            const okRol = rol ? String(u.role).toLowerCase() === rol : true;
            const okEst = est ? String(u.estado).toLowerCase() === est : true;
            return okRol && okEst;
        });

        chipTotal.textContent = filtrados.length;

        // Tabla
        tbody.innerHTML = filtrados.map((user, i) => {
            const editUrl = withId(EDIT_ROUTE_TEMPLATE, user.id);
            const showUrl = withId(SHOW_ROUTE_TEMPLATE, user.id);
            const zebra = i % 2 === 0 ? 'bg-white' : 'bg-gray-50';
            // ⚠️ Usamos data-* y encodeURIComponent para nombres seguros
            const encodedName = encodeURIComponent(user.nombre_completo ?? '');
            return `
      <tr class="${zebra} hover:bg-gray-50">
        <td class="px-4 py-3">${user.id}</td>
        <td class="px-4 py-3 font-medium text-gray-800">${user.nombre_completo ?? ''}</td>
        <td class="px-4 py-3">${user.telefono ?? ''}</td>
        <td class="px-4 py-3">${roleBadge(user.role)}</td>
        <td class="px-4 py-3">${estadoBadge(user.estado)}</td>
        <td class="px-4 py-3">${user.curso ?? '-'}</td>
        <td class="px-4 py-3">
          <div class="flex flex-nowrap justify-start lg:justify-center items-center gap-2 overflow-x-auto whitespace-nowrap">
            <a href="${editUrl}" class="inline-flex items-center justify-center gap-1 rounded-md bg-amber-500 px-3 py-1.5 text-xs font-semibold text-gray-900 shadow-sm hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2" title="Editar">✏️ <span class="hidden xl:inline">Editar</span></a>
            <a href="${showUrl}" class="inline-flex items-center justify-center gap-1 rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-black shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2" title="Ver">👁️ <span class="hidden xl:inline">Ver</span></a>
            <button type="button"
              data-delete-id="${user.id}"
              data-delete-name="${encodedName}"
              class="inline-flex items-center justify-center gap-1 rounded-md bg-rose-600 px-3 py-1.5 text-xs font-semibold text-black shadow-sm hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2"
              title="Eliminar">🗑 <span class="hidden xl:inline">Eliminar</span></button>
          </div>
        </td>
      </tr>`;
        }).join('');

        // Cards (móvil)
        cards.innerHTML = filtrados.map(user => {
            const editUrl = withId(EDIT_ROUTE_TEMPLATE, user.id);
            const showUrl = withId(SHOW_ROUTE_TEMPLATE, user.id);
            const encodedName = encodeURIComponent(user.nombre_completo ?? '');
            return `
      <div class="rounded-xl border border-gray-200 p-4 shadow-sm">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <div class="text-xs text-gray-500">ID #${user.id}</div>
            <h3 class="text-base font-semibold text-gray-900 truncate">${user.nombre_completo ?? ''}</h3>
            <div class="mt-1 flex flex-wrap items-center gap-2">
              ${roleBadge(user.role)} ${estadoBadge(user.estado)}
            </div>
            <p class="mt-2 text-sm text-gray-600">
              Tel: <span class="font-medium">${user.telefono ?? '-'}</span><br>
              Curso: <span class="font-medium">${user.curso ?? '-'}</span>
            </p>
          </div>
        </div>
        <div class="mt-3 grid grid-cols-3 gap-2">
          <a href="${editUrl}" class="col-span-1 inline-flex items-center justify-center gap-1 rounded-md bg-amber-500 px-3 py-2 text-xs font-semibold text-gray-900 shadow-sm hover:bg-amber-600">✏️ Editar</a>
          <a href="${showUrl}" class="col-span-1 inline-flex items-center justify-center gap-1 rounded-md bg-emerald-600 px-3 py-2 text-xs font-semibold text-black shadow-sm hover:bg-emerald-700">👁️ Ver</a>
          <button
            data-delete-id="${user.id}"
            data-delete-name="${encodedName}"
            class="col-span-1 inline-flex items-center justify-center gap-1 rounded-md bg-rose-600 px-3 py-2 text-xs font-semibold text-black shadow-sm hover:bg-rose-700">🗑 Eliminar</button>
        </div>
      </div>`;
        }).join('');

        emptyState.classList.toggle('hidden', filtrados.length !== 0);
    }

    /* ===================== Fetch ===================== */
    function buildQuery() {
        const params = new URLSearchParams();
        const q = buscarInput.value.trim();
        const r = filtroRol.value.trim();
        const e = filtroEstado.value.trim();
        if (q) params.set('query', q);
        if (r) params.set('role', r);
        if (e) params.set('estado', e);
        return params.toString();
    }

    function cargarUsuarios() {
        showLoading();
        const qs = buildQuery();
        const url = qs ? (BUSCAR_URL + '?' + qs) : BUSCAR_URL;
        fetch(url, {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(async res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(pintar)
            .catch(err => {
                console.error('Fallo fetch:', err);
                tbody.innerHTML =
                    `<tr><td colspan="7" class="px-4 py-3 text-rose-600">No se pudo cargar. Revisa consola.</td></tr>`;
                cards.innerHTML = '';
                chipTotal.textContent = '0';
                emptyState.classList.remove('hidden');
            });
    }

    /* ===================== Eliminar (delegación de eventos) ===================== */
    async function eliminarUsuario(id, nombre = '') {
        const ok = await confirmToast(
            `¿Estás seguro que quieres eliminar ${nombre ? `"${nombre}"` : 'este usuario'}? Esta acción no se puede deshacer.`
        );
        if (!ok) {
            toast('info', 'Acción cancelada');
            return;
        }

        const url = withId(DELETE_ROUTE_TEMPLATE, id);
        try {
            const res = await fetch(url, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            const text = await res.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch {
                data = {
                    success: false,
                    message: text
                };
            }

            if (!res.ok || !data.success) {
                toast('error', data?.message || 'No se pudo eliminar el usuario.');
                return;
            }
            toast('success', 'Usuario eliminado correctamente');
            cargarUsuarios();
        } catch (e) {
            console.error(e);
            toast('error', 'Error eliminando (revisa consola).');
        }
    }

    // Delegación: escucha clics en cualquier botón con data-delete-id
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-delete-id]');
        if (!btn) return;
        const id = btn.getAttribute('data-delete-id');
        const nombre = decodeURIComponent(btn.getAttribute('data-delete-name') || '');
        eliminarUsuario(id, nombre);
    });

    /* ===================== Eventos búsqueda ===================== */
    const debounce = (fn, t = 300) => {
        let h;
        return (...a) => {
            clearTimeout(h);
            h = setTimeout(() => fn(...a), t);
        };
    };
    btnBuscar.addEventListener('click', cargarUsuarios);
    btnLimpiar.addEventListener('click', () => {
        buscarInput.value = '';
        buscarInput.focus();
        buscarInput.dispatchEvent(new Event('input'));
        cargarUsuarios();
    });
    buscarInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') cargarUsuarios();
    });
    buscarInput.addEventListener('input', debounce(cargarUsuarios, 350));
    filtroRol.addEventListener('change', cargarUsuarios);
    filtroEstado.addEventListener('change', cargarUsuarios);

    cargarUsuarios();
    </script>
    @endpush


</x-app-layout>