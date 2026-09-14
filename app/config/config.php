<?php
// app/config/config.php

// Ruta absoluta del proyecto en el servidor
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'] . '/Proyecto SENA SaberWeb/');

// URL base del proyecto.
define('BASE_URL', '/Proyecto SENA SaberWeb/');

// Verifica que haya una sesión activa con el rol indicado. Si no la hay, redirige a login y evita que el navegador guarde
function requerirSesion($rolRequerido) {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");

    if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== $rolRequerido) {
        header("Location: " . BASE_URL . "app/views/public/login.php?error=acceso_requerido");
        exit();
    }
}