<?php
// app/controllers/ReporteController.php
//   crear      -> el ESTUDIANTE envía un reporte o sugerencia
//   actualizar -> el ADMINISTRADOR cambia el estado y, si quiere, responde (se avisa al estudiante)

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Reporte.php';
require_once __DIR__ . '/../models/Notificacion.php';
require_once __DIR__ . '/../models/Resultado.php';

class ReporteController {
    private $reporteModel;
    private $notificacionModel;

    public function __construct() {
        $this->reporteModel      = new Reporte();
        $this->notificacionModel = new Notificacion();
    }

    // ruta relativa a app/views/
    private function redirigir($ruta, array $params = []) {
        $url = BASE_URL . 'app/views/' . $ruta;
        if (count($params) > 0) {
            $url .= '?' . http_build_query($params);
        }
        header("Location: " . $url);
        exit();
    }

    private function exigirPost($rutaError) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valido()) {
            $this->redirigir($rutaError, ['error' => 'solicitud_invalida']);
        }
    }

    // ---------------------------------------------------------------
    // ESTUDIANTE
    // ---------------------------------------------------------------
    public function crear() {
        requerirSesion('Estudiante');
        $this->exigirPost('estudiante/reportar.php');
        $idCuenta = (int)$_SESSION['usuario_id'];

        $tipo        = $_POST['tipo'] ?? '';
        $idPregunta  = (int)($_POST['id_pregunta'] ?? 0);
        $idResultado = (int)($_POST['id_resultado'] ?? 0);
        $descripcion = trim($_POST['descripcion'] ?? '');

        // Para volver al formulario conservando la pregunta
        $volver = [];
        if ($idPregunta)  $volver['id_pregunta']  = $idPregunta;
        if ($idResultado) $volver['id_resultado'] = $idResultado;
        $conError = function ($codigo) use ($volver) {
            $this->redirigir('estudiante/reportar.php', array_merge($volver, ['error' => $codigo]));
        };

        if (!in_array($tipo, Reporte::TIPOS, true)) {
            $conError('solicitud_invalida');
        }

        if ($tipo === 'Sugerencia') {
            $idPregunta = 0; // las sugerencias son generales
        } else {
            if (!$idPregunta || !$this->reporteModel->preguntaParaReporte($idCuenta, $idPregunta)) {
                $conError('pregunta_invalida');
            }
        }

        $largo = mb_strlen($descripcion);
        if ($largo < 10 || $largo > 1000) {
            $conError('descripcion_invalida');
        }
        if ($this->reporteModel->contarHoy($idCuenta) >= Reporte::MAX_POR_DIA) {
            $conError('limite_diario');
        }
        if ($idPregunta && $this->reporteModel->existePendiente($idCuenta, $idPregunta, $tipo)) {
            $conError('duplicado');
        }

        if (!$this->reporteModel->crear($idCuenta, $idPregunta ?: null, $tipo, $descripcion)) {
            $conError('no_se_pudo_guardar');
        }
        $this->redirigir('estudiante/reportar.php', ['mensaje' => 'enviado']);
    }

    // ---------------------------------------------------------------
    // ADMINISTRADOR
    // ---------------------------------------------------------------
    public function actualizar() {
        requerirSesion('Administrador');

        // Para volver al listado con los mismos filtros
        $filtros = [];
        if (in_array($_POST['f_estado'] ?? '', Reporte::ESTADOS, true)) $filtros['estado'] = $_POST['f_estado'];
        if (in_array($_POST['f_tipo'] ?? '', Reporte::TIPOS, true))     $filtros['tipo']   = $_POST['f_tipo'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valido()) {
            $this->redirigir('admin/reportes.php', array_merge($filtros, ['error' => 'solicitud_invalida']));
        }

        $idReporte = (int)($_POST['id_reporte'] ?? 0);
        $estado    = $_POST['estado'] ?? '';
        $mensaje   = trim($_POST['mensaje'] ?? '');

        $reporte = $this->reporteModel->obtener($idReporte);
        if (!$reporte || !in_array($estado, Reporte::ESTADOS, true) || mb_strlen($mensaje) > 500) {
            $this->redirigir('admin/reportes.php', array_merge($filtros, ['error' => 'reporte_invalido']));
        }

        $cambio = ($estado !== $reporte['estado']);
        if ($cambio && !$this->reporteModel->actualizarEstado($idReporte, $estado)) {
            $this->redirigir('admin/reportes.php', array_merge($filtros, ['error' => 'no_se_pudo_guardar']));
        }

        // Se avisa al estudiante cuando el reporte avanza (Visto o Resuelto) o cuando el admin le escribe algo
        $avisar = ($cambio && $estado !== 'Pendiente') || $mensaje !== '';
        if ($avisar) {
            if ($estado === 'Resuelto')      $titulo = 'Tu reporte fue resuelto';
            elseif ($estado === 'Visto')     $titulo = 'Estamos revisando tu reporte';
            else                             $titulo = 'Respuesta a tu reporte';

            $queEs = ['Error' => 'error en una pregunta', 'Pregunta' => 'duda sobre una pregunta', 'Sugerencia' => 'sugerencia'];
            $texto = 'Sobre tu ' . $queEs[$reporte['tipo']];
            if ($reporte['numero_pregunta'] !== null) {
                $texto .= ' (pregunta ' . $reporte['numero_pregunta'] . ' de «' . $reporte['prueba_titulo'] . '»)';
            }
            $extracto = mb_substr($reporte['descripcion'], 0, 80);
            if (mb_strlen($reporte['descripcion']) > 80) $extracto .= '…';
            $texto .= ': «' . $extracto . '»';
            if ($mensaje !== '') {
                $texto .= "\n\nRespuesta del equipo: " . $mensaje;
            }
            $this->notificacionModel->crear($reporte['id_cuenta'], $titulo, $texto);
        }

        $this->redirigir('admin/reportes.php', array_merge($filtros, ['mensaje' => 'reporte_actualizado']));
    }
}

// Enrutador: solo acciones permitidas
if (isset($_GET['action'])) {
    $accionesPermitidas = ['crear', 'actualizar'];
    if (in_array($_GET['action'], $accionesPermitidas, true)) {
        $controller = new ReporteController();
        $controller->{$_GET['action']}();
    }
}
