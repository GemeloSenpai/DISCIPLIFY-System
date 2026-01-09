<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Discipulado</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <style>
    body {
        background-image: url('{{ asset('images/fondo1.png') }}');
        background-repeat: repeat;
        background-position: center;
    }

    .glass {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(12px) saturate(180%);
        -webkit-backdrop-filter: blur(12px) saturate(180%);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        border-radius: 1rem;
    }

    .input-wrapper {
        position: relative;
    }

    .eye-button {
        position: absolute;
        top: 50%;
        right: 10px;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
    }

    .eye-button svg {
        width: 24px;
        height: 24px;
        color: #6B7280;
    }

    @keyframes fade-in {
        0% {
            opacity: 0;
            transform: translateY(20px);
        }

        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in {
        animation: fade-in 0.8s ease-out forwards;
    }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center px-4 py-8">
    <div class="w-full max-w-md p-6 space-y-6 glass animate-fade-in">
        <div class="flex flex-col items-center space-y-2">
            <img src="{{ asset('images/logox1.png') }}" alt="Logo" class="w-20 h-20 rounded-full shadow-lg">
            <h1 class="text-xl font-bold text-gray-800 text-center leading-tight">
                Ministerio Apostólico y Profético <br> <span class="text-indigo-600">Torre Fuerte</span>
            </h1>
            <p class="text-sm text-gray-600 text-center">Bienvenido al sistema de gestión de discipulados</p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
                <input id="email" type="email" name="email" required autofocus
                    class="w-full h-12 px-4 py-2 border border-gray-300 rounded-md text-base focus:ring-indigo-500 focus:border-indigo-500"
                    value="{{ old('email') }}">
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                <div class="input-wrapper">
                    <input id="password" type="password" name="password" required
                        class="w-full h-12 px-4 py-2 pr-12 border border-gray-300 rounded-md text-base focus:ring-indigo-500 focus:border-indigo-500">
                    <button type="button" class="eye-button" onclick="togglePassword()" title="Mostrar/Ocultar">
                        <svg id="eyeClosed" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-5.523 0-10-4-10-7s4.477-7 10-7c2.385 0 4.577.93 6.225 2.475M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />
                        </svg>
                        <svg id="eyeOpen" class="hidden" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7s-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex justify-center">
                <button type="submit"
                    class="w-full h-12 py-2 bg-indigo-600 text-white font-semibold rounded-md hover:bg-indigo-700 transition">
                    Iniciar sesión
                </button>
            </div>

            <div class="pt-4 text-xs text-gray-600 text-center italic border-t border-gray-200 mt-4">
                “Por tanto, id, y haced discípulos a todas las naciones, bautizándolos en el nombre del Padre,
                y del Hijo, y del Espíritu Santo; enseñándoles que guarden todas las cosas que os he mandado.”<br>
                <strong>Mateo 28:19-20</strong>
            </div>
        </form>
    </div>

    <script>
    function togglePassword() {
        const input = document.getElementById("password");
        const eyeOpen = document.getElementById("eyeOpen");
        const eyeClosed = document.getElementById("eyeClosed");

        if (input.type === "password") {
            input.type = "text";
            eyeClosed.classList.add("hidden");
            eyeOpen.classList.remove("hidden");
        } else {
            input.type = "password";
            eyeClosed.classList.remove("hidden");
            eyeOpen.classList.add("hidden");
        }
    }
    </script>
</body>

</html>