<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a>
                        <img src="{{ asset('images/logox1.png') }}" alt="Logo Iglesia Torre Fuerte"
                            class="w-10 h-9 rounded-full shadow">
                    </a>
                </div>

                {{-- ==================== LINKS DESKTOP ==================== --}}
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @auth
                    @php($role = auth()->user()->role)

                    @if ($role === 'admin')
                    <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                        Panel Admin
                    </x-nav-link>

                    <x-nav-link :href="route('admin.cursos.gestionar')"
                        :active="request()->routeIs('admin.cursos.gestionar')">
                        Gestionar Cursos
                    </x-nav-link>

                    <x-nav-link :href="route('admin.usuarios.index')"
                        :active="request()->routeIs('admin.usuarios.index')">
                        Gestionar Usuarios
                    </x-nav-link>

                    <x-nav-link :href="route('admin.promocion.index')"
                        :active="request()->routeIs('admin.promocion.index') || request()->routeIs('admin.cursos.promocion.*')">
                        Promocionar
                    </x-nav-link>

                    <x-nav-link :href="route('admin.promocion.total')"
                        :active="request()->routeIs('admin.promocion.total*')">
                        Promoción total
                    </x-nav-link>

                    <x-nav-link :href="route('admin.promocion.graduados')"
                        :active="request()->routeIs('admin.promocion.graduados')">
                        Graduados
                    </x-nav-link>

                    <x-nav-link :href="route('admin.resultados.boleta')"
                        :active="request()->routeIs('admin.resultados.boleta')">
                        {{-- icono opcional --}}
                        <svg class="h-5 w-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M7 4h10a2 2 0 0 1 2 2v13l-4-2-4 2-4-2-4 2V6a2 2 0 0 1 2-2z" />
                        </svg>
                        <span>Boletas</span>
                    </x-nav-link>

                    @elseif ($role === 'maestro')
                    <x-nav-link :href="route('maestro.dashboard')" :active="request()->routeIs('maestro.dashboard')">
                        Panel Maestro
                    </x-nav-link>

                    <x-nav-link :href="route('maestro.curso.actual')"
                        :active="request()->routeIs('maestro.cursos.show') || request()->routeIs('maestro.curso.actual')">
                        Ver Detalles del Curso
                    </x-nav-link>

                    @php($cursoActual = session('curso_actual'))
                    <x-nav-link :href="route('maestro.calificaciones')"
                        :active="request()->routeIs('maestro.calificaciones*')">
                        Cuadro de Acumulado
                    </x-nav-link>

                    @elseif ($role === 'alumno')
                    <x-nav-link :href="route('alumno.dashboard')" :active="request()->routeIs('alumno.dashboard')">
                        Mi Panel
                    </x-nav-link>
                    <x-nav-link :href="route('alumno.cursos')" :active="request()->routeIs('alumno.cursos')">
                        Mis Clases
                    </x-nav-link>
                    @endif
                    @endauth
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Logout -->
                        <form method="POST" action="{{ route('logout') }}" id="logout-form" class="inline">
                            @csrf
                            <button type="submit"
                                class="md:hidden block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100">
                                {{ __('Log Out') }}
                            </button>
                            <button type="submit" class="hidden md:block">
                                <x-dropdown-link>
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </button>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @auth
            @php($role = auth()->user()->role)

            {{-- === ADMIN (móvil) === --}}
            @if ($role === 'admin')
            <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                Panel Admin
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('admin.cursos.gestionar')"
                :active="request()->routeIs('admin.cursos.gestionar*')">
                Gestionar Cursos
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('admin.usuarios.index')"
                :active="request()->routeIs('admin.usuarios.index*')">
                Gestionar Usuarios
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('admin.promocion.index')"
                :active="request()->routeIs('admin.promocion.index') || request()->routeIs('admin.cursos.promocion.*')">
                Promocionar
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('admin.promocion.total')"
                :active="request()->routeIs('admin.promocion.total*')">
                Promoción total
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('admin.promocion.graduados')"
                :active="request()->routeIs('admin.promocion.graduados')">
                Graduados
            </x-responsive-nav-link>

            {{-- === MAESTRO (móvil) === --}}
            @elseif ($role === 'maestro')
            <x-responsive-nav-link :href="route('maestro.dashboard')" :active="request()->routeIs('maestro.dashboard')">
                Panel Maestro
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('maestro.curso.actual')"
                :active="request()->routeIs('maestro.cursos.show') || request()->routeIs('maestro.curso.actual')">
                Ver Detalles del Curso
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('maestro.calificaciones')"
                :active="request()->routeIs('maestro.calificaciones*')">
                Cuadro de Acumulado
            </x-responsive-nav-link>

            {{-- === ALUMNO (móvil) === --}}
            @elseif ($role === 'alumno')
            <x-responsive-nav-link :href="route('alumno.dashboard')" :active="request()->routeIs('alumno.dashboard')">
                Mi Panel
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('alumno.cursos')" :active="request()->routeIs('alumno.cursos')">
                Mis Clases
            </x-responsive-nav-link>
            @endif
            @endauth
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">Usuario: {{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">Correo: {{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Ajustes del Perfil') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-100">
                        {{ __('Cerrar sesión') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>