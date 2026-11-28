<?php
// app/views/estudiante/notificaciones.php
require_once __DIR__ . '/../../config/config.php';
requerirSesion('Estudiante');
require_once __DIR__ . '/../../models/Notificacion.php';

$notificacionModel = new Notificacion();
$notificaciones    = $notificacionModel->listarPorCuenta($_SESSION['usuario_id']);
$sinLeer = 0;
foreach ($notificaciones as $n) {
    if ((int)$n['visto'] === 0) $sinLeer++;
}

$mensaje = $_GET['mensaje'] ?? '';
$error   = $_GET['error'] ?? '';
$ctrl    = BASE_URL . 'app/controllers/NotificacionController.php';

$tituloPagina = 'Notificaciones - SaberWeb';
$hojasCss     = ['estudiante/notificaciones.css'];
include ROOT_PATH . 'app/views/layouts/header.php';
?>
<main class="est-main">
    <div class="admin-titulo">
        <div>
            <h1>Notificaciones</h1>
            <p><?php echo $sinLeer > 0 ? 'Tienes ' . $sinLeer . ' sin leer.' : 'Estás al día.'; ?></p>
        </div>
        <?php if ($sinLeer > 0): ?>
            <form method="POST" action="<?php echo $ctrl; ?>?action=marcarTodas">
                <?php echo csrf_campo(); ?>
                <button type="submit" class="btn btn-borde"><i class="fa-solid fa-check-double"></i> Marcar todas como leídas</button>
            </form>
        <?php endif; ?>
    </div>

    <?php if ($mensaje === 'todas_leidas'): ?>
        <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> Listo, marcaste todo como leído.</div>
    <?php endif; ?>
    <?php if ($error === 'solicitud_invalida'): ?>
        <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> La solicitud no es válida. Intenta de nuevo.</div>
    <?php endif; ?>

    <?php if (count($notificaciones) === 0): ?>
        <div class="card vacio"><i class="fa-regular fa-bell"></i>
            No tienes notificaciones. Aquí te avisaremos cuando respondamos un reporte o haya un nuevo simulacro.</div>
    <?php endif; ?>

    <?php foreach ($notificaciones as $n):
        $nueva = ((int)$n['visto'] === 0); ?>
        <article class="notificacion <?php echo $nueva ? 'nueva' : ''; ?>">
            <div class="notif-icono"><i class="fa-solid <?php echo $nueva ? 'fa-bell' : 'fa-envelope-open'; ?>"></i></div>
            <div class="notif-cuerpo">
                <div class="notif-cab">
                    <strong><?php echo e($n['titulo']); ?></strong>
                    <span class="ayuda"><?php echo e(date('d/m/Y H:i', strtotime($n['fecha_notificacion']))); ?></span>
                </div>
                <p><?php echo e($n['mensaje']); ?></p>
            </div>
            <?php if ($nueva): ?>
                <form method="POST" action="<?php echo $ctrl; ?>?action=marcarLeida">
                    <?php echo csrf_campo(); ?>
                    <input type="hidden" name="id_notificacion" value="<?php echo (int)$n['id_notificacion']; ?>">
                    <button type="submit" class="btn btn-borde btn-pequeno">Marcar leída</button>
                </form>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</main>

<?php include ROOT_PATH . 'app/views/layouts/footer.php'; ?>
</body>
</html>
