<?php
// app/views/estudiante/perfil.php
// Nota: esta vista se espera que sea cargada por EstudianteController::verPerfil(),
// que ya valida la sesión y entrega la variable $cuenta con los datos reales de la BD.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/Proyecto SENA SaberWeb/app/config/config.php';
requerirSesion('Estudiante');

$nombreActual = $cuenta['nombre_completo'] ?? ($_SESSION['usuario_nombre'] ?? 'Estudiante');
$inicial = mb_strtoupper(mb_substr(trim($nombreActual), 0, 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - SaberWeb</title>

    <!-- Fuentes y Hojas de Estilo -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/navbar.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/footer.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/estudiante/perfil.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

    <?php include ROOT_PATH . 'app/views/layouts/navbar.php'; ?>

    <!-- Contenido Principal -->
    <main class="profile-main">
        <div class="profile-card">

            <!-- Cabecera con avatar: ayuda a confirmar de un vistazo de qué cuenta se trata -->
            <div class="profile-header">
                <div class="profile-avatar"><?php echo htmlspecialchars($inicial); ?></div>
                <h1>Mi Perfil</h1>
                <p class="profile-subtitle">Gestiona la información de tu cuenta</p>
            </div>

            <div class="profile-body">

                <!-- Mensajes de Notificación -->
                <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'actualizado'): ?>
                    <div class="alert alert-success">
                        ✓ Perfil actualizado correctamente.
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-error">
                        ✕ No se pudo actualizar el perfil. Intenta nuevamente.
                    </div>
                <?php endif; ?>

                <p class="section-label">Datos de la cuenta</p>

                <!-- Formulario de Actualización (CRUD: Update) -->
                <form action="<?php echo BASE_URL; ?>app/controllers/EstudianteController.php?action=actualizarPerfil" method="POST" class="profile-form">

                    <div class="form-group">
                        <label for="nombre_completo">Nombre completo</label>
                        <input type="text" id="nombre_completo" name="nombre_completo" value="<?php echo htmlspecialchars($cuenta['nombre_completo'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="correo">Correo electrónico</label>
                        <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($cuenta['correo'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="contrasenia">Nueva contraseña</label>
                        <input type="password" id="contrasenia" name="contrasenia" placeholder="Dejar en blanco para no cambiarla">
                        <span class="help-text">Solo se actualiza si escribes una nueva.</span>
                    </div>

                    <button type="submit" class="btn-submit">Guardar cambios</button>
                </form>

                <!-- Zona de Eliminación de Cuenta (CRUD: Delete) -->
                <div class="danger-zone">
                    <p class="danger-zone-title">Eliminar cuenta</p>
                    <p class="danger-zone-text">Se borrará tu cuenta y toda tu información de forma permanente. Esta acción no se puede deshacer.</p>
                    <form action="<?php echo BASE_URL; ?>app/controllers/EstudianteController.php?action=eliminarPerfil" method="POST"
                        onsubmit="return confirm('¿Seguro que deseas eliminar tu cuenta? Esta acción no se puede deshacer.');">
                        <button type="submit" class="btn-delete">Eliminar mi cuenta definitivamente</button>
                    </form>
                </div>

            </div>
        </div>
    </main>
    <?php include ROOT_PATH . 'app/views/layouts/footer.php'; ?>

    <script>
        window.addEventListener('unload', function () {});
    </script>

</body>
</html>