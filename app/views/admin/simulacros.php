<?php
// app/views/admin/simulacros.php : listado de pruebas con sus sesiones
require_once __DIR__ . '/../../config/config.php';
requerirSesion('Administrador');
require_once __DIR__ . '/../../models/Prueba.php';
require_once __DIR__ . '/../../models/Simulacro.php';

$pruebaModel    = new Prueba();
$simulacroModel = new Simulacro();
$pruebas        = $pruebaModel->listar();

$tituloPagina = 'Simulacros - Administración SaberWeb';
$hojasCss     = ['admin/admin.css'];
$ctrl         = BASE_URL . 'app/controllers/AdminController.php';
include ROOT_PATH . 'app/views/layouts/header.php';
?>
<main class="admin-main">
    <div class="admin-titulo">
        <div>
            <h1>Simulacros</h1>
            <p>Cada simulacro es una prueba completa dividida en sesiones.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>app/views/admin/crear_simulacro.php" class="btn btn-azul">
            <i class="fa-solid fa-plus"></i> Nuevo simulacro
        </a>
    </div>

    <?php include __DIR__ . '/_mensajes.php'; ?>

    <?php if (count($pruebas) === 0): ?>
        <div class="card vacio"><i class="fa-solid fa-folder-open"></i>
            Todavía no hay simulacros. Crea el primero para empezar a cargar preguntas.</div>
    <?php endif; ?>

    <?php foreach ($pruebas as $p):
        $sesiones  = $simulacroModel->listarPorPrueba($p['id_prueba']);
        $publicado = ($p['estado'] === 'Publicado');
    ?>
        <div class="card prueba-card">
            <div class="cabecera">
                <div>
                    <h3><?php echo e($p['titulo']); ?></h3>
                    <div class="meta">
                        <span class="badge badge-<?php echo strtolower(e($p['tipo'])); ?>"><?php echo e($p['tipo']); ?></span>
                        <span class="badge badge-<?php echo $publicado ? 'publicado' : 'prueba'; ?>"><?php echo e($p['estado']); ?></span>
                        <span><i class="fa-regular fa-calendar"></i> <?php echo e($p['anio']); ?></span>
                        <span><?php echo (int)$p['num_preguntas']; ?> preguntas en total</span>
                    </div>
                </div>
                <div class="fila-botones">
                    <a class="btn btn-borde btn-pequeno" href="<?php echo BASE_URL; ?>app/views/admin/editar_simulacro.php?id=<?php echo (int)$p['id_prueba']; ?>">
                        <i class="fa-solid fa-pen"></i> Editar</a>

                    <form method="POST" action="<?php echo $ctrl; ?>?action=cambiarEstado">
                        <?php echo csrf_campo(); ?>
                        <input type="hidden" name="id_prueba" value="<?php echo (int)$p['id_prueba']; ?>">
                        <input type="hidden" name="estado" value="<?php echo $publicado ? 'Prueba' : 'Publicado'; ?>">
                        <input type="hidden" name="volver" value="lista">
                        <button type="submit" class="btn btn-pequeno <?php echo $publicado ? 'btn-borde' : 'btn-verde'; ?>">
                            <i class="fa-solid <?php echo $publicado ? 'fa-eye-slash' : 'fa-rocket'; ?>"></i>
                            <?php echo $publicado ? 'Despublicar' : 'Publicar'; ?>
                        </button>
                    </form>

                    <form method="POST" action="<?php echo $ctrl; ?>?action=eliminarPrueba"
                          data-confirm="¿Eliminar el simulacro &quot;<?php echo e($p['titulo']); ?>&quot; con todas sus preguntas? Esta acción no se puede deshacer.">
                        <?php echo csrf_campo(); ?>
                        <input type="hidden" name="id_prueba" value="<?php echo (int)$p['id_prueba']; ?>">
                        <button type="submit" class="btn btn-peligro btn-pequeno"><i class="fa-solid fa-trash"></i></button>
                    </form>
                </div>
            </div>

            <?php if ($p['descripcion'] !== ''): ?>
                <p class="ayuda"><?php echo e($p['descripcion']); ?></p>
            <?php endif; ?>

            <div class="sesiones-lista">
                <?php foreach ($sesiones as $s): ?>
                    <div class="sesion-item">
                        <div>
                            <strong><?php echo e($s['titulo']); ?></strong>
                            <div class="meta">
                                <span><i class="fa-regular fa-clock"></i> <?php echo (int)$s['duracion_minutos']; ?> min</span>
                                <span><?php echo (int)$s['num_preguntas']; ?> preguntas</span>
                            </div>
                        </div>
                        <a class="btn btn-borde btn-pequeno" href="<?php echo BASE_URL; ?>app/views/admin/preguntas.php?id_simulacro=<?php echo (int)$s['id_simulacro']; ?>">
                            <i class="fa-solid fa-pen-to-square"></i> Preguntas</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</main>

<?php include ROOT_PATH . 'app/views/layouts/footer.php'; ?>
<script src="<?php echo BASE_URL; ?>public/assets/js/admin/admin.js"></script>
</body>
</html>
