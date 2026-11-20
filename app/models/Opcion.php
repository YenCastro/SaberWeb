<?php
// app/models/Opcion.php
// Opciones de respuesta (tabla `opciones_rta`). El guardado se hace desde Pregunta::guardarCompleta()
// para que pregunta y opciones se graben juntas en una transacción.
require_once __DIR__ . '/../config/database.php';

class Opcion {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // CONSULTAR: opciones de varias preguntas, agrupadas por id_pregunta y ordenadas por letra
    public function listarPorPreguntas(array $idsPregunta) {
        $agrupadas = [];
        if (count($idsPregunta) === 0) {
            return $agrupadas;
        }
        try {
            $ids = array_map('intval', $idsPregunta);
            $marcadores = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $this->conn->prepare(
                "SELECT * FROM opciones_rta WHERE id_pregunta IN ($marcadores) ORDER BY id_pregunta, letra"
            );
            $stmt->execute($ids);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
                $agrupadas[$fila['id_pregunta']][] = $fila;
            }
        } catch (PDOException $e) {
            // devuelve lo que haya
        }
        return $agrupadas;
    }

    // CONSULTAR: opciones de una pregunta, indexadas por letra (A, B, C...)
    public function listarPorPregunta($idPregunta) {
        $porLetra = [];
        try {
            $stmt = $this->conn->prepare("SELECT * FROM opciones_rta WHERE id_pregunta = :id ORDER BY letra");
            $stmt->bindValue(":id", (int)$idPregunta, PDO::PARAM_INT);
            $stmt->execute();
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
                $porLetra[$fila['letra']] = $fila;
            }
        } catch (PDOException $e) {
        }
        return $porLetra;
    }
}
