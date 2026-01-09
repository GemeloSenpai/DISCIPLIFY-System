<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Detalles del Curso: {{ $curso->nombre }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Contenido del dashboard -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Descripción:</h3>
                    <p class="mb-6">{{ $curso->descripcion }} - {{ $curso->titulo }}</p>

                    <h3 class="text-lg font-semibold mb-4">Maestro(s):</h3>
                    <ul class="list-disc list-inside mb-6">
                        @foreach($maestros as $maestro)
                        <li>{{ $maestro->name }} - {{ $maestro->email }}</li>
                        @endforeach
                    </ul>

                    <h3 class="text-lg font-semibold mb-4">Alumnos:</h3>

                    <div class="w-full overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-[1000px] table-auto text-sm text-gray-700">
                            <thead class="bg-gray-100">
                                <tr class="text-center">
                                    <th class="px-4 py-2 border">ID</th>
                                    <th class="px-4 py-2 border">Nombre Completo</th>
                                    <th class="px-4 py-2 border">Entregas</th>
                                    
                                    {{-- Tareas 1 a 20 --}}
                                    @foreach($tareas as $index => $tarea)
                                    <th class="px-4 py-2 border" title="ID real: {{ $tarea->id_tareas }}">
                                        Tarea #{{ $index + 1 }}
                                    </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($alumnos as $alumno)
                                <tr class="text-center">
                                    <td class="px-4 py-2 border">{{ $alumno->id }}</td>
                                    <td class="px-4 py-2 border">{{ $alumno->nombre_completo }}</td>
                                    <td class="px-4 py-2 border">{{ $alumno->entregas_count }}</td>

                                    {{-- Columnas de tareas con checkboxes --}}
                                    @foreach($tareas as $tarea)
                                    @php
                                    $entrego = $alumno->entregas->where('tarea_id',
                                    $tarea->id_tareas)->first()?->entregada;
                                    @endphp
                                    <td class="px-2 py-1 border">
                                        <input type="checkbox" data-alumno-id="{{ $alumno->id }}"
                                            data-tarea-id="{{ $tarea->id_tareas }}" class="entrega-checkbox"
                                            {{ $entrego ? 'checked' : '' }}>
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.entrega-checkbox').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const alumnoId = this.dataset.alumnoId;
                const tareaId = this.dataset.tareaId;
                const entregada = this.checked;

                fetch("{{ route('admin.entregas.update') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            alumno_id: alumnoId,
                            tarea_id: tareaId,
                            entregada: entregada
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        console.log('Actualizado:', data);
                    })
                    .catch(err => {
                        alert('Error al actualizar la entrega');
                        this.checked = !entregada; // Revertir si falló
                    });
            });
        });
    });
    </script>
    @endpush

</x-app-layout>