<?php
// app/controllers/SimulacroController.php
// Acciones del estudiante durante un simulacro: iniciar, guardar respuesta y finalizar.
// Todas llegan por POST con token CSRF.

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Simulacro.php';
require_once __DIR__ . '/../models/Resultado.php';
require_once __DIR__ . '/../models/RespuestaEstudiante.php';

class SimulacroController {
    // Margen (segundos) que se tolera después de acabarse el tiempo, por la demora de la red
    const MARGEN_SEGUNDOS = 5;

    private $simulacroModel;
    private $resultadoModel;
    private $respuestaModel;

    public function __construct() {
        requerirSesion('Estudiante');
        $this->simulacroModel = new Simulacro();
        $this->resultadoModel = new Resultado();
        $this->respuestaModel = new RespuestaEstudiante();
    }

    private function redirigir($ruta, array $params = []) {
        $url = BASE_URL . 'app/views/estudiante/' . $ruta;
        if (count($params) > 0) {
            $url .= '?' . http_build_query($params);
        }
        header("Location: " . $url);
        exit();
    }

    private function exigirPost() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valido()) {
            $this->redirigir('simulacros.php', ['error' => 'solicitud_invalida']);
        }
    }

    private function responderJson($datos, $codigo = 200) {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($datos);
        exit();
    }

    // Empieza un intento (o retoma el que está en curso)
    public function iniciar() {
        $this->exigirPost();
        $idSimulacro = (int)($_POST['id_simulacro'] ?? 0);

        $sesion = $this->simulacroModel->obtener($idSimulacro);
        if (!$sesion || $sesion['prueba_estado'] !== 'Publicado') {
            $this->redirigir('simulacros.php', ['error' => 'no_disponible']);
        }

        $sesiones = $this->simulacroModel->listarPorPrueba($sesion['id_prueba']);
        foreach ($sesiones as $s) {
            if ((int)$s['id_simulacro'] === $idSimulacro && (int)$s['num_preguntas'] === 0) {
                $this->redirigir('simulacros.php', ['error' => 'sin_preguntas']);
            }
        }

        $idResultado = $this->resultadoModel->iniciar($_SESSION['usuario_id'], $idSimulacro);
        if (!$idResultado) {
            $this->redirigir('simulacros.php', ['error' => 'no_se_pudo_iniciar']);
        }
        $this->redirigir('realizar_simulacro.php', ['id_resultado' => $idResultado]);
    }

    // Guarda la respuesta de una pregunta (la llama el JavaScript del examen). Responde JSON.
    public function guardarRespuesta() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valido()) {
            $this->responderJson(['ok' => false, 'mensaje' => 'Solicitud no válida. Recarga la página.'], 403);
        }
        $idResultado = (int)($_POST['id_resultado'] ?? 0);
        $idPregunta  = (int)($_POST['id_pregunta'] ?? 0);
        $idOpcion    = (int)($_POST['id_opcion'] ?? 0);

        $resultado = $this->resultadoModel->obtener($idResultado, $_SESSION['usuario_id']);
        if (!$resultado) {
            $this->responderJson(['ok' => false, 'mensaje' => 'No se encontró el intento.'], 404);
        }
        if ($resultado['estado'] !== 'En curso' || (int)$resultado['segundos_restantes'] < -self::MARGEN_SEGUNDOS) {
            $this->responderJson(['ok' => false, 'expirado' => true, 'mensaje' => 'El tiempo terminó.'], 409);
        }
        if (!$this->respuestaModel->guardar($idResultado, $idPregunta, $idOpcion)) {
            $this->responderJson(['ok' => false, 'mensaje' => 'No se pudo guardar la respuesta.'], 400);
        }
        $this->responderJson(['ok' => true]);
    }

    // Envía el examen (botón "Finalizar" o tiempo agotado) y lleva al resultado
    public function finalizar() {
        $this->exigirPost();
        $idResultado = (int)($_POST['id_resultado'] ?? 0);

        $resultado = $this->resultadoModel->obtener($idResultado, $_SESSION['usuario_id']);
        if (!$resultado) {
            $this->redirigir('simulacros.php', ['error' => 'no_encontrado']);
        }
        $this->resultadoModel->finalizar($idResultado); // si ya estaba finalizado, no hace nada
        $params = ['id' => $idResultado];
        if ((int)$resultado['segundos_restantes'] <= 0 && $resultado['estado'] === 'En curso') {
            $params['mensaje'] = 'tiempo_agotado';
        }
        $this->redirigir('resultado.php', $params);
    }
}

// Enrutador: solo acciones permitidas
if (isset($_GET['action'])) {
    $accionesPermitidas = ['iniciar', 'guardarRespuesta', 'finalizar'];
    if (in_array($_GET['action'], $accionesPermitidas, true)) {
        $controller = new SimulacroController();
        $controller->{$_GET['action']}();
    }
}
