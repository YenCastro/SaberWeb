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
$total   = (int)$r['respuestas_correctas'] + (int)$r['respuestas_incorrectas'];
$minutos = (int)floor(((int)$r['segundos_usados']) / 60);
$segs    = ((int)$r['segundos_usados']) % 60;
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
                · Tiempo usado: <?php echo $minutos; ?> min <?php echo $segs; ?> s</p>
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

    <div class="fila-botones">
        <a class="btn btn-azul" href="<?php echo BASE_URL; ?>app/views/estudiante/simulacros.php"><i class="fa-solid fa-list-check"></i> Más simulacros</a>
    </div>
</main>

<?php include ROOT_PATH . 'app/views/layouts/footer.php'; ?>
</body>
</html>
