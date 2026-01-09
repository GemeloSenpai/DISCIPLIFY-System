<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl md:text-2xl">Sesiones — {{ $curso->titulo }}</h2>

            <button type="button"
                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-indigo-700 active:scale-[.99]"
                onclick="const d=document.getElementById('modalCrear'); d && (d.showModal ? d.showModal() : d.setAttribute('open',''))">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M10 3.75a.75.75 0 0 1 .75.75v4.75H15.5a.75.75 0 0 1 0 1.5h-4.75V15.5a.75.75 0 0 1-1.5 0v-4.75H4.5a.75.75 0 0 1 0-1.5h4.75V4.5A.75.75 0 0 1 10 3.75z"/>
                </svg>
                <span class="hidden sm:inline">Crear sesión</span>
            </button>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto p-4 space-y-4">
        <div class="bg-white rounded-lg shadow overflow-x-auto max-h-[70vh] ring-1 ring-gray-200">
            <table class="min-w-full text-sm align-middle">
                <thead class="bg-indigo-600 text-white sticky top-0 z-10">
                    <tr>
                        <th class="px-3 sm:px-4 py-3 text-left font-semibold uppercase tracking-wide text-[11px] sm:text-xs">
                            Fecha de la sesión
                        </th>
                        <th class="px-3 sm:px-4 py-3 text-left font-semibold uppercase tracking-wide text-[11px] sm:text-xs">
                            Tipo de sesión
                        </th>
                        <th class="px-3 sm:px-4 py-3 text-center font-semibold uppercase tracking-wide text-[11px] sm:text-xs">
                            Acciones
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @forelse($sesiones as $s)
                        <tr class="hover:bg-gray-50" data-id="{{ $s->id_sesiones }}"
                            data-update="{{ route('maestro.sesiones.update', [$curso->id_cursos, $s->id_sesiones]) }}">

                            {{-- Fecha (no romper) --}}
                            <td class="px-3 sm:px-4 py-3 whitespace-nowrap text-gray-900 text-[13px] sm:text-sm">
                                {{ \Carbon\Carbon::parse($s->fecha)->isoFormat('DD/MM/YYYY') }}
                            </td>

                            {{-- Tipo: ancho mínimo en móvil para no cortarse --}}
                            <td class="px-3 sm:px-4 py-3">
                                <select
                                    class="tipo border-gray-300 rounded px-2 py-1 text-[13px] sm:text-sm w-full sm:w-44 min-w-[10.5rem]">
                                    <option value="con_clase"  {{ $s->tipo === 'con_clase'  ? 'selected' : '' }}>Clase</option>
                                    <option value="sin_clase"  {{ $s->tipo === 'sin_clase'  ? 'selected' : '' }}>Sin clases</option>
                                </select>
                            </td>

                            {{-- Acciones: SOLO íconos en móvil; ícono + texto desde sm: --}}
                            <td class="px-3 sm:px-4 py-3">
                                <div class="flex items-center justify-center gap-2 flex-wrap" x-data>
                                    {{-- Ver lista --}}
                                    <a href="{{ route('maestro.sesiones.show', [$curso->id_cursos, $s->id_sesiones, 'view' => 1]) }}"
                                       class="inline-flex items-center justify-center gap-2 rounded-lg border px-2 sm:px-3 py-2 text-sm hover:bg-gray-50"
                                       title="Ver lista" aria-label="Ver lista">
                                        <svg class="h-5 w-5 -ml-[2px]" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path d="M10 3.5c4.167 0 7.5 2.917 9 6.5-1.5 3.583-4.833 6.5-9 6.5S2.5 13.583 1 10C2.5 6.417 5.833 3.5 10 3.5Zm0 2c-2.9 0-5.417 1.9-6.75 4.5 1.333 2.6 3.85 4.5 6.75 4.5s5.417-1.9 6.75-4.5C15.417 7.4 12.9 5.5 10 5.5Zm0 1.75A2.75 2.75 0 1 1 7.25 10 2.75 2.75 0 0 1 10 7.25Z"/>
                                        </svg>
                                        <span class="hidden sm:inline">Ver lista</span>
                                    </a>

                                    {{-- Tomar asistencia --}}
                                    <a href="{{ route('maestro.sesiones.show', [$curso->id_cursos, $s->id_sesiones]) }}"
                                       class="inline-flex items-center justify-center gap-2 rounded-lg border px-2 sm:px-3 py-2 text-sm hover:bg-gray-50"
                                       title="Tomar asistencia" aria-label="Tomar asistencia">
                                        <svg class="h-5 w-5 -ml-[2px]" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path d="M16.5 3.75a.75.75 0 0 1 .75.75v11a.75.75 0 0 1-.75.75h-13a.75.75 0 0 1-.75-.75v-11a.75.75 0 0 1 .75-.75h13ZM6.5 2a.75.75 0 0 1 .75.75V3.5h5.5v-.75a.75.75 0 0 1 1.5 0V3.5h1.75A2.25 2.25 0 0 1 18.25 5v11A2.25 2.25 0 0 1 16 18.25H4A2.25 2.25 0 0 1 1.75 16V5A2.25 2.25 0 0 1 4 2.75H5.75V2.5A.75.75 0 0 1 6.5 2Zm7.03 6.28a.75.75 0 0 0-1.06-1.06L9 10.69 7.53 9.22a.75.75 0 0 0-1.06 1.06L8.47 12.28a.75.75 0 0 0 1.06 0l4-4Z"/>
                                        </svg>
                                        <span class="hidden sm:inline">Tomar asistencia</span>
                                    </a>

                                    {{-- Eliminar --}}
                                    <button type="button"
                                        class="inline-flex items-center justify-center gap-2 rounded-lg border px-2 sm:px-3 py-2 text-sm hover:bg-gray-50 text-red-600"
                                        title="Eliminar" aria-label="Eliminar"
                                        @click="
                                            window.twinsNotify({
                                                title:'¿Eliminar sesión?',
                                                icon:'question',
                                                showConfirmButton:true,
                                                confirmButtonText:'Sí, borrar'
                                            }).then(r => { if(r.isConfirmed){ $refs.delForm.submit(); } })
                                        ">
                                        <svg class="h-5 w-5 -ml-[2px]" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path d="M7.5 2.75A1.75 1.75 0 0 1 9.25 1h1.5A1.75 1.75 0 0 1 12.5 2.75V3.5H16a.75.75 0 0 1 0 1.5h-.7l-.73 10.04A2.25 2.25 0 0 1 12.33 17.25H7.67a2.25 2.25 0 0 1-2.24-2.21L4.7 5H4a.75.75 0 1 1 0-1.5h3.5v-.75ZM6.22 5l.7 9.96c.04.4.37.71.77.71h4.64c.4 0 .73-.31.77-.71L13.78 5H6.22ZM8.5 7.25a.75.75 0 0 1 .75.75v5a.75.75 0 0 1-1.5 0V8a.75.75 0 0 1 .75-.75Zm3 0A.75.75 0 0 1 12.25 8v5a.75.75 0 0 1-1.5 0V8a.75.75 0 0 1 .75-.75Z"/>
                                        </svg>
                                        <span class="hidden sm:inline">Eliminar</span>
                                    </button>

                                    <form x-ref="delForm" method="POST"
                                          action="{{ route('maestro.sesiones.destroy', [$curso->id_cursos, $s->id_sesiones]) }}"
                                          class="hidden">
                                        @csrf @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-gray-500">Sin sesiones.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <p class="text-xs text-gray-500">Cambia el tipo con el select; se guarda automáticamente.</p>
    </div>

    {{-- Modal crear sesión --}}
    <dialog id="modalCrear" class="rounded-xl p-0 w-full max-w-md">
        <form method="POST" action="{{ route('maestro.sesiones.store', $curso->id_cursos) }}" class="p-5 space-y-4">
            @csrf
            <h3 class="text-lg font-semibold">Crear sesión</h3>
            <div class="space-y-2">
                <label class="block text-sm font-medium">Fecha</label>
                <input type="date" name="fecha" required class="w-full border-gray-300 rounded">
            </div>
            <div class="space-y-2">
                <label class="block text-sm font-medium">Tipo</label>
                <select name="tipo" class="w-full border-gray-300 rounded">
                    <option value="con_clase">Clase</option>
                    <option value="sin_clase">Sin clase</option>
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="px-3 py-2 rounded border"
                        onclick="document.getElementById('modalCrear').close()">Cancelar</button>
                <button type="submit" class="inline-flex items-center gap-2 px-3 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3.75a.75.75 0 0 1 .75.75v4.75H15.5a.75.75 0 0 1 0 1.5h-4.75V15.5a.75.75 0 0 1-1.5 0v-4.75H4.5a.75.75 0 0 1 0-1.5h4.75V4.5A.75.75 0 0 1 10 3.75z"/></svg>
                    Crear
                </button>
            </div>
        </form>
    </dialog>

    @push('scripts')
    <script>
    (function () {
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;

        // Edición inline del tipo (PUT)
        document.addEventListener('change', async (e) => {
            if (!e.target.classList.contains('tipo')) return;
            const tr = e.target.closest('tr[data-update]');
            if (!tr) return;

            const url = tr.dataset.update;
            const params = new URLSearchParams();
            params.append('_method', 'PUT');
            params.append('tipo', e.target.value);

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                    },
                    body: params.toString()
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                window.twinsNotify?.({ title: 'Sesión actualizada', icon: 'success', timer: 1000 });
            } catch (err) {
                console.error(err);
                window.twinsNotify?.({ title: 'No se pudo actualizar', icon: 'error' });
            }
        });
    })();
    </script>
    @endpush
</x-app-layout>
