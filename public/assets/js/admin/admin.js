// public/assets/js/admin/admin.js
// Comportamiento común del panel de administrador.
document.addEventListener('DOMContentLoaded', function () {

    // 1) Confirmación antes de enviar formularios con data-confirm="mensaje"
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (evento) {
            if (!window.confirm(form.dataset.confirm)) {
                evento.preventDefault();
            }
        });
    });

    // 2) Sesiones dinámicas en "Crear simulacro"
    var lista = document.getElementById('lista-sesiones');
    if (!lista) return;

    var btnAgregar = document.getElementById('btn-agregar-sesion');
    var selectTipo = document.getElementById('tipo');
    var plantillas = {
        ICFES: ['Sesión 1', 'Sesión 2'],
        UNAL:  ['Examen completo']
    };

    function crearFila(titulo, duracion) {
        var fila = document.createElement('div');
        fila.className = 'sesion-fila';
        fila.innerHTML =
            '<input type="text" name="sesion_titulo[]" maxlength="200" placeholder="Nombre de la sesión" required>' +
            '<input type="number" name="sesion_duracion[]" min="1" max="600" required aria-label="Duración en minutos">' +
            '<span class="sesion-unidad">min</span>' +
            '<button type="button" class="btn-icono btn-quitar" title="Quitar sesión"><i class="fa-solid fa-xmark"></i></button>';
        fila.children[0].value = titulo || '';
        fila.children[1].value = duracion || 270;
        fila.querySelector('.btn-quitar').addEventListener('click', function () {
            if (lista.children.length > 1) {
                fila.remove();
            }
        });
        lista.appendChild(fila);
    }

    function cargarPlantilla(tipo) {
        lista.innerHTML = '';
        (plantillas[tipo] || ['Sesión 1']).forEach(function (titulo) {
            crearFila(titulo, 270);
        });
    }

    btnAgregar.addEventListener('click', function () {
        crearFila('Sesión ' + (lista.children.length + 1), 270);
    });
    selectTipo.addEventListener('change', function () {
        cargarPlantilla(selectTipo.value);
    });

    cargarPlantilla(selectTipo.value);
});
