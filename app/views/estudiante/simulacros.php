<?php
// app/views/estudiante/simulacros.php : simulacros publicados disponibles para presentar
require_once __DIR__ . '/../../config/config.php';
requerirSesion('Estudiante');
require_once __DIR__ . '/../../models/Prueba.php';
require_once __DIR__ . '/../../models/Simulacro.php';
require_once __DIR__ . '/../../models/Resultado.php';

$pruebas        = (new Prueba())->listarPublicadas();
$simulacroModel = new Simulacro();
$resumen        = (new Resultado())->resumenPorCuenta($_SESSION['usuario_id']);

$errores = [
    'no_encontrado'      => 'No encontramos ese intento.',
    'no_disponible'      => 'Ese simulacro ya no está disponible.',
    'sin_preguntas'      => 'Ese simulacro todavía no tiene preguntas.',
    'solicitud_invalida' => 'La solicitud no es válida. Intenta de nuevo.',
    'no_se_pudo_iniciar' => 'No se pudo iniciar el simulacro. Intenta de nuevo.',
];
$error = $_GET['error'] ?? '';

$tituloPagina = 'Simulacros - SaberWeb';
$hojasCss     = ['estudiante/simulacros.css'];
$ctrl         = BASE_URL . 'app/controllers/SimulacroController.php';
include ROOT_PATH . 'app/views/layouts/header.php';
?>
<main class="est-main">
    <div class="admin-titulo">
        <div>
            <h1>Simulacros disponibles</h1>
            <p>Elige una sesión y empieza. El tiempo corre desde que pulsas «Comenzar», y puedes retomar tu intento si sales.</p>
        </div>
    </div>

    <?php if ($error !== '' && isset($errores[$error])): ?>
        <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo e($errores[$error]); ?></div>
    <?php endif; ?>

    <?php if (count($pruebas) === 0): ?>
        <div class="card vacio"><i class="fa-solid fa-hourglass-half"></i>
            Aún no hay simulacros publicados. Vuelve pronto.</div>
    <?php endif; ?>

    <?php foreach ($pruebas as $p): ?>
        <div class="card prueba-card">
            <div class="cabecera">
                <div>
                    <h3><?php echo e($p['titulo']); ?></h3>
                    <div class="meta">
                        <span class="badge badge-<?php echo strtolower(e($p['tipo'])); ?>"><?php echo e($p['tipo']); ?></span>
                        <span><i class="fa-regular fa-calendar"></i> <?php echo e($p['anio']); ?></span>
                    </div>
                </div>
            </div>
            <?php if ($p['descripcion'] !== ''): ?>
                <p class="ayuda"><?php echo e($p['descripcion']); ?></p>
            <?php endif; ?>

            <div class="sesiones-lista">
                <?php foreach ($simulacroModel->listarPorPrueba($p['id_prueba']) as $s):
                    $estado    = $resumen[$s['id_simulacro']] ?? null;
                    $enCurso   = $estado['en_curso'] ?? null;
                    $ultimo    = $estado['ultimo_finalizado'] ?? null;
                    $sinPreg   = ((int)$s['num_preguntas'] === 0);
                ?>
                    <div class="sesion-item">
                        <div>
                            <strong><?php echo e($s['titulo']); ?></strong>
                            <div class="meta">
                                <span><i class="fa-regular fa-clock"></i> <?php echo (int)$s['duracion_minutos']; ?> min</span>
                                <span><i class="fa-solid fa-circle-question"></i> <?php echo (int)$s['num_preguntas']; ?> preguntas</span>
                                <?php if ($enCurso): ?>
                                    <span class="badge badge-prueba">En curso</span>
                                <?php elseif ($ultimo): ?>
                                    <span class="badge badge-publicado">Realizado · <?php echo (int)$estado['intentos']; ?> intento(s)</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="fila-botones">
                            <?php if ($ultimo): ?>
                                <a class="btn btn-borde btn-pequeno" href="<?php echo BASE_URL; ?>app/views/estudiante/resultado.php?id=<?php echo (int)$ultimo; ?>">
                                    <i class="fa-solid fa-chart-column"></i> Último resultado</a>
                            <?php endif; ?>

                            <?php if ($enCurso): ?>
                                <a class="btn btn-azul btn-pequeno" href="<?php echo BASE_URL; ?>app/views/estudiante/realizar_simulacro.php?id_resultado=<?php echo (int)$enCurso; ?>">
                                    <i class="fa-solid fa-play"></i> Continuar</a>
                            <?php elseif ($sinPreg): ?>
                                <span class="ayuda">Sin preguntas</span>
                            <?php else: ?>
                                <form method="POST" action="<?php echo $ctrl; ?>?action=iniciar"
                                      data-confirm="Al comenzar empieza a correr el tiempo (<?php echo (int)$s['duracion_minutos']; ?> minutos). ¿Estás listo?">
                                    <?php echo csrf_campo(); ?>
                                    <input type="hidden" name="id_simulacro" value="<?php echo (int)$s['id_simulacro']; ?>">
                                    <button type="submit" class="btn btn-azul btn-pequeno">
                                        <i class="fa-solid fa-play"></i> <?php echo $ultimo ? 'Intentar de nuevo' : 'Comenzar'; ?></button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</main>

<?php include ROOT_PATH . 'app/views/layouts/footer.php'; ?>
<script src="<?php echo BASE_URL; ?>public/assets/js/estudiante/simulacros.js"></script>
</body>
</html>
