<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      Términos y Condiciones de Uso
    </h2>
  </x-slot>

  <div class="py-6">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white shadow sm:rounded-lg p-6 space-y-6 text-sm text-gray-700">

        <p><strong>Última actualización:</strong> {{ now()->format('d/m/Y') }}</p>

        <p>
          Bienvenido/a. Estos Términos regulan el uso de la plataforma de discipulado operada por
          <strong>Twins Labs</strong> (“nosotros”). Al acceder o utilizar el sistema, aceptas estos Términos.
          Si no estás de acuerdo, por favor no uses la plataforma.
        </p>

        <h3 class="font-semibold text-lg">1) Definiciones</h3>
        <ul class="list-disc ml-6 space-y-1">
          <li><strong>Plataforma:</strong> El sistema web y servicios asociados.</li>
          <li><strong>Usuario:</strong> Cualquier persona con acceso (p. ej., alumno, mentor/maestro, administrador).</li>
          <li><strong>Contenido:</strong> Información, materiales y datos cargados o generados (asistencias, tareas, calificaciones, etc.).</li>
        </ul>

        <h3 class="font-semibold text-lg">2) Cuentas y roles</h3>
        <ul class="list-disc ml-6 space-y-1">
          <li>Debes proporcionar datos veraces y mantener la confidencialidad de tus credenciales.</li>
          <li>Los <strong>roles</strong> (alumno/maestro/admin) determinan permisos y vistas disponibles.</li>
          <li>Eres responsable de toda actividad realizada desde tu cuenta.</li>
        </ul>

        <h3 class="font-semibold text-lg">3) Uso aceptable</h3>
        <ul class="list-disc ml-6 space-y-1">
          <li>No intentes vulnerar la seguridad, interferir con el servicio ni acceder a datos de terceros sin autorización.</li>
          <li>No cargues contenido ilícito, difamatorio, ofensivo o que infrinja derechos de terceros.</li>
          <li>No utilices la plataforma con fines comerciales no autorizados.</li>
        </ul>

        <h3 class="font-semibold text-lg">4) Contenido y propiedad intelectual</h3>
        <ul class="list-disc ml-6 space-y-1">
          <li>El material de cursos, evaluaciones y recursos puede estar protegido por derechos de autor y sólo es para uso educativo interno.</li>
          <li>Conservas los derechos de tu propio contenido, pero otorgas a Twins Labs una licencia limitada para almacenarlo y mostrarlo dentro de la plataforma, según sea necesario para el servicio.</li>
        </ul>

        <h3 class="font-semibold text-lg">5) Asistencias, tareas y calificaciones</h3>
        <ul class="list-disc ml-6 space-y-1">
          <li>La plataforma registra asistencias, entregas y calificaciones conforme a las reglas establecidas por los mentores/administración.</li>
          <li>La visibilidad y edición de calificaciones se rige por el rol y políticas internas.</li>
          <li>Las métricas mostradas (sumas, promedios, aprobaciones) son informativas; cualquier aclaración o apelación sigue el procedimiento académico interno.</li>
        </ul>

        <h3 class="font-semibold text-lg">6) Disponibilidad y mantenimiento</h3>
        <p>
          Buscamos mantener la plataforma disponible y segura, pero no garantizamos disponibilidad ininterrumpida.
          Podemos realizar mantenimientos, cambios o suspender funciones siempre y cuando se implemente una funcionalidad nueva y Twnis Labs y Torre Fuerte esten deacuerdo.
        </p>

        <h3 class="font-semibold text-lg">7) Privacidad y datos personales</h3>
        <p>
          El tratamiento de datos se rige por nuestra
          <a class="text-indigo-600 hover:underline" href="{{ route('privacy') }}">Política de Privacidad</a>.
          Al usar la plataforma, aceptas dicha política.
        </p>

        <h3 class="font-semibold text-lg">8) Enlaces de terceros</h3>
        <p>
          La plataforma puede contener enlaces a sitios externos. No nos responsabilizamos por sus contenidos o prácticas.
          (Aun no se agregan enlaces externos)
        </p>

        <h3 class="font-semibold text-lg">9) Limitación de responsabilidad</h3>
        <ul class="list-disc ml-6 space-y-1">
          <li>La plataforma se ofrece “tal cual”, sin garantías de disponibilidad, exactitud o idoneidad para un propósito particular.</li>
          <li>En la medida permitida por la ley, Twins Labs no será responsable por daños indirectos o pérdida de datos derivados del uso o imposibilidad de uso de la plataforma.</li>
          <li>Twins Labs solo se hara responzable si admnistra el hosting en donde se aloja la pagina.</li>
        </ul>

        <h3 class="font-semibold text-lg">10) Suspensión y terminación</h3>
        <p>
          Podemos suspender o cerrar cuentas por incumplimientos a estos Términos o por razones de seguridad.
          Tú puedes solicitar la baja de tu cuenta conforme al procedimiento interno.
        </p>

        <h3 class="font-semibold text-lg">11) Cambios a los Términos</h3>
        <p>
          Podemos modificar estos Términos. Publicaremos la fecha de actualización y, si los cambios son relevantes, intentaremos avisar por medios razonables.
          El uso continuado implica aceptación de los cambios.
        </p>

        <h3 class="font-semibold text-lg">12) Ley aplicable y jurisdicción</h3>
        <p>
          Estos Términos se rigen por las leyes aplicables de <strong>Honduras</strong>.
          Cualquier controversia se someterá a los tribunales competentes de Honduras, salvo disposiciones imperativas distintas.
        </p>

        <h3 class="font-semibold text-lg">13) Contacto</h3>
        <p>
          Twins Labs — consultas legales o soporte:
          <a class="text-indigo-600 hover:underline" href="mailto:twinslaboratories@gmail.com">twinslaboratories@gmail.com</a> ·
          <a class="text-indigo-600 hover:underline" href="tel:+50433960213">+504 3396-0213</a>.
        </p>
      </div>
    </div>
  </div>
</x-app-layout>
