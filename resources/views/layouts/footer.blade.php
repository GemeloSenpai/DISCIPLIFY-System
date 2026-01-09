<footer class="bg-white border-t">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-sm text-gray-600
              flex flex-col sm:flex-row items-center justify-between gap-2">
    <p class="text-center sm:text-left">
      &copy; {{ now()->year }} Twins Labs. Todos los derechos reservados.
    </p>

    <address class="not-italic flex flex-col sm:flex-row items-center gap-3">
      <a href="tel:+50433960213" class="hover:text-gray-800" aria-label="Llamar al +504 3396 0213">
        Contacto: +504&nbsp;3396-0213
      </a>
      <span class="hidden sm:inline">·</span>
      <a href="mailto:twinslaboratories@gmail.com" class="hover:text-gray-800"
         aria-label="Enviar correo a twinslaboratories@gmail.com">
        Correo: twinslaboratories@gmail.com
      </a>
      @if (Route::has('privacy'))
        <span class="hidden sm:inline">·</span>
        <a href="{{ route('privacy') }}" class="hover:text-gray-800">Política de privacidad</a>
      @endif
      @if (Route::has('terms'))
        <span class="hidden sm:inline">·</span>
        <a href="{{ route('terms') }}" class="hover:text-gray-800">Términos</a>
      @endif
    </address>
  </div>
</footer>
