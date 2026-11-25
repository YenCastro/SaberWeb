<?php
// app/models/RespuestaEstudiante.php
// Respuestas que el estudiante marca durante un intento (tabla `respuestas_estudiante`).
// Una sola fila por pregunta en cada intento: si cambia de opinión, se actualiza.
require_once __DIR__ . '/../config/database.php';

class RespuestaEstudiante {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Guarda (o cambia) la respuesta a una pregunta.
    // Valida que la pregunta sea de la sesión del intento y que la opción sea de esa pregunta;
    // si no, devuelve false. La corrección (es_correcta) la decide el servidor, nunca el navegador.
    public function guardar($idResultado, $idPregunta, $idOpcion) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT o.es_correcta
                 FROM opciones_rta o
                 INNER JOIN preguntas q ON q.id_pregunta = o.id_pregunta
                 INNER JOIN resultados r ON r.id_simulacro = q.id_simulacro
                 WHERE o.id_opcion = :op AND o.id_pregunta = :p AND r.id_resultado = :r"
            );
            $stmt->execute([":op" => (int)$idOpcion, ":p" => (int)$idPregunta, ":r" => (int)$idResultado]);
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$fila) {
                return false;
            }
            $esCorrecta = (int)$fila['es_correcta'];

            $stmt = $this->conn->prepare(
                "SELECT id_respuesta FROM respuestas_estudiante WHERE id_resultado = :r AND id_pregunta = :p LIMIT 1"
            );
            $stmt->execute([":r" => (int)$idResultado, ":p" => (int)$idPregunta]);
            $idRespuesta = $stmt->fetchColumn();

            if ($idRespuesta) {
                $stmt = $this->conn->prepare(
                    "UPDATE respuestas_estudiante SET id_opcion = :op, es_correcta = :c WHERE id_respuesta = :id"
                );
                return $stmt->execute([":op" => (int)$idOpcion, ":c" => $esCorrecta, ":id" => $idRespuesta]);
            }
            $stmt = $this->conn->prepare(
                "INSERT INTO respuestas_estudiante (id_resultado, id_pregunta, id_opcion, es_correcta)
                 VALUES (:r, :p, :op, :c)"
            );
            return $stmt->execute([":r" => (int)$idResultado, ":p" => (int)$idPregunta, ":op" => (int)$idOpcion, ":c" => $esCorrecta]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // CONSULTAR: respuestas de un intento como [id_pregunta => id_opcion]
    public function listarPorResultado($idResultado) {
        $mapa = [];
        try {
            $stmt = $this->conn->prepare(
                "SELECT id_pregunta, id_opcion FROM respuestas_estudiante WHERE id_resultado = :r"
            );
            $stmt->execute([":r" => (int)$idResultado]);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
                $mapa[(int)$fila['id_pregunta']] = (int)$fila['id_opcion'];
            }
        } catch (PDOException $e) {
        }
        return $mapa;
    }
}
