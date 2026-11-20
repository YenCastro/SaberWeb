<?php
// app/config/config.php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('ROOT_PATH', str_replace('\\', '/', dirname(__DIR__, 2)) . '/');


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

// ---------------------------------------------------------------------
// Ayudantes generales
// ---------------------------------------------------------------------

// Escapar texto antes de mostrarlo en HTML (evita XSS)
function e($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

// Token CSRF: protege los formularios que modifican datos
function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_campo() {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valido() {
    return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}
