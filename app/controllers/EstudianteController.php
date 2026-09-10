<?php
// app/controllers/EstudianteController.php

session_start();
require_once __DIR__ . '/../models/Cuenta.php';

class EstudianteController {
    private $cuentaModel;

    public function __construct() {
        $this->cuentaModel = new Cuenta();
        $this->verificarSesion();
    }

    // CONSULTAR: ver los datos del propio perfil
    public function verPerfil() {
        $idCuenta = $_SESSION['usuario_id'];
        $cuenta = $this->cuentaModel->obtenerPorId($idCuenta);
        require __DIR__ . '/../views/estudiante/perfil.php';
    }

    // ACTUALIZAR: procesar los cambios del propio perfil
    public function actualizarPerfil() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idCuenta       = $_SESSION['usuario_id'];
            $nombreCompleto = trim($_POST['nombre_completo'] ?? '');
            $correo         = trim($_POST['correo'] ?? '');
            $contrasenia    = trim($_POST['contrasenia'] ?? '');

            if (!empty($nombreCompleto) && !empty($correo)) {
                $exito = $this->cuentaModel->actualizar($idCuenta, $nombreCompleto, $correo, $contrasenia ?: null);

                if ($exito) {
                    $_SESSION['usuario_nombre'] = $nombreCompleto;
                    header("Location: EstudianteController.php?action=verPerfil&mensaje=actualizado");
                } else {
                    header("Location: EstudianteController.php?action=verPerfil&error=actualizacion_fallida");
                }
                exit();
            }
        }
    }

    // ELIMINAR: el propio usuario borra su cuenta (nunca la de otro)
    public function eliminarPerfil() {
        $idCuenta = $_SESSION['usuario_id'];

        $this->cuentaModel->eliminar($idCuenta);

        session_destroy();
        header("Location: ../views/public/login.php?mensaje=cuenta_eliminada");
        exit();
    }

    // Verificar que exista una sesión activa
    private function verificarSesion() {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: ../views/public/login.php?error=sesion_requerida");
            exit();
        }
    }
}

// Enrutador para ejecutar la acción enviada por URL (?action=...)
if (isset($_GET['action'])) {
    $controller = new EstudianteController();

    switch ($_GET['action']) {
        case 'verPerfil':
            $controller->verPerfil();
            break;
        case 'actualizarPerfil':
            $controller->actualizarPerfil();
            break;
        case 'eliminarPerfil':
            $controller->eliminarPerfil();
            break;
    }
}
?>