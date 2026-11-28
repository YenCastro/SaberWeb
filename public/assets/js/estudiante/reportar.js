// public/assets/js/estudiante/reportar.js
// Contador de caracteres y botón "Enviar" que se bloquea mientras se envía (evita doble clic).
document.addEventListener('DOMContentLoaded', function () {
    var texto = document.getElementById('descripcion');
    var contador = document.getElementById('contador');
    var form = document.getElementById('form-reporte');
    var boton = document.getElementById('btn-enviar');
    if (!texto || !contador) return;

    function actualizar() {
        contador.textContent = texto.value.length;
    }
    texto.addEventListener('input', actualizar);
    actualizar();

    form.addEventListener('submit', function (evento) {
        if (texto.value.trim().length < 10) {
            evento.preventDefault();
            texto.focus();
            return;
        }
        boton.disabled = true;
        boton.textContent = 'Enviando…';
    });
});
