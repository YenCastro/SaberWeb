<?php
// app/views/admin/editar_simulacro.php?id=ID_PRUEBA
require_once __DIR__ . '/../../config/config.php';
requerirSesion('Administrador');
require_once __DIR__ . '/../../models/Prueba.php';
require_once __DIR__ . '/../../models/Simulacro.php';

$idPrueba = (int)($_GET['id'] ?? 0);
$prueba   = (new Prueba())->obtener($idPrueba);
if (!$prueba) {
    header("Location: " . BASE_URL . "app/views/admin/simulacros.php?error=no_encontrado");
    exit();
}
$sesiones  = (new Simulacro())->listarPorPrueba($idPrueba);
$publicado = ($prueba['estado'] === 'Publicado');

$tituloPagina = 'Editar simulacro - Administración SaberWeb';
$hojasCss     = ['admin/admin.css'];
$ctrl         = BASE_URL . 'app/controllers/AdminController.php';
include ROOT_PATH . 'app/views/layouts/header.php';
?>
<main class="admin-main">
    <p class="migas"><a href="<?php echo BASE_URL; ?>app/views/admin/simulacros.php">Simulacros</a> / <?php echo e($prueba['titulo']); ?></p>
    <div class="admin-titulo">
        <div>
            <h1>Editar simulacro</h1>
            <div class="meta" style="margin-top:6px;">
                <span class="badge badge-<?php echo $publicado ? 'publicado' : 'prueba'; ?>"><?php echo e($prueba['estado']); ?></span>
            </div>
        </div>
        <form method="POST" action="<?php echo $ctrl; ?>?action=cambiarEstado">
            <?php echo csrf_campo(); ?>
            <input type="hidden" name="id_prueba" value="<?php echo $idPrueba; ?>">
            <input type="hidden" name="estado" value="<?php echo $publicado ? 'Prueba' : 'Publicado'; ?>">
            <input type="hidden" name="volver" value="editar">
            <button type="submit" class="btn <?php echo $publicado ? 'btn-borde' : 'btn-verde'; ?>">
                <i class="fa-solid <?php echo $publicado ? 'fa-eye-slash' : 'fa-rocket'; ?>"></i>
                <?php echo $publicado ? 'Despublicar' : 'Publicar'; ?>
            </button>
        </form>
    </div>

    <?php include __DIR__ . '/_mensajes.php'; ?>

    <form method="POST" action="<?php echo $ctrl; ?>?action=actualizarPrueba" class="card">
        <?php echo csrf_campo(); ?>
        <input type="hidden" name="id_prueba" value="<?php echo $idPrueba; ?>">
        <h2>Datos generales</h2>
        <div class="form-grid">
            <div class="form-grupo ancho">
                <label for="titulo">Título</label>
                <input type="text" id="titulo" name="titulo" maxlength="200" value="<?php echo e($prueba['titulo']); ?>" required>
            </div>
            <div class="form-grupo">
                <label for="tipo">Tipo de prueba</label>
                <select id="tipo" name="tipo" required>
                    <?php foreach (['ICFES', 'UNAL'] as $t): ?>
                        <option value="<?php echo $t; ?>" <?php echo $prueba['tipo'] === $t ? 'selected' : ''; ?>><?php echo $t; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-grupo">
                <label for="anio">Año</label>
                <input type="number" id="anio" name="anio" min="2000" max="2100" value="<?php echo e($prueba['anio']); ?>" required>
            </div>
            <div class="form-grupo ancho">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" maxlength="2000"><?php echo e($prueba['descripcion']); ?></textarea>
            </div>
        </div>
        <button type="submit" class="btn btn-azul"><i class="fa-solid fa-floppy-disk"></i> Guardar cambios</button>
    </form>

    <div class="card">
        <h2>Sesiones</h2>
        <?php if (count($sesiones) === 0): ?>
            <p class="ayuda">Esta prueba no tiene sesiones. Agrega una abajo.</p>
        <?php endif; ?>

        <?php foreach ($sesiones as $s): ?>
            <div class="sesion-editar">
                <form method="POST" action="<?php echo $ctrl; ?>?action=actualizarSesion" style="display:contents;">
                    <?php echo csrf_campo(); ?>
                    <input type="hidden" name="id_simulacro" value="<?php echo (int)$s['id_simulacro']; ?>">
                    <input type="text" name="titulo" maxlength="200" value="<?php echo e($s['titulo']); ?>" required aria-label="Nombre de la sesión">
                    <input type="number" name="duracion" min="1" max="600" value="<?php echo (int)$s['duracion_minutos']; ?>" required aria-label="Duración en minutos">
                    <div class="fila-botones">
                        <button type="submit" class="btn btn-borde btn-pequeno">Guardar</button>
                        <a class="btn btn-borde btn-pequeno" href="<?php echo BASE_URL; ?>app/views/admin/preguntas.php?id_simulacro=<?php echo (int)$s['id_simulacro']; ?>">
                            Preguntas (<?php echo (int)$s['num_preguntas']; ?>)</a>
                    </div>
                </form>
                <form method="POST" action="<?php echo $ctrl; ?>?action=eliminarSesion" style="grid-column:1/-1;"
                      data-confirm="¿Eliminar la sesión &quot;<?php echo e($s['titulo']); ?>&quot; y sus preguntas?">
                    <?php echo csrf_campo(); ?>
                    <input type="hidden" name="id_simulacro" value="<?php echo (int)$s['id_simulacro']; ?>">
                    <button type="submit" class="btn btn-peligro btn-pequeno"><i class="fa-solid fa-trash"></i> Eliminar sesión</button>
                </form>
            </div>
        <?php endforeach; ?>

        <h2 style="margin-top:22px;">Agregar sesión</h2>
        <form method="POST" action="<?php echo $ctrl; ?>?action=crearSesion" class="sesion-fila">
            <?php echo csrf_campo(); ?>
            <input type="hidden" name="id_prueba" value="<?php echo $idPrueba; ?>">
            <input type="text" name="titulo" maxlength="200" placeholder="Nombre de la sesión" required>
            <input type="number" name="duracion" min="1" max="600" value="270" required aria-label="Duración en minutos">
            <span class="sesion-unidad">min</span>
            <button type="submit" class="btn-icono" title="Agregar"><i class="fa-solid fa-plus"></i></button>
        </form>
    </div>
</main>

<?php include ROOT_PATH . 'app/views/layouts/footer.php'; ?>
<script src="<?php echo BASE_URL; ?>public/assets/js/admin/admin.js"></script>
</body>
</html>
