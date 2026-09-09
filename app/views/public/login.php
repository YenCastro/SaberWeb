<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SaberWeb - Iniciar Sesión</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../public/assets/css/public/login.css">
</head>

<body class="auth-body">

    <main class="auth-container">
        <div class="auth-card">

            <!-- PANEL IZQUIERDO -->
            <div class="auth-sidebar">
                <div class="sidebar-top">
                    <span class="welcome-badge">¡Hola de nuevo!</span>
                    <h1>Continúa evaluando tus <span class="highlight-text">conocimientos</span></h1>
                    <p>Accede a tus simulacros guardados y revisa el historial de tus calificaciones.</p>
                </div>

                <div class="sidebar-footer">
                    <div class="shield-icon"><i class="fa-solid fa-shield-halved"></i></div>
                    <div>
                        <strong>Acceso Seguro</strong>
                        <p>Tus datos están protegidos en todo momento.</p>
                    </div>
                </div>
            </div>

            <!-- PANEL DERECHO (FORMULARIO) -->
            <div class="auth-form-container">
                <a href="../../../public/index.php" class="btn-back">
                    <i class="fa-solid fa-arrow-left"></i> Volver al inicio
                </a>

                <div class="form-header">
                    <h2>Iniciar Sesión</h2>
                    <p>Ingresa tus credenciales para acceder</p>
                </div>

                <!-- MENSAJES DE ALERTA PROVENIENTES DEL CONTROLADOR -->
                <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'registro_exitoso'): ?>
                    <div style="padding: 10px; margin-bottom: 15px; border-radius: 8px; text-align: center; font-size: 0.85rem; background-color: #d1e7dd; color: #0f5132;">
                        ¡Registro completado! Ahora puedes iniciar sesión.
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div style="padding: 10px; margin-bottom: 15px; border-radius: 8px; text-align: center; font-size: 0.85rem; background-color: #f8d7da; color: #842029;">
                        <?php 
                            if ($_GET['error'] === 'autenticacion_fallida') echo "Error en la autenticación. Correo o contraseña incorrectos.";
                            if ($_GET['error'] === 'campos_vacios') echo "Por favor llena todos los campos.";
                        ?>
                    </div>
                <?php endif; ?>

                <!-- APUNTA AL CONTROLADOR MEDIANTE POST -->
                <form class="auth-form" action="../../controllers/AuthController.php?action=login" method="POST">
                    <div class="form-group">
                        <label for="email">Correo Electrónico</label>
                        <div class="input-wrapper">
                            <i class="fa-regular fa-envelope input-icon"></i>
                            <input type="email" id="email" name="email" placeholder="ejemplo@gmail.com" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock input-icon"></i>
                            <input type="password" id="password" name="password" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">Ingresar</button>

                    <p class="redirect-text">¿Aún no tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
                </form>
            </div>
        </div>
    </main>

    <script src="../../../public/assets/js/public/login.js"></script>
</body>

</html>