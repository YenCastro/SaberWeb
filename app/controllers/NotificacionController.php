<?php
// app/controllers/NotificacionController.php
// El estudiante marca sus notificaciones como leídas.

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Notificacion.php';

class NotificacionController {
    private $notificacionModel;

    public function __construct() {
        requerirSesion('Estudiante');
        $this->notificacionModel = new Notificacion();
    }

    private function volver(array $params = []) {
        $url = BASE_URL . 'app/views/estudiante/notificaciones.php';
        if (count($params) > 0) {
            $url .= '?' . http_build_query($params);
        }
        header("Location: " . $url);
        exit();
    }

    private function exigirPost() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valido()) {
            $this->volver(['error' => 'solicitud_invalida']);
        }
    }

    public function marcarLeida() {
        $this->exigirPost();
        $this->notificacionModel->marcarLeida((int)($_POST['id_notificacion'] ?? 0), $_SESSION['usuario_id']);
        $this->volver();
    }

    public function marcarTodas() {
        $this->exigirPost();
        $this->notificacionModel->marcarTodasLeidas($_SESSION['usuario_id']);
        $this->volver(['mensaje' => 'todas_leidas']);
    }
}

// Enrutador: solo acciones permitidas
if (isset($_GET['action'])) {
    $accionesPermitidas = ['marcarLeida', 'marcarTodas'];
    if (in_array($_GET['action'], $accionesPermitidas, true)) {
        $controller = new NotificacionController();
        $controller->{$_GET['action']}();
    }
}
