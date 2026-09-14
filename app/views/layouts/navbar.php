<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$paginaActual = basename($_SERVER['PHP_SELF']);
$rol = $_SESSION['usuario_rol'] ?? null;
?>
<header class="navbar">
    <div class="logo">
        <a href="<?php echo BASE_URL; ?>public/index.php">
            <img src="<?php echo BASE_URL; ?>public/assets/img/logo/logo.png" alt="SaberWeb Logo" class="logo-img">
        </a>
    </div>

    <?php if ($rol === 'Administrador'): ?>

        <!-- NAVBAR ADMINISTRADOR -->
        <nav class="nav-links">
            <a href="<?php echo BASE_URL; ?>app/views/admin/dashboard_admin.php" class="<?php echo ($paginaActual === 'dashboard_admin.php') ? 'active' : ''; ?>">Panel</a>
            <a href="<?php echo BASE_URL; ?>app/views/admin/simulacros.php" class="<?php echo ($paginaActual === 'simulacros.php') ? 'active' : ''; ?>">Simulacros</a>
            <a href="<?php echo BASE_URL; ?>app/views/admin/preguntas.php" class="<?php echo ($paginaActual === 'preguntas.php') ? 'active' : ''; ?>">Preguntas</a>
            <a href="<?php echo BASE_URL; ?>app/views/admin/reportes.php" class="<?php echo ($paginaActual === 'reportes.php') ? 'active' : ''; ?>">Reportes</a>
        </nav>
        <div class="auth-buttons user-buttons">
            <a href="<?php echo BASE_URL; ?>app/views/admin/perfil.php" class="navbar-profile">
                <i class="fa-solid fa-circle-user"></i>
                <span class="navbar-saludo"><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></span>
            </a>
            <a href="<?php echo BASE_URL; ?>app/controllers/AuthController.php?action=logout" class="btn-outline btn-logout">
                <i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión
            </a>
        </div>

    <?php elseif ($rol === 'Estudiante'): ?>

        <!-- NAVBAR ESTUDIANTE -->
        <nav class="nav-links">
            <a href="<?php echo BASE_URL; ?>app/views/estudiante/dashboard_estudiante.php" class="<?php echo ($paginaActual === 'dashboard_estudiante.php') ? 'active' : ''; ?>">Inicio</a>
            <a href="<?php echo BASE_URL; ?>app/views/estudiante/simulacros.php" class="<?php echo ($paginaActual === 'simulacros.php') ? 'active' : ''; ?>">Simulacros</a>
            <a href="<?php echo BASE_URL; ?>app/views/estudiante/resultado.php" class="<?php echo ($paginaActual === 'resultado.php') ? 'active' : ''; ?>">Resultados</a>
        </nav>
        <div class="auth-buttons user-buttons">
            <a href="<?php echo BASE_URL; ?>app/views/estudiante/perfil.php" class="navbar-profile <?php echo ($paginaActual === 'perfil.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-circle-user"></i>
                <span class="navbar-saludo"><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></span>
            </a>
            <a href="<?php echo BASE_URL; ?>app/controllers/AuthController.php?action=logout" class="btn-outline btn-logout">
                <i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión
            </a>
        </div>

    <?php else: ?>

        <!-- NAVBAR PÚBLICO (sin sesión) -->
        <nav class="nav-links">
            <a href="<?php echo BASE_URL; ?>public/index.php" class="<?php echo ($paginaActual === 'index.php') ? 'active' : ''; ?>">Inicio</a>
            <a href="<?php echo BASE_URL; ?>public/simulacros.php" class="<?php echo ($paginaActual === 'simulacros.php') ? 'active' : ''; ?>">Simulacros</a>
            <a href="<?php echo BASE_URL; ?>public/resultados.php" class="<?php echo ($paginaActual === 'resultados.php') ? 'active' : ''; ?>">Resultados</a>
        </nav>
        <div class="auth-buttons">
            <a href="<?php echo BASE_URL; ?>app/views/public/login.php" class="btn-outline">Iniciar Sesión</a>
            <a href="<?php echo BASE_URL; ?>app/views/public/registro.php" class="btn-primary">Regístrate</a>
        </div>

    <?php endif; ?>
</header>