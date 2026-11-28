<?php
// app/views/admin/reportes.php : reportes y sugerencias de los estudiantes
require_once __DIR__ . '/../../config/config.php';
requerirSesion('Administrador');
require_once __DIR__ . '/../../models/Reporte.php';

$reporteModel = new Reporte();

// Filtros (solo valores permitidos)
$fEstado = in_array($_GET['estado'] ?? '', Reporte::ESTADOS, true) ? $_GET['estado'] : '';
$fTipo   = in_array($_GET['tipo'] ?? '', Reporte::TIPOS, true) ? $_GET['tipo'] : '';

$reportes = $reporteModel->listarAdmin($fEstado, $fTipo);
$conteo   = $reporteModel->contarPorEstado();

$etiquetaTipo = ['Error' => 'Error en pregunta', 'Pregunta' => 'Duda sobre pregunta', 'Sugerencia' => 'Sugerencia'];
$ctrl = BASE_URL . 'app/controllers/ReporteController.php';

// Enlace de una pestaña de estado conservando el filtro de tipo
function urlFiltro($estado, $tipo) {
    $params = [];
    if ($estado !== '') $params['estado'] = $estado;
    if ($tipo !== '')   $params['tipo'] = $tipo;
    return BASE_URL . 'app/views/admin/reportes.php' . (count($params) ? '?' . http_build_query($params) : '');
}

$tituloPagina = 'Reportes - Administración SaberWeb';
$hojasCss     = ['admin/admin.css', 'admin/reportes.css'];
include ROOT_PATH . 'app/views/layouts/header.php';
?>
<main class="admin-main">
    <div class="admin-titulo">
        <div>
            <h1>Reportes y sugerencias</h1>
            <p>Lo que los estudiantes te cuentan. Al responder o resolver, ellos reciben una notificación.</p>
        </div>
    </div>

    <?php include __DIR__ . '/_mensajes.php'; ?>

    <div class="reportes-barra">
        <div class="filtros" role="group" aria-label="Filtrar por estado">
            <a class="filtro <?php echo $fEstado === '' ? 'activo' : ''; ?>" href="<?php echo urlFiltro('', $fTipo); ?>">Todos</a>
            <?php foreach (Reporte::ESTADOS as $est): ?>
                <a class="filtro <?php echo $fEstado === $est ? 'activo' : ''; ?>" href="<?php echo urlFiltro($est, $fTipo); ?>">
                    <?php echo e($est); ?> (<?php echo (int)$conteo[$est]; ?>)</a>
            <?php endforeach; ?>
        </div>
        <form method="GET" class="filtro-tipo">
            <?php if ($fEstado !== ''): ?><input type="hidden" name="estado" value="<?php echo e($fEstado); ?>"><?php endif; ?>
            <label for="tipo" class="ayuda">Tipo:</label>
            <select id="tipo" name="tipo" onchange="this.form.submit()">
                <option value="">Todos</option>
                <?php foreach (Reporte::TIPOS as $t): ?>
                    <option value="<?php echo $t; ?>" <?php echo $fTipo === $t ? 'selected' : ''; ?>><?php echo e($etiquetaTipo[$t]); ?></option>
                <?php endforeach; ?>
            </select>
            <noscript><button type="submit" class="btn btn-borde btn-pequeno">Filtrar</button></noscript>
        </form>
    </div>

    <?php if (count($reportes) === 0): ?>
        <div class="card vacio"><i class="fa-regular fa-comments"></i>
            <?php echo ($fEstado !== '' || $fTipo !== '') ? 'No hay reportes con ese filtro.' : 'Todavía no hay reportes ni sugerencias.'; ?></div>
    <?php endif; ?>

    <?php foreach ($reportes as $r): ?>
        <article class="card reporte-card reporte-<?php echo strtolower(e($r['estado'])); ?>">
            <div class="reporte-cab">
                <div class="meta">
                    <span class="badge badge-<?php echo strtolower(e($r['tipo'])); ?>"><?php echo e($etiquetaTipo[$r['tipo']]); ?></span>
                    <span class="badge badge-<?php echo strtolower(e($r['estado'])); ?>"><?php echo e($r['estado']); ?></span>
                    <span><i class="fa-regular fa-calendar"></i> <?php echo e(date('d/m/Y H:i', strtotime($r['fecha']))); ?></span>
                </div>
                <div class="ayuda"><i class="fa-regular fa-user"></i> <?php echo e($r['nombre_completo']); ?> · <?php echo e($r['correo']); ?></div>
            </div>

            <?php if ($r['id_pregunta'] !== null): ?>
                <div class="pregunta-ref">
                    <div class="meta">
                        <strong>Pregunta <?php echo (int)$r['numero_pregunta']; ?></strong>
                        <span class="badge badge-area"><?php echo e($r['area']); ?></span>
                        <span><?php echo e($r['prueba_titulo']); ?> · <?php echo e($r['sesion_titulo']); ?></span>
                    </div>
                    <p><?php echo e($r['enunciado']); ?></p>
                    <a class="btn btn-borde btn-pequeno" href="<?php echo BASE_URL; ?>app/views/admin/preguntas.php?id_simulacro=<?php echo (int)$r['id_simulacro']; ?>&editar=<?php echo (int)$r['id_pregunta']; ?>">
                        <i class="fa-solid fa-pen"></i> Abrir la pregunta para editarla</a>
                </div>
            <?php elseif ($r['tipo'] !== 'Sugerencia'): ?>
                <p class="ayuda">La pregunta reportada ya fue eliminada.</p>
            <?php endif; ?>

            <p class="reporte-texto"><?php echo e($r['descripcion']); ?></p>

            <form method="POST" action="<?php echo $ctrl; ?>?action=actualizar" class="reporte-form">
                <?php echo csrf_campo(); ?>
                <input type="hidden" name="id_reporte" value="<?php echo (int)$r['id_reporte']; ?>">
                <input type="hidden" name="f_estado" value="<?php echo e($fEstado); ?>">
                <input type="hidden" name="f_tipo" value="<?php echo e($fTipo); ?>">

                <div class="form-grupo">
                    <label for="estado-<?php echo (int)$r['id_reporte']; ?>">Estado</label>
                    <select id="estado-<?php echo (int)$r['id_reporte']; ?>" name="estado">
                        <?php foreach (Reporte::ESTADOS as $est): ?>
                            <option value="<?php echo $est; ?>" <?php echo $r['estado'] === $est ? 'selected' : ''; ?>><?php echo $est; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-grupo reporte-mensaje">
                    <label for="mensaje-<?php echo (int)$r['id_reporte']; ?>">Mensaje para el estudiante (opcional)</label>
                    <textarea id="mensaje-<?php echo (int)$r['id_reporte']; ?>" name="mensaje" maxlength="500" rows="2"
                              placeholder="Ej: Gracias, ya corregimos la opción B."></textarea>
                </div>
                <button type="submit" class="btn btn-azul"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
            </form>
            <p class="ayuda">Se avisa al estudiante cuando el estado pasa a Visto o Resuelto, o cuando le escribes un mensaje.</p>
        </article>
    <?php endforeach; ?>
</main>

<?php include ROOT_PATH . 'app/views/layouts/footer.php'; ?>
<script src="<?php echo BASE_URL; ?>public/assets/js/admin/admin.js"></script>
</body>
</html>
