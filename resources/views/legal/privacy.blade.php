<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      Política de Privacidad
    </h2>
  </x-slot>

  <div class="py-6">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white shadow sm:rounded-lg p-6 space-y-6 text-sm text-gray-700">
        <p><strong>Última actualización:</strong> {{ now()->format('d/m/Y') }}</p>

        <h3 class="font-semibold text-lg">1) Quiénes somos</h3>
        <p>
          Esta plataforma es operada por <strong>Twins Labs</strong> (“nosotros”).
          Contacto: <a class="text-indigo-600 hover:underline" href="mailto:twinslaboratories@gmail.com">twinslaboratories@gmail.com</a>,
          Tel: <a class="text-indigo-600 hover:underline" href="tel:+50433960213">+504 3396-0213</a>.
        </p>

        <h3 class="font-semibold text-lg">2) Ámbito</h3>
        <p>
          Esta política aplica a los datos personales tratados en el sistema de discipulado (cursos, sesiones, tareas,
          calificaciones y asistencias) y a la navegación en el sitio/app.
        </p>

        <h3 class="font-semibold text-lg">3) Datos que recolectamos</h3>
        <ul class="list-disc ml-6 space-y-1">
          <li><strong>Cuenta:</strong> nombre, correo, teléfono, rol.</li>
          <li><strong>Académicos:</strong> cursos inscritos, sesiones, asistencias, tareas, calificaciones, comentarios.</li>
          <li><strong>Técnicos:</strong> IP, dispositivo/navegador, identificadores de sesión, logs y cookies.</li>
        </ul>

        <h3 class="font-semibold text-lg">4) Finalidades y base legal</h3>
        <ul class="list-disc ml-6 space-y-1">
          <li><em>Prestar el servicio</em> (gestionar cursos, asistencias, calificaciones) — <strong>ejecución de contrato</strong>.</li>
          <li><em>Comunicación operativa</em> (avisos de clase/tareas) — <strong>ejecución de contrato/interés legítimo</strong>.</li>
          <li><em>Seguridad</em> (detección de abuso, auditoría) — <strong>interés legítimo</strong>.</li>
          <li><em>Analítica básica</em> — <strong>consentimiento/interés legítimo</strong> (según la herramienta).</li>
        </ul>

        <h3 class="font-semibold text-lg">5) Conservación</h3>
        <ul class="list-disc ml-6 space-y-1">
          <li>El Software es propiedad de Twins Labs.</li>
          <li>La Institucion Torre Fuerte puede gozar del uso del sistema y sus servicios ilimitadamente.</li>
          <li>Twins Labs se compromete a no dar de baja a los servicios ofrecidos por el sistema siempre y cuando no se comercialize el codigo fuente que pertenece a Twins Labs.</li>
        </ul>

        <h3 class="font-semibold text-lg">6) Destinatarios y encargados</h3>
        <p>
          No vendemos datos. Podemos compartirlos con proveedores que nos dan infraestructura o funciones (alojamiento,
          correo, monitorización, analítica) bajo acuerdos de confidencialidad y tratamiento de datos.
          Si la ley lo exige, podremos atender requerimientos de autoridades.
        </p>

        <h3 class="font-semibold text-lg">7) Transferencias internacionales</h3>
        <p>
          Si nuestros proveedores alojan datos fuera de tu país, aplicamos salvaguardas contractuales razonables.
          Puedes consultarnos para conocer la lista actualizada de proveedores.
        </p>

        <h3 class="font-semibold text-lg">8) Cookies</h3>
        <p>
          Usamos cookies necesarias para el login y funcionamiento. Las analíticas (si se habilitan) ayudan a entender el uso.
          Puedes gestionar cookies desde la configuración de tu navegador.
        </p>

        <h3 class="font-semibold text-lg">9) Derechos</h3>
        <p>
          Puedes solicitar acceso, rectificación, eliminación, oposición o portabilidad de tus datos, y revocar el consentimiento cuando corresponda,
          escribiéndonos a <a class="text-indigo-600 hover:underline" href="mailto:twinslaboratories@gmail.com">twinslaboratories@gmail.com</a>.
          Responderemos en plazos razonables conforme a las <strong>leyes aplicables</strong>.
        </p>

        <h3 class="font-semibold text-lg">10) Seguridad</h3>
        <p>
          Aplicamos medidas técnicas y organizativas razonables (control de accesos, cifrado en tránsito, backups).
          Ningún sistema es 100% infalible; notificaremos incidentes conforme a la normativa aplicable.
        </p>

        <h3 class="font-semibold text-lg">11) Menores</h3>
        <p>
          Si gestionamos datos de menores, lo haremos con autorización del responsable correspondiente (p. ej., tutor o institución).
        </p>

        <h3 class="font-semibold text-lg">12) Cambios</h3>
        <p>
          Podremos actualizar esta política. Publicaremos la fecha de la última modificación y, si los cambios son sustanciales,
          lo comunicaremos por medios razonables.
        </p>

        <h3 class="font-semibold text-lg">13) Contacto</h3>
        <p>
          Dudas o solicitudes sobre privacidad: 
          <a class="text-indigo-600 hover:underline" href="mailto:twinslaboratories@gmail.com">twinslaboratories@gmail.com</a> ·
          <a class="text-indigo-600 hover:underline" href="tel:+50433960213">+504 3396-0213</a>.
        </p>
      </div>
    </div>
  </div>
</x-app-layout>
