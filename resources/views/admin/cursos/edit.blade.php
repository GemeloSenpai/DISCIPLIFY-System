<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl md:text-2xl text-gray-800">
                Editar Curso
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto bg-white shadow rounded-lg p-6">
            <form method="POST" action="{{ route('admin.cursos.update', $curso->id_cursos) }}">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <x-input-label for="titulo" value="Título del curso" />
                    <x-text-input type="text" name="titulo" id="titulo" class="mt-1 block w-full"
                        value="{{ old('titulo', $curso->titulo) }}" required autofocus />
                    <x-input-error :messages="$errors->get('titulo')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <x-input-label for="descripcion" value="Descripción" />
                    <textarea name="descripcion" id="descripcion"
                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                        rows="3">{{ old('descripcion', $curso->descripcion) }}</textarea>
                    <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
                </div>

                <div class="flex justify-end">
                    <x-primary-button>Actualizar Curso</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>