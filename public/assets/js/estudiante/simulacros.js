// public/assets/js/estudiante/simulacros.js
// Pide confirmación antes de enviar formularios con data-confirm="mensaje" (ej: comenzar un simulacro).
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (evento) {
            if (!window.confirm(form.dataset.confirm)) {
                evento.preventDefault();
            }
        });
    });
});
