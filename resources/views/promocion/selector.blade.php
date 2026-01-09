<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl md:text-2xl text-gray-800">
                Promoción de alumnos — Seleccionar curso
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-4">
                    <div>
                        <label for="curso_select" class="block text-sm font-medium text-gray-700 mb-1">
                            Curso
                        </label>
                        <select id="curso_select"
                            class="w-full max-w-xl border rounded px-3 py-2 focus:outline-none focus:ring"
                            aria-label="Selecciona un curso">
                            <option value="">— Selecciona —</option>
                            @foreach($cursos as $c)
                            <option value="{{ $c->id_cursos }}">{{ $c->titulo }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <button id="btn_ir" class="bg-indigo-600 hover:bg-indigo-700 text-black px-4 py-2 rounded">
                            Ir a promoción
                        </button>
                    </div>

                    <p class="text-xs text-gray-500">
                        Luego podrás marcar qué alumnos se inscriben en el curso destino.
                    </p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    (function() {
        const sel = document.getElementById('curso_select');
        const btn = document.getElementById('btn_ir');
        btn?.addEventListener('click', () => {
            const id = sel?.value;
            if (!id) {
                alert('Selecciona un curso.');
                return;
            }
            // Redirige al formulario de promoción del curso elegido
            window.location.href = "{{ url('/admin/cursos') }}/" + id + "/promocion";
        });
    })();
    </script>
    @endpush
</x-app-layout>