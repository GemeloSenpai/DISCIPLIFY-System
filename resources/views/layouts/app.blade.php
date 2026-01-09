<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    {{-- Todo local: Tailwind/Alpine, SweetAlert2, fuentes, etc. --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-gray-100 text-gray-900">
    <div class="min-h-screen">
        @include('layouts.navigation')

        {{-- Encabezado opcional --}}
        @isset($header)
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
        @endisset

        {{-- Contenido --}}
        <main>
            {{ $slot }}
        </main>

        @include('layouts.footer')
    </div>

    {{-- Flash/Toasts globales (éxito, error, info, warning, validaciones) --}}
    <x-flash-messages />

    @stack('scripts')
</body>

</html>