<?php
// app/views/admin/crear_simulacro.php
require_once __DIR__ . '/../../config/config.php';
requerirSesion('Administrador');

$tituloPagina = 'Nuevo simulacro - Administración SaberWeb';
$hojasCss     = ['admin/admin.css'];
$ctrl         = BASE_URL . 'app/controllers/AdminController.php';
include ROOT_PATH . 'app/views/layouts/header.php';
?>
<main class="admin-main">
    <p class="migas"><a href="<?php echo BASE_URL; ?>app/views/admin/simulacros.php">Simulacros</a> / Nuevo</p>
    <div class="admin-titulo">
        <div>
            <h1>Nuevo simulacro</h1>
            <p>Primero los datos generales y las sesiones; las preguntas se cargan después.</p>
        </div>
    </div>

    <?php include __DIR__ . '/_mensajes.php'; ?>

    <form method="POST" action="<?php echo $ctrl; ?>?action=crearPrueba">
        <?php echo csrf_campo(); ?>

        <div class="card">
            <h2>Datos generales</h2>
            <div class="form-grid">
                <div class="form-grupo ancho">
                    <label for="titulo">Título</label>
                    <input type="text" id="titulo" name="titulo" maxlength="200" placeholder="Ej: Simulacro ICFES Saber 11 - Calendario A" required>
                </div>
                <div class="form-grupo">
                    <label for="tipo">Tipo de prueba</label>
                    <select id="tipo" name="tipo" required>
                        <option value="ICFES">ICFES</option>
                        <option value="UNAL">UNAL</option>
                    </select>
                </div>
                <div class="form-grupo">
                    <label for="anio">Año</label>
                    <input type="number" id="anio" name="anio" min="2000" max="2100" value="<?php echo date('Y'); ?>" required>
                </div>
                <div class="form-grupo ancho">
                    <label for="descripcion">Descripción (opcional)</label>
                    <textarea id="descripcion" name="descripcion" maxlength="2000" placeholder="Qué incluye este simulacro, a quién va dirigido..."></textarea>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>Sesiones</h2>
            <p class="ayuda" style="margin-bottom:14px;">
                El ICFES se presenta en dos sesiones y el UNAL de corrido. Ajusta los nombres y la duración (en minutos) a tu simulacro.
            </p>
            <div id="lista-sesiones"></div>
            <button type="button" id="btn-agregar-sesion" class="btn btn-borde btn-pequeno">
                <i class="fa-solid fa-plus"></i> Agregar sesión
            </button>
        </div>

        <div class="fila-botones">
            <button type="submit" class="btn btn-azul"><i class="fa-solid fa-floppy-disk"></i> Crear simulacro</button>
            <a href="<?php echo BASE_URL; ?>app/views/admin/simulacros.php" class="btn btn-borde">Cancelar</a>
        </div>
    </form>
</main>

<?php include ROOT_PATH . 'app/views/layouts/footer.php'; ?>
<script src="<?php echo BASE_URL; ?>public/assets/js/admin/admin.js"></script>
</body>
</html>
