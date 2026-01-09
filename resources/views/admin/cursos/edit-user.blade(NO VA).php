<x-app-layout>
    <div class="flex items-center justify-between gap-3">
        <h2 class="font-semibold text-xl md:text-2xl text-gray-800">
            Editar usuario
        </h2>
    </div>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if ($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <ul class="list-disc pl-6">
                    @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="bg-white p-6 rounded shadow">
                <form method="POST" action="{{ route('admin.usuarios.update', $user->id) }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Nombre (alias / corto)</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Nombre completo</label>
                            <input type="text" name="nombre_completo"
                                value="{{ old('nombre_completo', $user->nombre_completo) }}"
                                class="w-full border rounded px-3 py-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Email *</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Password (dejar vacío para no cambiar)</label>
                            <input type="password" name="password" minlength="6"
                                class="w-full border rounded px-3 py-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">DNI</label>
                            <input type="text" name="DNI" value="{{ old('DNI', $user->DNI) }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Fecha de nacimiento</label>
                            <input type="date" name="fecha_nacimiento"
                                value="{{ old('fecha_nacimiento', $user->fecha_nacimiento) }}"
                                class="w-full border rounded px-3 py-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Teléfono</label>
                            <input type="text" name="telefono" value="{{ old('telefono', $user->telefono) }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-1">Dirección</label>
                            <input type="text" name="direccion" value="{{ old('direccion', $user->direccion) }}"
                                class="w-full border rounded px-3 py-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Rol *</label>
                            <select name="role" id="role" class="w-full border rounded px-3 py-2" required>
                                <option value="alumno" {{ old('role', $user->role)==='alumno'?'selected':'' }}>alumno
                                </option>
                                <option value="maestro" {{ old('role', $user->role)==='maestro'?'selected':'' }}>maestro
                                </option>
                                <option value="admin" {{ old('role', $user->role)==='admin'?'selected':'' }}>admin
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Estado *</label>
                            <select name="estado" class="w-full border rounded px-3 py-2" required>
                                <option value="activo" {{ old('estado', $user->estado)==='activo'?'selected':'' }}>
                                    activo</option>
                                <option value="inactivo" {{ old('estado', $user->estado)==='inactivo'?'selected':'' }}>
                                    inactivo</option>
                            </select>
                        </div>

                        {{-- Selector de curso (visible si alumno/maestro) --}}
                        <div id="curso-wrapper" class="md:col-span-2">
                            <label class="block text-sm font-medium mb-1">Curso *</label>
                            <select name="curso_id" id="curso_id" class="w-full border rounded px-3 py-2">
                                <option value="">-- Selecciona un curso --</option>
                                @foreach($cursos as $c)
                                <option value="{{ $c->id_cursos }}"
                                    {{ (string)old('curso_id', $cursoActual) === (string)$c->id_cursos ? 'selected' : '' }}>
                                    {{ $c->titulo }}
                                </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Obligatorio para alumnos y maestros.</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2">
                        <a href="{{ route('admin.usuarios.index') }}" class="px-4 py-2 border rounded">Cancelar</a>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-black px-4 py-2 rounded">
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function toggleCurso() {
        const role = document.getElementById('role').value;
        const wrap = document.getElementById('curso-wrapper');
        const sel = document.getElementById('curso_id');
        const required = (role === 'alumno' || role === 'maestro');
        wrap.style.display = required ? '' : 'none';
        sel.required = required;
    }
    document.getElementById('role').addEventListener('change', toggleCurso);
    toggleCurso();
    </script>
    @endpush
</x-app-layout>