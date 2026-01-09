<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detalle de usuario') }}
            </h2>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.usuarios.index') }}"
                   class="inline-flex items-center gap-1 text-sm px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                         d="M10 19l-7-7 7-7M3 12h18"/></svg>
                    Volver
                </a>

                <a href="{{ route('admin.usuarios.edit', $user->id) }}"
                   class="inline-flex items-center gap-1 text-sm px-3 py-1.5 rounded-md bg-indigo-600 text-black hover:bg-indigo-700 shadow">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                         d="M15.232 5.232l3.536 3.536M9 11l6-6m2-2a2.828 2.828 0 114 4l-10 10H5v-4l10-10z"/></svg>
                    Editar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-4">

            @if (session('success'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800 border border-green-200">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Tarjeta principal --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                {{-- Perfil / resumen a la izquierda (mobile arriba) --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        {{-- Avatar iniciales --}}
                        @php
                            $displayName = $user->nombre_completo ?: $user->name ?: $user->email;
                            $initials = collect(explode(' ', trim($displayName)))
                                ->filter(fn($p) => strlen($p)>0)
                                ->take(2)->map(fn($p)=>mb_strtoupper(mb_substr($p,0,1,'UTF-8'),'UTF-8'))->implode('');
                        @endphp
                        <div class="flex items-center gap-4">
                            <div class="h-14 w-14 rounded-full bg-indigo-600 text-black grid place-items-center text-lg font-semibold">
                                {{ $initials ?: 'U' }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm text-gray-500">ID: {{ $user->id }}</p>
                                <h3 class="text-lg font-semibold text-gray-900 truncate">
                                    {{ $user->nombre_completo ?? $user->name ?? '—' }}
                                </h3>
                                <div class="mt-1 flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs
                                        {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-700' :
                                           ($user->role === 'maestro' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700') }}">
                                        Rol: {{ ucfirst($user->role) }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs
                                        {{ $user->estado === 'activo' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-700' }}">
                                        {{ ucfirst($user->estado) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-sm">
                            <div class="flex items-center gap-2 text-gray-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M16 12A4 4 0 118 12a4 4 0 018 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 14v7m-7-7h14" />
                                </svg>
                                <span class="truncate">{{ $user->email }}</span>
                            </div>
                            @if($user->telefono)
                                <div class="mt-1 flex items-center gap-2 text-gray-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M3 5h2l3 7-1 2a11 11 0 0011 11l2-1 7 3v2a2 2 0 01-2 2h-1A19 19 0 013 5z" />
                                    </svg>
                                    <span>{{ $user->telefono }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Detalle en 2 columnas a la derecha --}}
                <div class="lg:col-span-2 space-y-4">
                    {{-- Datos personales --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <h4 class="text-base font-semibold text-gray-900 mb-4">Datos personales</h4>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="block text-gray-500">Nombre corto</span>
                                <span class="font-medium text-gray-900">{{ $user->name ?: '—' }}</span>
                            </div>
                            <div>
                                <span class="block text-gray-500">Nombre completo</span>
                                <span class="font-medium text-gray-900">{{ $user->nombre_completo ?: '—' }}</span>
                            </div>

                            <div>
                                <span class="block text-gray-500">DNI</span>
                                <span class="font-medium text-gray-900">{{ $user->DNI ?: '—' }}</span>
                            </div>
                            <div>
                                <span class="block text-gray-500">Fecha de nacimiento</span>
                                <span class="font-medium text-gray-900">{{ $user->fecha_nacimiento ?: '—' }}</span>
                            </div>

                            <div class="sm:col-span-2">
                                <span class="block text-gray-500">Dirección</span>
                                <span class="font-medium text-gray-900">{{ $user->direccion ?: '—' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Curso y evaluación --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-base font-semibold text-gray-900">Curso y evaluación</h4>
                            @if ($curso)
                                <span class="text-xs text-gray-500">ID curso: {{ $curso->id_cursos }}</span>
                            @endif
                        </div>

                        @if ($curso)
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                <div class="flex flex-col">
                                    <span class="text-gray-500">Curso</span>
                                    <span class="font-semibold text-gray-900">{{ $curso->titulo }}</span>
                                </div>

                                <div class="flex flex-col">
                                    <span class="text-gray-500">Nota final</span>
                                    <div class="flex items-center gap-2">
                                        @if (is_null($notaFinal))
                                            <span class="font-medium text-gray-900">Sin calificación</span>
                                        @else
                                            <span class="font-semibold text-gray-900">{{ $notaFinal }}</span>
                                            <span class="px-2 py-0.5 rounded-full text-xs
                                                {{ $notaFinal >= 70 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                {{ $estadoNota }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="rounded-lg border border-dashed border-gray-200 p-4 text-sm text-gray-600">
                                Este usuario no tiene curso asignado.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Acciones rápidas en móvil (fixed bottom, opcional) --}}
            <div class="lg:hidden">
                <div class="sticky bottom-3">
                    <div class="bg-white/90 backdrop-blur rounded-full shadow-md border border-gray-200 px-3 py-2 flex items-center justify-between">
                        <a href="{{ route('admin.usuarios.edit', $user->id) }}"
                           class="flex-1 text-center text-sm font-medium py-2 rounded-full bg-indigo-600 text-black hover:bg-indigo-700">
                            Editar usuario
                        </a>
                        <a href="{{ route('admin.usuarios.index') }}"
                           class="ml-2 flex-1 text-center text-sm font-medium py-2 rounded-full border border-gray-300 hover:bg-gray-50">
                            Volver
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
