<?php
// app/controllers/AuthController.php

session_start();
require_once __DIR__ . '/../models/Cuenta.php';

class AuthController {
    private $cuentaModel;

    public function __construct() {
        $this->cuentaModel = new Cuenta();
    }

    // Procesar el registro de un nuevo usuario
    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre   = trim($_POST['nombre'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (!empty($nombre) && !empty($email) && !empty($password)) {
                $exito = $this->cuentaModel->registrar($nombre, $email, $password);

                if ($exito) {
                    header("Location: ../views/public/login.php?mensaje=registro_exitoso");
                    exit();
                } else {
                    header("Location: ../views/public/registro.php?error=correo_existente");
                    exit();
                }
            } else {
                header("Location: ../views/public/registro.php?error=campos_vacios");
                exit();
            }
        }
    }

    // Procesar el inicio de sesión
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (!empty($email) && !empty($password)) {
                $usuario = $this->cuentaModel->login($email, $password);

                if ($usuario) {
                    // Crear variables de sesión
                    $_SESSION['usuario_id']     = $usuario['id_cuenta'];
                    $_SESSION['usuario_nombre'] = $usuario['nombre_completo'];
                    $_SESSION['usuario_rol']    = $usuario['rol'];

                    // Redirigir según el rol
                    if ($usuario['rol'] === 'Administrador') {
                        header("Location: ../views/admin/dashboard.php");
                    } else {
                        header("Location: ../views/estudiante/dashboard_estudiante.php");
                    }
                    exit();
                } else {
                    header("Location: ../views/public/login.php?error=autenticacion_fallida");
                    exit();
                }
            } else {
                header("Location: ../views/public/login.php?error=campos_vacios");
                exit();
            }
        }
    }

    // Procesar el cierre de sesión
    public function logout() {
        session_destroy();
        header("Location: ../views/public/login.php?mensaje=sesion_cerrada");
        exit();
    }
} // ← Cierre correcto de la clase AuthController

// Enrutador para ejecutar la acción enviada por URL (?action=...)
if (isset($_GET['action'])) {
    $controller = new AuthController();

    if ($_GET['action'] === 'registrar') {
        $controller->registrar();
    } elseif ($_GET['action'] === 'login') {
        $controller->login();
    } elseif ($_GET['action'] === 'logout') {
        $controller->logout();
    }
}
?>