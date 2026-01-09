import './bootstrap';

// Si usas Alpine con Breeze:
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

// SweetAlert2 local (sin CDN)
import Swal from 'sweetalert2';

// Notificador global:
// Por defecto: modal/toast centrado, sin botón, con temporizador
window.twinsNotify = ({
  title = 'Listo',
  text = '',
  icon = 'success',    // 'success' | 'error' | 'warning' | 'info' | 'question'
  toast = false,       // true = estilo toast; false = modal
  position = 'center', // 'center' para "en medio de pantalla"
  timer = 1800,
  showConfirmButton = false,
  ...rest
} = {}) => {
  // Ajustes por defecto "en medio"
  const base = {
    title,
    text,
    icon,
    toast,
    position,
    timer,
    showConfirmButton,
    heightAuto: false, // evita jumps por CSS
    timerProgressBar: true,
    ...rest
  };

  // Si quieres "toast pequeño", puedes usar:
  // base.toast = true; base.position = 'top-end';

  return Swal.fire(base);
};

// Atajo para eventos desde JS/AJAX: window.dispatchEvent(new CustomEvent('tw-toast', { detail: { title, icon } }))
window.addEventListener('tw-toast', (e) => {
  const { title, text = '', icon = 'success', ...rest } = e.detail || {};
  window.twinsNotify({ title, text, icon, ...rest });
});


// Detectar si es móvil y añadir clase al body
function detectMobile() {
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    if (isMobile) {
        document.body.classList.add('is-mobile');
    }
}


// Ejecutar al cargar
document.addEventListener('DOMContentLoaded', function() {
    detectMobile();
    
    // Mejorar experiencia táctil
    document.querySelectorAll('button, a').forEach(el => {
        el.addEventListener('touchstart', function() {
            this.classList.add('active-touch');
        });
        
        el.addEventListener('touchend', function() {
            this.classList.remove('active-touch');
        });
    });
});