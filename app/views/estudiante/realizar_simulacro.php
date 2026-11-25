<?php
// app/views/estudiante/realizar_simulacro.php?id_resultado=ID
// Pantalla del examen: una pregunta a la vez, temporizador y guardado automático de respuestas.
require_once __DIR__ . '/../../config/config.php';
requerirSesion('Estudiante');
require_once __DIR__ . '/../../models/Resultado.php';
require_once __DIR__ . '/../../models/Pregunta.php';
require_once __DIR__ . '/../../models/Opcion.php';
require_once __DIR__ . '/../../models/RespuestaEstudiante.php';

$idResultado    = (int)($_GET['id_resultado'] ?? 0);
$resultadoModel = new Resultado();
$intento        = $resultadoModel->obtener($idResultado, $_SESSION['usuario_id']);

// Intento que no existe o que es de otra persona
if (!$intento) {
    header("Location: " . BASE_URL . "app/views/estudiante/simulacros.php?error=no_encontrado");
    exit();
}
// Ya terminado -> a su resultado
if ($intento['estado'] === 'Finalizado') {
    header("Location: " . BASE_URL . "app/views/estudiante/resultado.php?id=" . $idResultado);
    exit();
}
// Se acabó el tiempo mientras no estaba -> se cierra solo
if ((int)$intento['segundos_restantes'] <= 0) {
    $resultadoModel->finalizar($idResultado);
    header("Location: " . BASE_URL . "app/views/estudiante/resultado.php?id=" . $idResultado . "&mensaje=tiempo_agotado");
    exit();
}

$preguntas = (new Pregunta())->listarPorSimulacro($intento['id_simulacro']);
if (count($preguntas) === 0) {
    header("Location: " . BASE_URL . "app/views/estudiante/simulacros.php?error=sin_preguntas");
    exit();
}
$opciones   = (new Opcion())->listarPorPreguntas(array_column($preguntas, 'id_pregunta'));
$respuestas = (new RespuestaEstudiante())->listarPorResultado($idResultado);

// Datos para el JavaScript. IMPORTANTE: aquí NO va es_correcta: el estudiante no debe ver las respuestas.
$datos = [
    'idResultado' => $idResultado,
    'segundos'    => (int)$intento['segundos_restantes'],
    'csrf'        => csrf_token(),
    'urlGuardar'  => BASE_URL . 'app/controllers/SimulacroController.php?action=guardarRespuesta',
    'respuestas'  => (object)$respuestas,
    'preguntas'   => array_map(function ($q) use ($opciones) {
        return [
            'id'        => (int)$q['id_pregunta'],
            'numero'    => (int)$q['numero_pregunta'],
            'area'      => $q['area'],
            'enunciado' => $q['enunciado'],
            'opciones'  => array_map(function ($o) {
                return ['id' => (int)$o['id_opcion'], 'letra' => $o['letra'], 'texto' => $o['texto']];
            }, $opciones[$q['id_pregunta']] ?? []),
        ];
    }, $preguntas),
];
$json = json_encode($datos, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($intento['sesion_titulo']); ?> - SaberWeb</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/variables.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/estudiante/realizar_simulacro.css">
</head>
<body>

<!-- Barra superior: sin navegación del sitio, para no distraer durante el examen -->
<header class="exam-top">
    <div class="exam-titulo">
        <strong><?php echo e($intento['prueba_titulo']); ?></strong>
        <span><?php echo e($intento['sesion_titulo']); ?></span>
    </div>
    <div class="exam-progreso">
        Pregunta <strong id="num-actual">1</strong> de <strong id="num-total"><?php echo count($preguntas); ?></strong>
        <span class="exam-respondidas">· <span id="contador-respondidas">0</span> respondidas</span>
    </div>
    <div class="exam-timer" id="temporizador" role="timer" aria-live="off">
        <i class="fa-regular fa-clock"></i> <span id="tiempo">--:--:--</span>
    </div>
</header>

<div id="aviso" class="aviso" role="alert" hidden></div>

<main class="exam-layout">
    <section class="pregunta-card">
        <div class="pregunta-cab">
            <span class="area-badge" id="area"></span>
        </div>
        <p class="enunciado" id="enunciado"></p>
        <div class="opciones" id="opciones" role="radiogroup" aria-label="Opciones de respuesta"></div>

        <div class="exam-botones">
            <button type="button" class="btn-exam btn-claro" id="btn-anterior"><i class="fa-solid fa-arrow-left"></i> Anterior</button>
            <button type="button" class="btn-exam btn-principal" id="btn-siguiente">Siguiente <i class="fa-solid fa-arrow-right"></i></button>
        </div>
    </section>

    <aside class="exam-lateral">
        <h2>Preguntas</h2>
        <div class="navegador" id="navegador"></div>
        <div class="leyenda">
            <span><i class="punto punto-resp"></i> Respondida</span>
            <span><i class="punto punto-vacia"></i> Sin responder</span>
        </div>

        <form method="POST" id="form-finalizar" action="<?php echo BASE_URL; ?>app/controllers/SimulacroController.php?action=finalizar">
            <?php echo csrf_campo(); ?>
            <input type="hidden" name="id_resultado" value="<?php echo $idResultado; ?>">
            <button type="button" class="btn-exam btn-finalizar" id="btn-finalizar"><i class="fa-solid fa-flag-checkered"></i> Finalizar examen</button>
        </form>
    </aside>
</main>

<script type="application/json" id="datos-examen"><?php echo $json; ?></script>
<script src="<?php echo BASE_URL; ?>public/assets/js/estudiante/realizar_simulacro.js"></script>
</body>
</html>
