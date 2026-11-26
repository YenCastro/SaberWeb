// public/assets/js/estudiante/resultado.js
// Filtros de la revisión: todas / incorrectas / sin responder / correctas.
document.addEventListener('DOMContentLoaded', function () {
    var botones = document.querySelectorAll('.filtro');
    var items = document.querySelectorAll('.rev-item');
    var vacio = document.getElementById('revision-vacia');
    if (!botones.length) return;

    botones.forEach(function (boton) {
        boton.addEventListener('click', function () {
            var filtro = boton.dataset.filtro;
            var visibles = 0;

            botones.forEach(function (b) {
                var activo = (b === boton);
                b.classList.toggle('activo', activo);
                b.setAttribute('aria-pressed', activo ? 'true' : 'false');
            });
            items.forEach(function (item) {
                var mostrar = (filtro === 'todas' || item.dataset.estado === filtro);
                item.hidden = !mostrar;
                if (mostrar) visibles++;
            });
            if (vacio) vacio.hidden = (visibles > 0);
        });
    });
});
