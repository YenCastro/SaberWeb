<?php
// app/views/estudiante/resultado.php?id=ID_RESULTADO
require_once __DIR__ . '/../../config/config.php';
requerirSesion('Estudiante');
require_once __DIR__ . '/../../models/Resultado.php';

$idResultado    = (int)($_GET['id'] ?? 0);
$resultadoModel = new Resultado();
$r = $resultadoModel->obtener($idResultado, $_SESSION['usuario_id']);

if (!$r) {
    header("Location: " . BASE_URL . "app/views/estudiante/simulacros.php?error=no_encontrado");
    exit();
}
if ($r['estado'] === 'En curso') {
    header("Location: " . BASE_URL . "app/views/estudiante/realizar_simulacro.php?id_resultado=" . $idResultado);
    exit();
}

$areas   = $resultadoModel->detallePorArea($idResultado, $r['id_simulacro']);
$revision = $resultadoModel->revision($idResultado, $r['id_simulacro']);
$cuenta = ['correcta' => 0, 'incorrecta' => 0, 'sin_responder' => 0];
foreach ($revision as $q) { $cuenta[$q['estado']]++; }
$total   = (int)$r['respuestas_correctas'] + (int)$r['respuestas_incorrectas'];
$puntaje = (int)$r['puntaje'];

$tituloPagina = 'Resultado - SaberWeb';
$hojasCss     = ['estudiante/resultado.css'];
include ROOT_PATH . 'app/views/layouts/header.php';
?>
<main class="est-main">
    <p class="migas"><a href="<?php echo BASE_URL; ?>app/views/estudiante/simulacros.php">Simulacros</a> / Resultado</p>

    <?php if (($_GET['mensaje'] ?? '') === 'tiempo_agotado'): ?>
        <div class="alert alert-error"><i class="fa-regular fa-clock"></i> Se acabó el tiempo: tu examen se envió automáticamente con las respuestas que alcanzaste a marcar.</div>
    <?php endif; ?>

    <div class="card resultado-principal">
        <div class="puntaje-circulo" style="--p: <?php echo $puntaje; ?>;">
            <div><strong><?php echo $puntaje; ?></strong><span>de 100</span></div>
        </div>
        <div>
            <h1><?php echo e($r['prueba_titulo']); ?></h1>
            <p class="ayuda"><?php echo e($r['sesion_titulo']); ?> · <?php echo e(date('d/m/Y H:i', strtotime($r['fecha_realizacion']))); ?>
                · Tiempo usado: <?php echo e(duracionLegible($r['segundos_usados'])); ?></p>
            <div class="resumen-numeros">
                <div><strong class="ok"><?php echo (int)$r['respuestas_correctas']; ?></strong><span>Correctas</span></div>
                <div><strong class="mal"><?php echo (int)$r['respuestas_incorrectas']; ?></strong><span>Incorrectas o sin responder</span></div>
                <div><strong><?php echo $total; ?></strong><span>Total de preguntas</span></div>
            </div>
            <p class="ayuda">El puntaje es el porcentaje de preguntas acertadas. No es el puntaje oficial del ICFES.</p>
        </div>
    </div>

    <div class="card">
        <h2>Desempeño por área</h2>
        <?php foreach ($areas as $a):
            $pct = ((int)$a['total'] > 0) ? (int)round($a['correctas'] * 100 / $a['total']) : 0; ?>
            <div class="area-fila">
                <div class="area-nombre"><?php echo e($a['area']); ?></div>
                <div class="barra"><div class="barra-relleno" style="width: <?php echo $pct; ?>%;"></div></div>
                <div class="area-dato"><?php echo (int)$a['correctas']; ?>/<?php echo (int)$a['total']; ?> · <?php echo $pct; ?>%</div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card" id="revision">
        <div class="revision-cab">
            <h2>Revisión de preguntas</h2>
            <div class="filtros" role="group" aria-label="Filtrar preguntas">
                <button type="button" class="filtro activo" data-filtro="todas" aria-pressed="true">Todas (<?php echo count($revision); ?>)</button>
                <button type="button" class="filtro" data-filtro="incorrecta" aria-pressed="false">Incorrectas (<?php echo $cuenta['incorrecta']; ?>)</button>
                <button type="button" class="filtro" data-filtro="sin_responder" aria-pressed="false">Sin responder (<?php echo $cuenta['sin_responder']; ?>)</button>
                <button type="button" class="filtro" data-filtro="correcta" aria-pressed="false">Correctas (<?php echo $cuenta['correcta']; ?>)</button>
            </div>
        </div>

        <?php $etiquetas = ['correcta' => 'Correcta', 'incorrecta' => 'Incorrecta', 'sin_responder' => 'Sin responder']; ?>
        <?php foreach ($revision as $q): ?>
            <article class="rev-item rev-<?php echo e($q['estado']); ?>" data-estado="<?php echo e($q['estado']); ?>">
                <div class="rev-cab">
                    <div class="meta">
                        <strong>Pregunta <?php echo (int)$q['numero_pregunta']; ?></strong>
                        <span class="badge badge-area"><?php echo e($q['area']); ?></span>
                    </div>
                    <span class="rev-estado rev-estado-<?php echo e($q['estado']); ?>"><?php echo e($etiquetas[$q['estado']]); ?></span>
                </div>
                <p class="enunciado-rev"><?php echo e($q['enunciado']); ?></p>
                <ul class="rev-opciones">
                    <?php foreach ($q['opciones'] as $o):
                        $esCorrecta = ((int)$o['es_correcta'] === 1);
                        $esElegida  = ($q['elegida'] !== null && (int)$q['elegida'] === (int)$o['id_opcion']);
                        $clase = $esCorrecta ? 'op-correcta' : ($esElegida ? 'op-mala' : '');
                    ?>
                        <li class="<?php echo $clase; ?>">
                            <span class="op-letra"><?php echo e($o['letra']); ?></span>
                            <span class="op-texto"><?php echo e($o['texto']); ?></span>
                            <?php if ($esElegida): ?><span class="op-tag">Tu respuesta</span><?php endif; ?>
                            <?php if ($esCorrecta): ?><span class="op-tag op-tag-ok"><i class="fa-solid fa-check"></i> Respuesta correcta</span><?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </article>
        <?php endforeach; ?>
        <p class="vacio" id="revision-vacia" hidden>No hay preguntas en este filtro.</p>
    </div>

    <div class="fila-botones">
        <a class="btn btn-azul" href="<?php echo BASE_URL; ?>app/views/estudiante/simulacros.php"><i class="fa-solid fa-list-check"></i> Más simulacros</a>
        <a class="btn btn-borde" href="<?php echo BASE_URL; ?>app/views/estudiante/historial.php"><i class="fa-solid fa-clock-rotate-left"></i> Mi historial</a>
    </div>
</main>

<?php include ROOT_PATH . 'app/views/layouts/footer.php'; ?>
<script src="<?php echo BASE_URL; ?>public/assets/js/estudiante/resultado.js"></script>
</body>
</html>
