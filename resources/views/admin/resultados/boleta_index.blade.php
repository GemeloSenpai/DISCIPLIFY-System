<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl md:text-2xl text-gray-800">
                Boletas — buscar alumno
            </h2>
            <a href="{{ route('admin.dashboard') }}"
               class="text-sm rounded-lg bg-slate-800 text-black px-3 py-1.5 hover:bg-slate-900">← Volver</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Filtros --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Nombre o ID</label>
                        <input type="text" name="q" value="{{ $q ?? '' }}"
                               placeholder="Ej. María Pérez o 123"
                               class="w-full rounded-lg border px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">DNI</label>
                        <input type="text" name="dni" value="{{ $dni ?? '' }}"
                               placeholder="Ej. 01020304"
                               class="w-full rounded-lg border px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Curso</label>
                        <select name="curso_id"
                                class="w-full rounded-lg border px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="">Todos</option>
                            @foreach($cursos as $c)
                                <option value="{{ $c->id_cursos }}" @selected(($cursoId ?? '')==$c->id_cursos)>{{ $c->titulo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button class="rounded-lg bg-slate-800 text-black px-4 py-2 text-sm hover:bg-slate-900 w-full">Buscar</button>
                    </div>
                </form>
                <p class="mt-2 text-[11px] text-gray-500">
                    • <b>Nombre o ID</b>: si es número busca por ID de usuario; si es texto, por nombre. <br>
                    • <b>DNI</b>: requiere que tu tabla <code>users</code> tenga la columna <code>dni</code> (si usas otro nombre, avísame y lo ajustamos).
                </p>
            </div>

            {{-- Resultados --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                @if(is_null($results))
                    <div class="text-sm text-gray-500">Usa los filtros para buscar alumnos y abrir su boleta.</div>
                @else
                    <div class="overflow-x-auto rounded-xl border border-gray-200">
                        <table class="min-w-[860px] w-full text-sm">
                            <thead class="bg-slate-100 text-slate-800">
                            <tr>
                                <th class="px-3 py-2 text-left">#</th>
                                <th class="px-3 py-2 text-left">Alumno</th>
                                <th class="px-3 py-2 text-left">DNI</th>
                                <th class="px-3 py-2 text-left">Cursos</th>
                                <th class="px-3 py-2 text-left">Email</th>
                                <th class="px-3 py-2 text-right">Acciones</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                            @forelse($results as $i => $u)
                                <tr>
                                    <td class="px-3 py-2">{{ $results->firstItem() + $i }}</td>
                                    <td class="px-3 py-2">
                                        <span class="font-medium">{{ $u->nombre_completo }}</span>
                                        <span class="text-xs text-gray-500">(#{{ $u->id }})</span>
                                    </td>
                                    <td class="px-3 py-2">{{ $u->dni ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ $u->cursos ?: '—' }}</td>
                                    <td class="px-3 py-2">{{ $u->email ?? '—' }}</td>
                                    <td class="px-3 py-2 text-right">
                                        <a href="{{ route('admin.resultados.boleta.show', ['alumno' => $u->id]) }}"
                                           class="text-xs rounded-lg bg-slate-800 text-black px-3 py-1.5 hover:bg-slate-900">
                                            Abrir boleta
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-3 py-6 text-center text-gray-500" colspan="6">Sin resultados.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $results->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
