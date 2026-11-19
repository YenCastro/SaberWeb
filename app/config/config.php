<?php
// app/config/config.php

// Iniciar sesión una sola vez (seguro si otro archivo ya la inició)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ruta absoluta del proyecto en el servidor (se calcula sola: sube 2 niveles desde app/config/)
define('ROOT_PATH', str_replace('\\', '/', dirname(__DIR__, 2)) . '/');

// URL base del proyecto (se calcula sola a partir de DOCUMENT_ROOT).
// Funciona sin importar el nombre de la carpeta: /SaberWeb/, /Proyecto SENA SaberWeb/, etc.
// Si el proyecto está en la raíz del servidor, queda '/'.
(function () {
    $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '');
    $root    = rtrim(ROOT_PATH, '/');
    $relativa = '';

    if ($docRoot !== '' && stripos($root, $docRoot) === 0) {
        $relativa = substr($root, strlen($docRoot));
    }

    // Codificar cada segmento (los espacios pasan a %20)
    $segmentos = array_filter(explode('/', $relativa), 'strlen');
    $base = '/' . implode('/', array_map('rawurlencode', $segmentos));
    define('BASE_URL', rtrim($base, '/') . '/');
})();

// Verifica que haya una sesión activa con el rol indicado. Si no la hay, redirige a login y evita que el navegador guarde
function requerirSesion($rolRequerido) {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");

    if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== $rolRequerido) {
        header("Location: " . BASE_URL . "app/views/public/login.php?error=acceso_requerido");
        exit();
    }
}

// Enlace a "Simulacros" según quién navega (visitante, estudiante o administrador)
function urlSimulacros() {
    $rol = $_SESSION['usuario_rol'] ?? null;
    if ($rol === 'Estudiante')    return BASE_URL . 'app/views/estudiante/simulacros.php';
    if ($rol === 'Administrador') return BASE_URL . 'app/views/admin/simulacros.php';
    return BASE_URL . 'app/views/public/login.php?error=acceso_requerido';
}

// Enlace a "Resultados" según quién navega
function urlResultados() {
    $rol = $_SESSION['usuario_rol'] ?? null;
    if ($rol === 'Estudiante')    return BASE_URL . 'app/views/estudiante/historial.php';
    if ($rol === 'Administrador') return BASE_URL . 'app/views/admin/dashboard_admin.php';
    return BASE_URL . 'app/views/public/login.php?error=acceso_requerido';
}
