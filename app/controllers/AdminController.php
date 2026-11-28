<?php
// app/controllers/AdminController.php
// Acciones del administrador: pruebas, sesiones (simulacros) y preguntas.
// Todas las acciones que modifican datos llegan por POST con token CSRF.

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Prueba.php';
require_once __DIR__ . '/../models/Simulacro.php';
require_once __DIR__ . '/../models/Pregunta.php';
require_once __DIR__ . '/../models/Notificacion.php';

class AdminController {
    private $pruebaModel;
    private $simulacroModel;
    private $preguntaModel;

    public function __construct() {
        requerirSesion('Administrador');
        $this->pruebaModel    = new Prueba();
        $this->simulacroModel = new Simulacro();
        $this->preguntaModel  = new Pregunta();
    }

    // ---------------------------------------------------------------
    // Utilidades internas
    // ---------------------------------------------------------------
    private function redirigir($ruta, array $params = []) {
        $url = BASE_URL . 'app/views/admin/' . $ruta;
        if (count($params) > 0) {
            $url .= '?' . http_build_query($params);
        }
        header("Location: " . $url);
        exit();
    }

    // Exige POST + token CSRF
    private function exigirPost() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valido()) {
            $this->redirigir('simulacros.php', ['error' => 'solicitud_invalida']);
        }
    }

    // Lee y valida los datos generales de una prueba. Devuelve array o null si algo está mal.
    private function leerDatosPrueba() {
        $titulo      = trim($_POST['titulo'] ?? '');
        $tipo        = $_POST['tipo'] ?? '';
        $anio        = filter_var($_POST['anio'] ?? '', FILTER_VALIDATE_INT);
        $descripcion = trim($_POST['descripcion'] ?? '');

        if ($titulo === '' || mb_strlen($titulo) > 200) return null;
        if (!in_array($tipo, ['ICFES', 'UNAL'], true)) return null;
        if ($anio === false || $anio < 2000 || $anio > 2100) return null;
        if (mb_strlen($descripcion) > 2000) return null;

        return ['titulo' => $titulo, 'tipo' => $tipo, 'anio' => $anio, 'descripcion' => $descripcion];
    }

    // Valida el título y la duración (1 a 600 minutos) de una sesión
    private function validarSesion($titulo, $duracion) {
        $titulo   = trim($titulo);
        $duracion = filter_var($duracion, FILTER_VALIDATE_INT);
        if ($titulo === '' || mb_strlen($titulo) > 200) return null;
        if ($duracion === false || $duracion < 1 || $duracion > 600) return null;
        return ['titulo' => $titulo, 'duracion' => $duracion];
    }

    // ---------------------------------------------------------------
    // PRUEBAS
    // ---------------------------------------------------------------
    public function crearPrueba() {
        $this->exigirPost();

        $datos = $this->leerDatosPrueba();
        if (!$datos) {
            $this->redirigir('crear_simulacro.php', ['error' => 'datos_invalidos']);
        }

        // Sesiones enviadas por el formulario (arrays paralelos)
        $titulos    = $_POST['sesion_titulo'] ?? [];
        $duraciones = $_POST['sesion_duracion'] ?? [];
        $sesiones = [];
        if (is_array($titulos) && is_array($duraciones)) {
            foreach ($titulos as $i => $titulo) {
                $sesion = $this->validarSesion($titulo, $duraciones[$i] ?? '');
                if (!$sesion) {
                    $this->redirigir('crear_simulacro.php', ['error' => 'sesion_invalida']);
                }
                $sesiones[] = $sesion;
            }
        }
        if (count($sesiones) === 0) {
            $this->redirigir('crear_simulacro.php', ['error' => 'sin_sesiones']);
        }

        $idPrueba = $this->pruebaModel->crearConSesiones(
            $datos['titulo'], $datos['tipo'], $datos['anio'], $datos['descripcion'], $sesiones
        );
        if (!$idPrueba) {
            $this->redirigir('crear_simulacro.php', ['error' => 'no_se_pudo_guardar']);
        }
        $this->redirigir('editar_simulacro.php', ['id' => $idPrueba, 'mensaje' => 'prueba_creada']);
    }

    public function actualizarPrueba() {
        $this->exigirPost();
        $idPrueba = (int)($_POST['id_prueba'] ?? 0);

        $datos = $this->leerDatosPrueba();
        if (!$datos) {
            $this->redirigir('editar_simulacro.php', ['id' => $idPrueba, 'error' => 'datos_invalidos']);
        }
        $ok = $this->pruebaModel->actualizar($idPrueba, $datos['titulo'], $datos['tipo'], $datos['anio'], $datos['descripcion']);
        $this->redirigir('editar_simulacro.php', ['id' => $idPrueba, $ok ? 'mensaje' : 'error' => $ok ? 'prueba_actualizada' : 'no_se_pudo_guardar']);
    }

    public function cambiarEstado() {
        $this->exigirPost();
        $idPrueba = (int)($_POST['id_prueba'] ?? 0);
        $estado   = $_POST['estado'] ?? '';
        $volver   = (($_POST['volver'] ?? '') === 'editar') ? 'editar' : 'lista';

        $destino = function (array $params) use ($volver, $idPrueba) {
            if ($volver === 'editar') {
                $this->redirigir('editar_simulacro.php', array_merge(['id' => $idPrueba], $params));
            }
            $this->redirigir('simulacros.php', $params);
        };

        $prueba = $this->pruebaModel->obtener($idPrueba);
        if (!$prueba || !in_array($estado, ['Prueba', 'Publicado'], true)) {
            $destino(['error' => 'solicitud_invalida']);
        }

        if ($estado === 'Publicado') {
            $problemas = $this->pruebaModel->problemasParaPublicar($idPrueba);
            if (count($problemas) > 0) {
                $_SESSION['flash_problemas'] = $problemas;
                $destino(['error' => 'no_publicable']);
            }
        }

        $ok = $this->pruebaModel->cambiarEstado($idPrueba, $estado);

        // Al publicar por primera vez, se avisa a todos los estudiantes
        if ($ok && $estado === 'Publicado' && $prueba['estado'] !== 'Publicado') {
            (new Notificacion())->crearParaEstudiantes(
                'Nuevo simulacro: ' . $prueba['titulo'],
                'Ya está disponible «' . $prueba['titulo'] . '» (' . $prueba['tipo'] . ' ' . $prueba['anio'] . '). Entra a Simulacros para presentarlo.'
            );
        }
        $destino($ok
            ? ['mensaje' => ($estado === 'Publicado') ? 'publicada' : 'despublicada']
            : ['error' => 'no_se_pudo_guardar']);
    }

    public function eliminarPrueba() {
        $this->exigirPost();
        $idPrueba = (int)($_POST['id_prueba'] ?? 0);
        $prueba = $this->pruebaModel->obtener($idPrueba);

        if (!$prueba) {
            $this->redirigir('simulacros.php', ['error' => 'solicitud_invalida']);
        }
        if ($prueba['estado'] === 'Publicado') {
            $this->redirigir('simulacros.php', ['error' => 'despublicar_primero']);
        }
        $ok = $this->pruebaModel->eliminar($idPrueba);
        $this->redirigir('simulacros.php', $ok ? ['mensaje' => 'prueba_eliminada'] : ['error' => 'no_se_puede_eliminar']);
    }

    // ---------------------------------------------------------------
    // SESIONES (tabla simulacro)
    // ---------------------------------------------------------------
    public function crearSesion() {
        $this->exigirPost();
        $idPrueba = (int)($_POST['id_prueba'] ?? 0);

        $sesion = $this->validarSesion($_POST['titulo'] ?? '', $_POST['duracion'] ?? '');
        if (!$this->pruebaModel->obtener($idPrueba) || !$sesion) {
            $this->redirigir('editar_simulacro.php', ['id' => $idPrueba, 'error' => 'sesion_invalida']);
        }
        $ok = $this->simulacroModel->crear($idPrueba, $sesion['titulo'], $sesion['duracion']);
        $this->redirigir('editar_simulacro.php', ['id' => $idPrueba, $ok ? 'mensaje' : 'error' => $ok ? 'sesion_creada' : 'no_se_pudo_guardar']);
    }

    public function actualizarSesion() {
        $this->exigirPost();
        $idSimulacro = (int)($_POST['id_simulacro'] ?? 0);
        $actual = $this->simulacroModel->obtener($idSimulacro);
        if (!$actual) {
            $this->redirigir('simulacros.php', ['error' => 'solicitud_invalida']);
        }
        $idPrueba = (int)$actual['id_prueba'];

        $sesion = $this->validarSesion($_POST['titulo'] ?? '', $_POST['duracion'] ?? '');
        if (!$sesion) {
            $this->redirigir('editar_simulacro.php', ['id' => $idPrueba, 'error' => 'sesion_invalida']);
        }
        $ok = $this->simulacroModel->actualizar($idSimulacro, $sesion['titulo'], $sesion['duracion']);
        $this->redirigir('editar_simulacro.php', ['id' => $idPrueba, $ok ? 'mensaje' : 'error' => $ok ? 'sesion_actualizada' : 'no_se_pudo_guardar']);
    }

    public function eliminarSesion() {
        $this->exigirPost();
        $idSimulacro = (int)($_POST['id_simulacro'] ?? 0);
        $actual = $this->simulacroModel->obtener($idSimulacro);
        if (!$actual) {
            $this->redirigir('simulacros.php', ['error' => 'solicitud_invalida']);
        }
        $idPrueba = (int)$actual['id_prueba'];

        if ($actual['prueba_estado'] === 'Publicado') {
            $this->redirigir('editar_simulacro.php', ['id' => $idPrueba, 'error' => 'despublicar_primero']);
        }
        $ok = $this->simulacroModel->eliminar($idSimulacro);
        $this->redirigir('editar_simulacro.php', ['id' => $idPrueba, $ok ? 'mensaje' : 'error' => $ok ? 'sesion_eliminada' : 'no_se_puede_eliminar']);
    }

    // ---------------------------------------------------------------
    // PREGUNTAS
    // ---------------------------------------------------------------
    public function guardarPregunta() {
        $this->exigirPost();
        $idSimulacro = (int)($_POST['id_simulacro'] ?? 0);
        $idPregunta  = (int)($_POST['id_pregunta'] ?? 0) ?: null;

        if (!$this->simulacroModel->obtener($idSimulacro)) {
            $this->redirigir('preguntas.php', ['error' => 'solicitud_invalida']);
        }
        if ($idPregunta) {
            $existente = $this->preguntaModel->obtener($idPregunta);
            if (!$existente || (int)$existente['id_simulacro'] !== $idSimulacro) {
                $this->redirigir('preguntas.php', ['id_simulacro' => $idSimulacro, 'error' => 'solicitud_invalida']);
            }
        }

        $volverConError = function ($codigo) use ($idSimulacro, $idPregunta) {
            $params = ['id_simulacro' => $idSimulacro, 'error' => $codigo];
            if ($idPregunta) $params['editar'] = $idPregunta;
            $this->redirigir('preguntas.php', $params);
        };

        $enunciado = trim($_POST['enunciado'] ?? '');
        $area      = trim($_POST['area'] ?? '');
        $correcta  = $_POST['correcta'] ?? '';
        $opciones  = [];
        foreach (Pregunta::LETRAS as $letra) {
            $opciones[$letra] = trim($_POST['opcion_' . $letra] ?? '');
        }

        if ($enunciado === '' || mb_strlen($enunciado) > 5000 || $area === '' || mb_strlen($area) > 100) {
            $volverConError('pregunta_invalida');
        }
        if ($opciones['A'] === '' || $opciones['B'] === '') {
            $volverConError('minimo_dos_opciones');
        }
        if (!in_array($correcta, Pregunta::LETRAS, true) || $opciones[$correcta] === '') {
            $volverConError('sin_correcta');
        }

        $id = $this->preguntaModel->guardarCompleta($idSimulacro, $idPregunta, $enunciado, $area, $opciones, $correcta);
        if (!$id) {
            $volverConError('no_se_pudo_guardar');
        }
        $this->redirigir('preguntas.php', ['id_simulacro' => $idSimulacro, 'mensaje' => $idPregunta ? 'pregunta_actualizada' : 'pregunta_creada']);
    }

    public function eliminarPregunta() {
        $this->exigirPost();
        $idPregunta = (int)($_POST['id_pregunta'] ?? 0);
        $pregunta = $this->preguntaModel->obtener($idPregunta);
        if (!$pregunta) {
            $this->redirigir('preguntas.php', ['error' => 'solicitud_invalida']);
        }
        $idSimulacro = (int)$pregunta['id_simulacro'];
        $ok = $this->preguntaModel->eliminar($idPregunta);
        $this->redirigir('preguntas.php', ['id_simulacro' => $idSimulacro, $ok ? 'mensaje' : 'error' => $ok ? 'pregunta_eliminada' : 'no_se_puede_eliminar']);
    }
}

// Enrutador: ejecuta la acción enviada por URL (?action=...). Solo acciones permitidas.
if (isset($_GET['action'])) {
    $accionesPermitidas = [
        'crearPrueba', 'actualizarPrueba', 'cambiarEstado', 'eliminarPrueba',
        'crearSesion', 'actualizarSesion', 'eliminarSesion',
        'guardarPregunta', 'eliminarPregunta'
    ];
    if (in_array($_GET['action'], $accionesPermitidas, true)) {
        $controller = new AdminController();
        $controller->{$_GET['action']}();
    }
}
