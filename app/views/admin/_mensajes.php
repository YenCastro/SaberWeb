<?php
// app/views/admin/_mensajes.php
// Muestra los avisos (?mensaje=... / ?error=...) que deja AdminController al redirigir.
$textosOk = [
    'prueba_creada'        => 'Simulacro creado. Ahora agrega las preguntas de cada sesión.',
    'prueba_actualizada'   => 'Datos del simulacro actualizados.',
    'prueba_eliminada'     => 'Simulacro eliminado.',
    'publicada'            => 'Simulacro publicado: ya está disponible para los estudiantes.',
    'despublicada'         => 'Simulacro despublicado: los estudiantes ya no lo ven.',
    'sesion_creada'        => 'Sesión agregada.',
    'sesion_actualizada'   => 'Sesión actualizada.',
    'sesion_eliminada'     => 'Sesión eliminada.',
    'pregunta_creada'      => 'Pregunta guardada.',
    'pregunta_actualizada' => 'Pregunta actualizada.',
    'pregunta_eliminada'   => 'Pregunta eliminada. Las demás se renumeraron.',
];
$textosError = [
    'solicitud_invalida'   => 'La solicitud no es válida. Intenta de nuevo.',
    'datos_invalidos'      => 'Revisa los datos: título obligatorio, tipo ICFES o UNAL y año entre 2000 y 2100.',
    'sesion_invalida'      => 'Revisa la sesión: título obligatorio y duración entre 1 y 600 minutos.',
    'sin_sesiones'         => 'Agrega al menos una sesión.',
    'no_se_pudo_guardar'   => 'No se pudo guardar. Intenta de nuevo.',
    'no_se_puede_eliminar' => 'No se puede eliminar porque ya tiene respuestas o resultados de estudiantes.',
    'despublicar_primero'  => 'Despublica el simulacro antes de eliminarlo o quitarle sesiones.',
    'no_publicable'        => 'No se puede publicar todavía. Corrige lo siguiente:',
    'pregunta_invalida'    => 'Escribe el enunciado y el área de la pregunta.',
    'minimo_dos_opciones'  => 'Escribe al menos las opciones A y B.',
    'sin_correcta'         => 'Marca cuál es la respuesta correcta (debe tener texto).',
    'no_encontrado'        => 'No se encontró lo que buscabas.',
];

$mensaje = $_GET['mensaje'] ?? '';
$error   = $_GET['error'] ?? '';

if ($mensaje !== '' && isset($textosOk[$mensaje])): ?>
    <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo e($textosOk[$mensaje]); ?></div>
<?php endif;

if ($error !== '' && isset($textosError[$error])): ?>
    <div class="alert alert-error">
        <i class="fa-solid fa-circle-exclamation"></i> <?php echo e($textosError[$error]); ?>
        <?php if ($error === 'no_publicable' && !empty($_SESSION['flash_problemas'])): ?>
            <ul>
                <?php foreach (array_slice($_SESSION['flash_problemas'], 0, 8) as $problema): ?>
                    <li><?php echo e($problema); ?></li>
                <?php endforeach; ?>
                <?php if (count($_SESSION['flash_problemas']) > 8): ?>
                    <li>… y <?php echo count($_SESSION['flash_problemas']) - 8; ?> más.</li>
                <?php endif; ?>
            </ul>
        <?php endif; ?>
    </div>
<?php endif;
unset($_SESSION['flash_problemas']);
