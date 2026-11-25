// public/assets/js/estudiante/realizar_simulacro.js
// Examen: una pregunta a la vez, temporizador, navegación y guardado automático de cada respuesta.
(function () {
    'use strict';

    var datos = JSON.parse(document.getElementById('datos-examen').textContent);
    var preguntas = datos.preguntas;
    var respuestas = datos.respuestas || {};   // { idPregunta: idOpcion }
    var actual = 0;
    var terminado = false;
    // El tiempo restante lo calculó el servidor. Usamos Date.now() para no depender de que el navegador "duerma" el reloj.
    var finMs = Date.now() + datos.segundos * 1000;

    var el = {
        area: document.getElementById('area'),
        enunciado: document.getElementById('enunciado'),
        opciones: document.getElementById('opciones'),
        numActual: document.getElementById('num-actual'),
        respondidas: document.getElementById('contador-respondidas'),
        navegador: document.getElementById('navegador'),
        anterior: document.getElementById('btn-anterior'),
        siguiente: document.getElementById('btn-siguiente'),
        finalizar: document.getElementById('btn-finalizar'),
        form: document.getElementById('form-finalizar'),
        tiempo: document.getElementById('tiempo'),
        temporizador: document.getElementById('temporizador'),
        aviso: document.getElementById('aviso')
    };

    function totalRespondidas() {
        return Object.keys(respuestas).length;
    }

    // ---------- Avisos ----------
    function mostrarAviso(texto, tipo) {
        el.aviso.textContent = texto;
        el.aviso.className = 'aviso' + (tipo ? ' aviso-' + tipo : '');
        el.aviso.hidden = false;
    }
    function ocultarAviso() {
        el.aviso.hidden = true;
    }

    // ---------- Navegador de preguntas ----------
    function construirNavegador() {
        el.navegador.innerHTML = '';
        preguntas.forEach(function (p, i) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'nav-num';
            b.textContent = p.numero;
            b.setAttribute('aria-label', 'Ir a la pregunta ' + p.numero);
            b.addEventListener('click', function () { mostrar(i); });
            el.navegador.appendChild(b);
        });
    }

    function refrescarNavegador() {
        Array.prototype.forEach.call(el.navegador.children, function (b, i) {
            b.classList.toggle('respondida', respuestas[preguntas[i].id] !== undefined);
            b.classList.toggle('actual', i === actual);
        });
        el.respondidas.textContent = totalRespondidas();
    }

    // ---------- Mostrar una pregunta ----------
    function mostrar(indice) {
        if (indice < 0 || indice >= preguntas.length) return;
        actual = indice;
        var p = preguntas[indice];

        el.numActual.textContent = indice + 1;
        el.area.textContent = p.area;
        el.enunciado.textContent = p.enunciado;     // textContent: nunca interpreta HTML

        el.opciones.innerHTML = '';
        p.opciones.forEach(function (o) {
            var label = document.createElement('label');
            var marcada = respuestas[p.id] === o.id;
            label.className = 'opcion' + (marcada ? ' sel' : '');

            var input = document.createElement('input');
            input.type = 'radio';
            input.name = 'opcion';
            input.value = o.id;
            input.checked = marcada;
            input.addEventListener('change', function () { responder(p.id, o.id); });

            var letra = document.createElement('span');
            letra.className = 'letra';
            letra.textContent = o.letra;

            var texto = document.createElement('span');
            texto.className = 'texto';
            texto.textContent = o.texto;

            label.appendChild(input);
            label.appendChild(letra);
            label.appendChild(texto);
            el.opciones.appendChild(label);
        });

        el.anterior.disabled = (indice === 0);
        el.siguiente.disabled = (indice === preguntas.length - 1);
        refrescarNavegador();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // ---------- Guardar respuesta ----------
    function responder(idPregunta, idOpcion) {
        if (terminado) return;
        var previa = respuestas[idPregunta];
        respuestas[idPregunta] = idOpcion;
        marcarSeleccion(idOpcion);
        refrescarNavegador();

        var fd = new FormData();
        fd.append('csrf', datos.csrf);
        fd.append('id_resultado', datos.idResultado);
        fd.append('id_pregunta', idPregunta);
        fd.append('id_opcion', idOpcion);

        fetch(datos.urlGuardar, {
            method: 'POST',
            body: fd,
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        })
        .then(function (r) { return r.json(); })
        .then(function (j) {
            if (j.ok) { ocultarAviso(); return; }
            deshacer(idPregunta, previa);
            if (j.expirado) { terminar(); }
            else { mostrarAviso(j.mensaje || 'No se pudo guardar tu respuesta. Inténtalo de nuevo.', 'error'); }
        })
        .catch(function () {
            deshacer(idPregunta, previa);
            mostrarAviso('No se pudo guardar tu respuesta. Revisa tu conexión e inténtalo de nuevo.', 'error');
        });
    }

    function marcarSeleccion(idOpcion) {
        Array.prototype.forEach.call(el.opciones.children, function (label) {
            label.classList.toggle('sel', Number(label.querySelector('input').value) === idOpcion);
        });
    }

    // Si el servidor no pudo guardar, la pantalla vuelve a lo que sí está guardado
    function deshacer(idPregunta, previa) {
        if (previa === undefined) { delete respuestas[idPregunta]; }
        else { respuestas[idPregunta] = previa; }
        if (preguntas[actual].id === idPregunta) { mostrar(actual); } else { refrescarNavegador(); }
    }

    // ---------- Temporizador ----------
    function formato(s) {
        var h = Math.floor(s / 3600), m = Math.floor((s % 3600) / 60), seg = s % 60;
        function dos(n) { return (n < 10 ? '0' : '') + n; }
        return dos(h) + ':' + dos(m) + ':' + dos(seg);
    }

    function tick() {
        var s = Math.max(0, Math.round((finMs - Date.now()) / 1000));
        el.tiempo.textContent = formato(s);
        el.temporizador.classList.toggle('timer-aviso', s <= 600 && s > 300);
        el.temporizador.classList.toggle('timer-critico', s <= 300);
        if (s <= 0) { terminar(); }
    }

    // ---------- Terminar ----------
    function terminar() {
        if (terminado) return;
        terminado = true;
        mostrarAviso('Se acabó el tiempo. Enviando tus respuestas…', 'info');
        el.form.submit();
    }

    el.finalizar.addEventListener('click', function () {
        var faltan = preguntas.length - totalRespondidas();
        var mensaje = faltan > 0
            ? 'Te faltan ' + faltan + ' pregunta(s) sin responder. ¿Quieres finalizar de todos modos?'
            : 'Respondiste todas las preguntas. ¿Finalizar el examen?';
        if (window.confirm(mensaje) && !terminado) {
            terminado = true;
            el.form.submit();
        }
    });

    el.anterior.addEventListener('click', function () { mostrar(actual - 1); });
    el.siguiente.addEventListener('click', function () { mostrar(actual + 1); });

    // Flechas del teclado para moverse entre preguntas
    document.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowLeft') mostrar(actual - 1);
        if (e.key === 'ArrowRight') mostrar(actual + 1);
    });

    // ---------- Inicio ----------
    construirNavegador();
    mostrar(0);
    tick();
    setInterval(tick, 1000);
})();
