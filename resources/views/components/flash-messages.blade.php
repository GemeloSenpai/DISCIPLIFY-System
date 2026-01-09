@php
    // Normalizamos claves comunes de Laravel
    $flash = [
        'success' => session('success') ?? session('status'),
        'error'   => session('error'),
        'warning' => session('warning'),
        'info'    => session('info'),
    ];
@endphp

<script>
    // Disparador global desde Blade (sesiones)
    document.addEventListener('DOMContentLoaded', () => {
        const fire = (title, icon = 'info', options = {}) => {
            if (!window.twinsNotify) return;
            window.twinsNotify({ title, icon, ...options });
        };

        @foreach ($flash as $type => $msg)
            @if (!empty($msg))
                fire(@json($msg), @json($type));
            @endif
        @endforeach

        // Validaciones: mostramos la primera (o concatena si prefieres)
        @if ($errors->any())
            fire(@json($errors->first()), 'error');
        @endif
    });
</script>
