<?php
session_start();

// 1. Validar que la sesión esté activa y sea un estudiante
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Estudiante') {
    header("Location: ../public/login.php?error=acceso_requerido");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SaberWeb - Panel de Estudiante</title>
    <!-- Rutas corregidas para acceder a los assets desde app/views/estudiante/ -->
    <link rel="stylesheet" href="../../../public/assets/css/public/inicio.css">
    <link rel="stylesheet" href="../../../public/assets/css/footer.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <header class="navbar">
        <div class="logo">
            <a href="dashboard_estudiante.php">
                <img src="../../../public/assets/img/logo/logo.png" alt="SaberWeb Logo" class="logo-img">
            </a>
        </div>
        
        <nav class="nav-links">
            <a href="dashboard_estudiante.php" class="active">Inicio</a>
            <a href="simulacros.php">Simulacros</a>
            <a href="resultado.php">Resultados</a>
            <a href="perfil.php">Mi Perfil</a>
        </nav>
        
        <div class="auth-buttons">
            <!-- Muestra el nombre cargado desde la sesión -->
            <span style="font-weight: 600; color: #0B2D4D; margin-right: 10px;">
                Hola, <?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Estudiante'); ?>
            </span>
            <a href="../../controllers/AuthController.php?action=logout" class="btn-outline">Cerrar Sesión</a>
        </div>
    </header>

 <!-- HERO SECTION -->
    <section class="hero">
        <div class="hero-content">
            <span class="badge">Simulacros ICFES Saber 11° y UNAL</span>
            <h1>Prepárate hoy, alcanza tu <span class="highlight-blue">mejor versión</span> mañana.</h1>
            <p>Practica con simulacros reales, refuerza tus conocimientos y llega con confianza al examen Saber 11° y
                UNAL.</p>

            <div class="hero-actions">
                <a href="simulacros.php" class="btn-primary-icon">
                    Comenzar ahora <i class="fa-solid fa-arrow-right"></i>
                </a>
                <a href="#como-funciona" class="btn-outline-icon">
                    Conoce más <i class="fa-regular fa-play-circle"></i>
                </a>
            </div>

            <div class="stats-container">
                <div class="stat-item">
                    <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
                    <div>
                        <strong>+50.000</strong>
                        <p>estudiantes preparándose</p>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon"><i class="fa-solid fa-book-open"></i></div>
                    <div>
                        <strong>+10.000</strong>
                        <p>simulacros realizados</p>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon"><i class="fa-solid fa-award"></i></div>
                    <div>
                        <strong>Mejora</strong>
                        <p>tu puntaje y alcanza tus metas</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="hero-image-container">
            <div class="blue-shape"></div>
            <img src="../../../public/assets/img/fondos/Home.png" alt="Estudiante SaberWeb" class="hero-img">
        </div>
    </section>

    <!-- CARDS FEATURES -->
    <section class="features-section">
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="fa-solid fa-globe"></i></div>
                <h3>Simulacros reales</h3>
                <p>Practica con preguntas actualizadas del examen Saber 11° y examen UNAL.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fa-solid fa-chart-column"></i></div>
                <h3>Resultados detallados</h3>
                <p>Obtén análisis por desempeño y nivel de dificultad.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fa-solid fa-book-bookmark"></i></div>
                <h3>Aprende y mejora</h3>
                <p>Explicaciones claras para que entiendas tus errores y sigas avanzando.</p>
            </div>
            <div class="feature-card-3">
                <div class="feature-icon"><i class="fa-regular fa-clock"></i></div>
                <h3>A tu ritmo</h3>
                <p>Practica cuando quieras, donde quieras y desde cualquier dispositivo.</p>
            </div>
        </div>
    </section>

    <!-- CÓMO FUNCIONA -->
    <section class="how-it-works" id="como-funciona">
        <h2>¿Cómo funciona <span>SaberWeb</span>?</h2>
        <div class="steps-container">
            <div class="step-item">
                <div class="step-icon-wrapper">
                    <a href="../app/views/public/registro.php" class="step-icon">
                        <i class="fa-solid fa-user-plus"></i>
                    </a>
                    <span class="step-number">1</span>
                </div>
                <h3>Crea tu cuenta</h3>
                <p>Regístrate gratis y accede a todas las herramientas.</p>
            </div>
            <div class="step-line"></div>
            <div class="step-item">
                <div class="step-icon-wrapper">
                    <a href="simulacros.php" class="step-icon">
                        <i class="fa-solid fa-copy"></i>
                    </a>
                    <span class="step-number">2</span>
                </div>
                <h3>Elige un simulacro</h3>
                <p>Selecciona el simulacro que deseas presentar.</p>
            </div>
            <div class="step-line"></div>
            <div class="step-item">
                <div class="step-icon-wrapper">
                    <a href="#" class="step-icon">
                        <i class="fa-solid fa-pen"></i>
                    </a>
                    <span class="step-number">3</span>
                </div>
                <h3>Responde y aprende</h3>
                <p>Resuelve las preguntas y recibe retroalimentación inmediata.</p>
            </div>
            <div class="step-line"></div>
            <div class="step-item">
                <div class="step-icon-wrapper">
                    <a href="resultados.php" class="step-icon">
                        <i class="fa-solid fa-chart-line"></i>
                    </a>
                    <span class="step-number">4</span>
                </div>
                <h3>Mejora tu puntaje</h3>
                <p>Analiza tus resultados, identifica debilidades y sigue avanzando.</p>
            </div>
        </div>
    </section>

    <script src="script.js"></script>
    <?php include ROOT_PATH . 'app/views/layouts/footer.php'; ?>
</body>
</html>