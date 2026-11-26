<?php
// app/views/estudiante/historial.php : todos los intentos del estudiante
require_once __DIR__ . '/../../config/config.php';
requerirSesion('Estudiante');
require_once __DIR__ . '/../../models/Resultado.php';

$resultadoModel = new Resultado();
$intentos = $resultadoModel->listarPorCuenta($_SESSION['usuario_id']);
$stats    = $resultadoModel->estadisticasCuenta($_SESSION['usuario_id']);

// Evolución: puntajes de los últimos 10 intentos finalizados, del más antiguo al más reciente
$finalizados = array_values(array_filter($intentos, function ($i) { return $i['estado'] === 'Finalizado'; }));
$evolucion   = array_reverse(array_slice($finalizados, 0, 10));

// Clase de color según el puntaje
function claseNivel($puntaje) {
    if ($puntaje >= 70) return 'nivel-alto';
    if ($puntaje >= 40) return 'nivel-medio';
    return 'nivel-bajo';
}

$tituloPagina = 'Mi historial - SaberWeb';
$hojasCss     = ['estudiante/historial.css'];
include ROOT_PATH . 'app/views/layouts/header.php';
?>
<main class="est-main">
    <div class="admin-titulo">
        <div>
            <h1>Mi historial</h1>
            <p>Todos los simulacros que has presentado. Entra a cada uno para revisar tus respuestas.</p>
        </div>
        <a class="btn btn-azul" href="<?php echo BASE_URL; ?>app/views/estudiante/simulacros.php"><i class="fa-solid fa-play"></i> Presentar un simulacro</a>
    </div>

    <?php if (count($intentos) === 0): ?>
        <div class="card vacio"><i class="fa-solid fa-clock-rotate-left"></i>
            Todavía no has presentado ningún simulacro. Cuando termines el primero, aparecerá aquí.</div>
    <?php else: ?>

        <div class="stats-grid">
            <div class="stat-card"><div class="ico"><i class="fa-solid fa-check-double"></i></div>
                <div><strong><?php echo (int)$stats['intentos']; ?></strong><span>Intentos finalizados</span></div></div>
            <div class="stat-card"><div class="ico"><i class="fa-solid fa-trophy"></i></div>
                <div><strong><?php echo (int)$stats['mejor']; ?></strong><span>Mejor puntaje (de 100)</span></div></div>
            <div class="stat-card"><div class="ico"><i class="fa-solid fa-chart-line"></i></div>
                <div><strong><?php echo (int)$stats['promedio']; ?></strong><span>Promedio (de 100)</span></div></div>
        </div>

        <?php if (count($evolucion) >= 2): ?>
            <div class="card">
                <h2>Tu evolución <span class="ayuda">(últimos <?php echo count($evolucion); ?> intentos)</span></h2>
                <div class="grafica" role="img" aria-label="Puntajes de tus últimos intentos">
                    <?php foreach ($evolucion as $e): ?>
                        <div class="grafica-col">
                            <span class="grafica-valor"><?php echo (int)$e['puntaje']; ?></span>
                            <div class="grafica-barra <?php echo claseNivel((int)$e['puntaje']); ?>" style="height: <?php echo max(4, (int)$e['puntaje']); ?>%;"></div>
                            <span class="grafica-fecha"><?php echo e(date('d/m', strtotime($e['fecha_realizacion']))); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2>Intentos</h2>
            <?php foreach ($intentos as $i):
                $enCurso = ($i['estado'] === 'En curso'); ?>
                <div class="intento-fila">
                    <div class="intento-info">
                        <strong><?php echo e($i['prueba_titulo']); ?></strong>
                        <div class="meta">
                            <span class="badge badge-<?php echo strtolower(e($i['prueba_tipo'])); ?>"><?php echo e($i['prueba_tipo']); ?></span>
                            <span><?php echo e($i['sesion_titulo']); ?></span>
                            <span><i class="fa-regular fa-calendar"></i> <?php echo e(date('d/m/Y H:i', strtotime($i['fecha_realizacion']))); ?></span>
                            <?php if (!$enCurso): ?>
                                <span><i class="fa-regular fa-clock"></i> <?php echo e(duracionLegible($i['segundos_usados'])); ?></span>
                                <span><?php echo (int)$i['respuestas_correctas']; ?> correctas · <?php echo (int)$i['respuestas_incorrectas']; ?> incorrectas</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($enCurso): ?>
                        <span class="badge badge-prueba">En curso</span>
                        <a class="btn btn-azul btn-pequeno" href="<?php echo BASE_URL; ?>app/views/estudiante/realizar_simulacro.php?id_resultado=<?php echo (int)$i['id_resultado']; ?>">
                            <i class="fa-solid fa-play"></i> Continuar</a>
                    <?php else: ?>
                        <span class="puntaje-pill <?php echo claseNivel((int)$i['puntaje']); ?>"><?php echo (int)$i['puntaje']; ?><small>/100</small></span>
                        <a class="btn btn-borde btn-pequeno" href="<?php echo BASE_URL; ?>app/views/estudiante/resultado.php?id=<?php echo (int)$i['id_resultado']; ?>">
                            Ver y revisar</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php include ROOT_PATH . 'app/views/layouts/footer.php'; ?>
</body>
</html>
