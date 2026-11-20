<?php
// app/views/admin/dashboard_admin.php
require_once __DIR__ . '/../../config/config.php';
requerirSesion('Administrador');
require_once __DIR__ . '/../../models/Prueba.php';

$pruebaModel = new Prueba();
$resumen     = $pruebaModel->resumenAdmin();
$recientes   = array_slice($pruebaModel->listar(), 0, 5);

$tituloPagina = 'Panel de administración - SaberWeb';
$hojasCss     = ['admin/admin.css'];
include ROOT_PATH . 'app/views/layouts/header.php';
?>
<main class="admin-main">
    <div class="admin-titulo">
        <div>
            <h1>Hola, <?php echo e($_SESSION['usuario_nombre']); ?></h1>
            <p>Gestiona los simulacros, las preguntas y los reportes de SaberWeb.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>app/views/admin/crear_simulacro.php" class="btn btn-azul">
            <i class="fa-solid fa-plus"></i> Nuevo simulacro
        </a>
    </div>

    <?php include __DIR__ . '/_mensajes.php'; ?>

    <div class="stats-grid">
        <div class="stat-card"><div class="ico"><i class="fa-solid fa-layer-group"></i></div>
            <div><strong><?php echo (int)$resumen['pruebas']; ?></strong><span>Simulacros (<?php echo (int)$resumen['publicadas']; ?> publicados)</span></div></div>
        <div class="stat-card"><div class="ico"><i class="fa-regular fa-clock"></i></div>
            <div><strong><?php echo (int)$resumen['sesiones']; ?></strong><span>Sesiones</span></div></div>
        <div class="stat-card"><div class="ico"><i class="fa-solid fa-circle-question"></i></div>
            <div><strong><?php echo (int)$resumen['preguntas']; ?></strong><span>Preguntas</span></div></div>
        <div class="stat-card"><div class="ico"><i class="fa-solid fa-user-graduate"></i></div>
            <div><strong><?php echo (int)$resumen['estudiantes']; ?></strong><span>Estudiantes</span></div></div>
        <div class="stat-card"><div class="ico"><i class="fa-solid fa-flag"></i></div>
            <div><strong><?php echo (int)$resumen['reportes_pendientes']; ?></strong><span>Reportes pendientes</span></div></div>
    </div>

    <div class="acciones-grid">
        <a class="accion-card" href="<?php echo BASE_URL; ?>app/views/admin/simulacros.php">
            <i class="fa-solid fa-list-check"></i><strong>Simulacros</strong><span>Ver, editar, publicar o eliminar.</span></a>
        <a class="accion-card" href="<?php echo BASE_URL; ?>app/views/admin/preguntas.php">
            <i class="fa-solid fa-pen-to-square"></i><strong>Preguntas</strong><span>Crear y editar preguntas por sesión.</span></a>
        <a class="accion-card" href="<?php echo BASE_URL; ?>app/views/admin/reportes.php">
            <i class="fa-solid fa-flag"></i><strong>Reportes</strong><span>Errores y sugerencias de estudiantes.</span></a>
    </div>

    <div class="card">
        <h2>Simulacros recientes</h2>
        <?php if (count($recientes) === 0): ?>
            <div class="vacio"><i class="fa-solid fa-folder-open"></i>Aún no has creado ningún simulacro.</div>
        <?php else: ?>
            <?php foreach ($recientes as $p): ?>
                <div class="sesion-item">
                    <div>
                        <strong><?php echo e($p['titulo']); ?></strong>
                        <div class="meta">
                            <span class="badge badge-<?php echo strtolower(e($p['tipo'])); ?>"><?php echo e($p['tipo']); ?></span>
                            <span class="badge badge-<?php echo $p['estado'] === 'Publicado' ? 'publicado' : 'prueba'; ?>"><?php echo e($p['estado']); ?></span>
                            <span><?php echo (int)$p['num_sesiones']; ?> sesión(es) · <?php echo (int)$p['num_preguntas']; ?> preguntas</span>
                        </div>
                    </div>
                    <a class="btn btn-borde btn-pequeno" href="<?php echo BASE_URL; ?>app/views/admin/editar_simulacro.php?id=<?php echo (int)$p['id_prueba']; ?>">Gestionar</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<?php include ROOT_PATH . 'app/views/layouts/footer.php'; ?>
<script src="<?php echo BASE_URL; ?>public/assets/js/admin/admin.js"></script>
</body>
</html>
