{{-- resources/views/admin/cursos/gestionar-cursos.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl md:text-2xl text-gray-800">
                Gestionar Cursos
            </h2>
        </div>
    </x-slot>

    <div class="py-4 md:py-6">
        <div class="mx-auto w-full max-w-7xl px-3 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 md:p-6">
                    {{-- Mensaje de éxito --}}
                    @if (session('success'))
                        <div class="mb-4 md:mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg shadow-sm text-sm md:text-base flex items-center gap-2">
                            <span class="text-lg" aria-hidden="true">✅</span>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    {{-- Botón crear nuevo curso --}}
                    <div class="flex justify-center">
                        <div class="w-full flex justify-start mb-3 md:mb-4">
                            <button
                                onclick="document.getElementById('modal-crear').classList.remove('hidden')"
                                class="inline-flex items-center gap-2 bg-indigo-600 text-white px-4 md:px-5 py-2 md:py-2.5 rounded-lg hover:bg-indigo-700 text-sm font-semibold shadow transition active:scale-95 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <span class="text-base" aria-hidden="true">➕</span>
                                <span>Crear nuevo curso</span>
                            </button>
                        </div>
                    </div>

                    {{-- ====== LISTA MÓVIL (tarjetas) ====== --}}
                    <div class="md:hidden space-y-3">
                        @forelse($cursos as $curso)
                            <div class="rounded-xl border border-gray-200 p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="text-xs text-gray-500">ID #{{ $curso->id_cursos }}</div>
                                        <h3 class="text-base font-semibold text-gray-900 truncate">
                                            {{ $curso->titulo }}
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-600 line-clamp-2">
                                            {{ Str::limit($curso->descripcion, 120) }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    {{-- Editar --}}
                                    <a href="{{ route('admin.cursos.edit', $curso->id_cursos) }}"
                                       class="flex-1 min-w-[44%] text-center bg-indigo-600 text-white px-3 py-2 rounded-lg text-sm font-semibold shadow hover:bg-indigo-700 active:scale-95 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                        ✏️ Editar
                                    </a>

                                    {{-- Eliminar --}}
                                    <form action="{{ route('admin.cursos.destroy', $curso->id_cursos) }}" method="POST" class="flex-1 min-w-[44%]">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                onclick="return confirm('¿Estás seguro de eliminar este curso?')"
                                                class="w-full bg-rose-600 text-white px-3 py-2 rounded-lg text-sm font-semibold shadow hover:bg-rose-700 active:scale-95 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2">
                                            🗑️ Eliminar
                                        </button>
                                    </form>

                                    {{-- Asignar / Quitar Docentes --}}
                                    <button
                                        onclick="abrirModalDocentes({{ $curso->id_cursos }}, '{{ addslashes($curso->titulo) }}', this)"
                                        data-asignados='@json($curso->maestros->pluck("id"))'
                                        class="w-full bg-emerald-600 text-white px-3 py-2 rounded-lg text-sm font-semibold shadow hover:bg-emerald-700 active:scale-95 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                                        👩‍🏫 Asignar / Quitar docentes
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-gray-500 py-6">No hay cursos registrados.</div>
                        @endforelse
                    </div>

                    {{-- ====== TABLA ESCRITORIO ====== --}}
                    <div class="hidden md:block">
                        <div class="overflow-x-auto rounded-lg border border-gray-200">
                            <table class="min-w-[900px] w-full divide-y divide-gray-200 text-sm text-gray-800">
                                <thead class="bg-indigo-600 text-white sticky top-0 z-10">
                                    <tr class="text-center font-semibold">
                                        <th class="px-4 py-3">ID</th>
                                        <th class="px-4 py-3 text-left">Nombre</th>
                                        <th class="px-4 py-3 text-left">Descripción</th>
                                        <th class="px-4 py-3">Acciones</th>
                                        <th class="px-4 py-3">Docentes</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @forelse($cursos as $curso)
                                        <tr class="text-center hover:bg-gray-50">
                                            <td class="px-4 py-3">{{ $curso->id_cursos }}</td>
                                            <td class="px-4 py-3 text-left font-medium">{{ $curso->titulo }}</td>
                                            <td class="px-4 py-3 text-left">{{ Str::limit($curso->descripcion, 90) }}</td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center justify-center gap-2">
                                                    <a href="{{ route('admin.cursos.edit', $curso->id_cursos) }}"
                                                       class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg shadow transition text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                                        ✏️ Editar
                                                    </a>
                                                    <form action="{{ route('admin.cursos.destroy', $curso->id_cursos) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                onclick="return confirm('¿Estás seguro de eliminar este curso?')"
                                                                class="inline-flex items-center gap-2 bg-rose-600 hover:bg-rose-700 text-white px-3 py-1.5 rounded-lg shadow transition text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2">
                                                            🗑️ Eliminar
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <button
                                                    onclick="abrirModalDocentes({{ $curso->id_cursos }}, '{{ addslashes($curso->titulo) }}', this)"
                                                    data-asignados='@json($curso->maestros->pluck("id"))'
                                                    class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg text-sm font-semibold shadow focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                                                    👩‍🏫 Asignar / Quitar
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-gray-500 py-6">No hay cursos registrados.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Modal Crear Curso --}}
            <div id="modal-crear" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 hidden p-3">
                <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl p-5 relative">
                    <h3 class="text-lg md:text-xl font-semibold mb-4 text-gray-800 flex items-center gap-2">
                        <span class="text-lg" aria-hidden="true">📘</span> Nuevo Curso
                    </h3>

                    <form method="POST" action="{{ route('admin.cursos.store') }}" class="space-y-3">
                        @csrf
                        <div>
                            <label for="titulo" class="block text-sm font-medium text-gray-700">Título</label>
                            <input type="text" name="titulo" id="titulo" required
                                   class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label for="descripcion" class="block text-sm font-medium text-gray-700">Descripción</label>
                            <textarea name="descripcion" id="descripcion" rows="3"
                                      class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button"
                                    onclick="document.getElementById('modal-crear').classList.add('hidden')"
                                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2">
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                💾 Guardar
                            </button>
                        </div>
                    </form>

                    <button
                        onclick="document.getElementById('modal-crear').classList.add('hidden')"
                        class="absolute top-3 right-4 text-gray-500 hover:text-gray-700 text-2xl leading-none"
                        aria-label="Cerrar">
                        &times;
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Modal Docentes (bottom-sheet en móvil, centrado en desktop) ===== --}}
    <div id="modal-docentes" class="fixed inset-0 z-50 hidden items-end md:items-center justify-center">
        <button type="button" class="absolute inset-0 bg-black/40" onclick="cerrarModalDocentes()" aria-label="Cerrar overlay"></button>

        <div class="relative w-full md:w-auto md:max-w-md bg-white rounded-t-2xl md:rounded-2xl shadow-2xl p-4 md:p-5 max-h-[84vh] md:max-h-[86vh] overflow-hidden">
            <div class="mx-auto mb-2 h-1.5 w-10 rounded-full bg-gray-300 md:hidden"></div>

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-1">
                <h3 class="text-base md:text-lg font-semibold text-gray-800">
                    👩‍🏫 Asignar docentes <span id="curso-id" class="text-xs text-gray-500"></span>
                </h3>
                <p id="curso-titulo" class="text-sm text-gray-600 md:ml-3 truncate"></p>
                <button type="button" class="text-gray-500 hover:text-gray-700 text-xl md:ml-auto" onclick="cerrarModalDocentes()" aria-label="Cerrar">×</button>
            </div>

            <form id="form-docentes" method="POST" action="#" class="mt-3 flex flex-col gap-3">
                @csrf
                <div class="rounded-lg border overflow-y-auto h-[58vh] md:h-[50vh] overscroll-contain touch-pan-y">
                    <div class="divide-y">
                        @isset($maestros)
                            @foreach ($maestros as $m)
                                @php $ocupado = $ocupados[$m->id] ?? null; @endphp
                                <label class="flex items-center gap-3 p-3 text-sm">
                                    <input type="checkbox" name="maestros[]" value="{{ $m->id }}" class="chk-maestro h-5 w-5" data-ocupado-en="{{ $ocupado ?? '' }}">
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium truncate">{{ $m->nombre_completo ?? $m->name }}</div>
                                        <div class="text-xs text-gray-500 truncate">{{ $m->email }}</div>
                                    </div>
                                    <span class="shrink-0 text-[10px] px-2 py-0.5 rounded {{ $ocupado ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">
                                        {{ $ocupado ? "Ocupado #$ocupado" : 'Disponible' }}
                                    </span>
                                </label>
                            @endforeach
                        @else
                            <div class="p-4 text-sm text-gray-500">
                                No se cargó la lista de docentes. Asegúrate de pasar <code>$maestros</code> y <code>$ocupados</code> desde el controlador.
                            </div>
                        @endisset
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="button" onclick="cerrarModalDocentes()" class="flex-1 px-4 py-2 rounded-lg bg-gray-200 text-gray-800 font-medium hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 rounded-lg bg-emerald-600 text-white font-semibold hover:bg-emerald-700 active:scale-95 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                        💾 Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        const modalDoc = document.getElementById('modal-docentes');
        const formDoc  = document.getElementById('form-docentes');

        function abrirModalDocentes(cursoId, titulo, btn) {
            @php
                $route = \Illuminate\Support\Facades\Route::has('admin.cursos.maestros.sync')
                       ? route('admin.cursos.maestros.sync', ':id')
                       : '/admin/cursos/:id/asignar-maestros';
            @endphp
            formDoc.action = "{{ $route }}".replace(':id', cursoId);

            // Títulos
            document.getElementById('curso-id').textContent = `(#${cursoId})`;
            document.getElementById('curso-titulo').textContent = titulo;

            // Reset checks & estados
            formDoc.querySelectorAll('.chk-maestro').forEach(chk => {
                chk.checked = false;
                chk.disabled = false;
                const label = chk.closest('label');
                label && label.classList.remove('opacity-50');

                const ocupadoEn = chk.getAttribute('data-ocupado-en');
                if (ocupadoEn && parseInt(ocupadoEn) !== parseInt(cursoId)) {
                    chk.disabled = true;
                    label && label.classList.add('opacity-50');
                }
            });

            // Marcar los ya asignados
            const asignados = JSON.parse(btn.getAttribute('data-asignados') || '[]');
            asignados.forEach(id => {
                const chk = formDoc.querySelector(`.chk-maestro[value="${id}"]`);
                if (chk) chk.checked = true;
            });

            modalDoc.classList.remove('hidden');
            modalDoc.classList.add('flex');
        }

        function cerrarModalDocentes() {
            modalDoc.classList.add('hidden');
            modalDoc.classList.remove('flex');
        }
    </script>
    @endpush
</x-app-layout>
