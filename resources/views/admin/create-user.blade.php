{{-- resources/views/admin/create-user.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="font-semibold text-xl md:text-2xl text-gray-800">
                Crear usuario
            </h1>
        </div>
    </x-slot>

    @php
        $checkUrl = \Illuminate\Support\Facades\Route::has('admin.usuarios.checkEmail')
            ? route('admin.usuarios.checkEmail')
            : (\Illuminate\Support\Facades\Route::has('usuarios.checkEmail')
                ? route('usuarios.checkEmail')
                : url('/admin/usuarios/check-email'));
    @endphp

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-lg shadow-sm">
                    <ul class="list-disc pl-6">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white p-6 rounded-xl shadow">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">
                    Inserte los datos del nuevo usuario
                </h2>

                <form method="POST" action="{{ route('admin.usuarios.store') }}" class="space-y-5" novalidate>
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Nombre completo</label>
                            <input type="text" name="nombre_completo" value="{{ old('nombre_completo') }}"
                                   class="w-full border rounded px-3 py-2">
                        </div>

                        {{-- Email con verificación en vivo (rojo si existe, verde si libre) --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-1">Email *</label>
                            <div class="relative">
                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    required
                                    placeholder="usuario@dominio.com"
                                    data-check-url="{{ $checkUrl }}"
                                    class="peer w-full border rounded px-3 py-2 pr-10 transition focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                >
                                <span id="email-status" class="absolute right-3 top-1/2 -translate-y-1/2 text-sm"></span>
                            </div>
                        </div>

                        {{-- Passwords visibles (sin botón de ocultar/mostrar) --}}
                        <div>
                            <label class="block text-sm font-medium mb-1">Password *</label>
                            <input id="password" type="text" name="password" minlength="6" required
                                   class="w-full border rounded px-3 py-2" placeholder="Escribe una contraseña">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Confirmar Password *</label>
                            <input id="password_confirmation" type="text" name="password_confirmation" minlength="6" required
                                   class="w-full border rounded px-3 py-2" placeholder="Repite la contraseña">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">DNI</label>
                            <input type="text" name="DNI" value="{{ old('DNI') }}"
                                   class="w-full border rounded px-3 py-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Fecha de nacimiento</label>
                            <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}"
                                   class="w-full border rounded px-3 py-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Teléfono</label>
                            <input type="text" name="telefono" value="{{ old('telefono') }}"
                                   class="w-full border rounded px-3 py-2">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-1">Dirección</label>
                            <input type="text" name="direccion" value="{{ old('direccion') }}"
                                   class="w-full border rounded px-3 py-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Rol *</label>
                            <select name="role" id="role" class="w-full border rounded px-3 py-2" required>
                                <option value="alumno"  {{ old('role')==='alumno'  ? 'selected' : '' }}>alumno</option>
                                <option value="maestro" {{ old('role')==='maestro' ? 'selected' : '' }}>maestro</option>
                                <option value="admin"   {{ old('role')==='admin'   ? 'selected' : '' }}>admin</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Estado *</label>
                            <select name="estado" class="w-full border rounded px-3 py-2" required>
                                <option value="activo"   {{ old('estado','activo')==='activo'   ? 'selected' : '' }}>activo</option>
                                <option value="inactivo" {{ old('estado')==='inactivo'          ? 'selected' : '' }}>inactivo</option>
                            </select>
                        </div>

                        {{-- Curso solo para alumno/maestro --}}
                        <div id="curso-wrapper" class="md:col-span-2">
                            <label class="block text-sm font-medium mb-1">Curso *</label>
                            <select name="curso_id" id="curso_id" class="w-full border rounded px-3 py-2">
                                <option value="">-- Selecciona un curso --</option>
                                @foreach($cursos as $c)
                                    <option value="{{ $c->id_cursos }}" {{ old('curso_id')==$c->id_cursos ? 'selected' : '' }}>
                                        {{ $c->titulo }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Obligatorio para alumnos y maestros.</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2">
                        <a href="{{ route('admin.usuarios.index') }}" class="px-4 py-2 border rounded">Cancelar</a>
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-black px-4 py-2 rounded">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    // Mostrar/ocultar campo curso según rol
    function toggleCurso() {
        const role = document.getElementById('role').value;
        const wrap = document.getElementById('curso-wrapper');
        const sel  = document.getElementById('curso_id');
        const required = (role === 'alumno' || role === 'maestro');
        wrap.style.display = required ? '' : 'none';
        sel.required = required;
    }
    document.getElementById('role').addEventListener('change', toggleCurso);
    toggleCurso();

    // Debounce helper
    function debounce(fn, wait = 300) { let t; return (...a) => { clearTimeout(t); t=setTimeout(() => fn(...a), wait); }; }

    // Email check (verde si libre, rojo si existe)
    const emailInput  = document.getElementById('email');
    const emailStatus = document.getElementById('email-status');
    const emailHelp   = document.getElementById('email-help');
    const CHECK_URL   = emailInput.dataset.checkUrl;

    function resetEmailStyles() {
        emailInput.classList.remove(
            'ring-2','ring-rose-300','border-rose-500','focus:ring-rose-500',
            'ring-emerald-300','border-emerald-500','focus:ring-emerald-500'
        );
        emailStatus.textContent = '';
        emailStatus.className   = 'absolute right-3 top-1/2 -translate-y-1/2 text-sm';
        emailHelp.className     = 'text-xs mt-1 text-gray-500';
        emailHelp.textContent   = 'Se marcará verde si está disponible o rojo si ya existe.';
    }

    function isTaken(payload) {
        if (!payload || typeof payload !== 'object') return false;
        if (payload.exists === true) return true;
        if (payload.found  === true) return true;
        if (typeof payload.count === 'number' && payload.count > 0) return true;
        if (payload.status === 'exists') return true;
        return false;
    }

    const doCheck = debounce(async () => {
        const v = (emailInput.value || '').trim();
        if (!v) { resetEmailStyles(); return; }

        try {
            const res  = await fetch(CHECK_URL + '?email=' + encodeURIComponent(v), {
                headers: { 'Accept':'application/json' }
            });
            let data; try { data = await res.json(); } catch { data = {}; }

            emailInput.classList.remove(
                'ring-2','ring-rose-300','border-rose-500','focus:ring-rose-500',
                'ring-emerald-300','border-emerald-500','focus:ring-emerald-500'
            );

            if (isTaken(data)) {
                // ROJO: ya existe
                emailInput.classList.add('ring-2','ring-rose-300','border-rose-500','focus:ring-rose-500');
                emailStatus.textContent = '✖';
                emailStatus.className   = 'absolute right-3 top-1/2 -translate-y-1/2 text-rose-600';
                emailHelp.textContent   = 'Este correo ya existe.';
                emailHelp.className     = 'text-xs mt-1 text-rose-600';
            } else {
                // VERDE: disponible
                emailInput.classList.add('ring-2','ring-emerald-300','border-emerald-500','focus:ring-emerald-500');
                emailStatus.textContent = '✔';
                emailStatus.className   = 'absolute right-3 top-1/2 -translate-y-1/2 text-emerald-600';
                emailHelp.textContent   = 'Correo disponible.';
                emailHelp.className     = 'text-xs mt-1 text-emerald-600';
            }
        } catch (e) {
            console.error(e);
            resetEmailStyles();
            emailHelp.textContent = 'No se pudo verificar el correo (revisa conexión).';
            emailHelp.className   = 'text-xs mt-1 text-amber-600';
        }
    }, 350);

    emailInput.addEventListener('input', doCheck);
    </script>
    @endpush
</x-app-layout>
