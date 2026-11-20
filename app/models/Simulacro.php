<?php
// app/models/Simulacro.php
// Un SIMULACRO es una sesión de una prueba (tabla `simulacro`), con su duración en minutos.
require_once __DIR__ . '/../config/database.php';

class Simulacro {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // CONSULTAR: sesiones de una prueba, con su número de preguntas
    public function listarPorPrueba($idPrueba) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT s.*, (SELECT COUNT(*) FROM preguntas q WHERE q.id_simulacro = s.id_simulacro) AS num_preguntas
                 FROM simulacro s WHERE s.id_prueba = :id ORDER BY s.id_simulacro ASC"
            );
            $stmt->bindValue(":id", (int)$idPrueba, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // CONSULTAR: una sesión con los datos de su prueba
    public function obtener($idSimulacro) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT s.*, p.titulo AS prueba_titulo, p.tipo AS prueba_tipo, p.estado AS prueba_estado
                 FROM simulacro s INNER JOIN pruebas p ON p.id_prueba = s.id_prueba
                 WHERE s.id_simulacro = :id LIMIT 1"
            );
            $stmt->bindValue(":id", (int)$idSimulacro, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    // INSERTAR
    public function crear($idPrueba, $titulo, $duracion) {
        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO simulacro (id_prueba, titulo, duracion_minutos) VALUES (:idPrueba, :titulo, :duracion)"
            );
            return $stmt->execute([":idPrueba" => (int)$idPrueba, ":titulo" => $titulo, ":duracion" => (int)$duracion]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // ACTUALIZAR
    public function actualizar($idSimulacro, $titulo, $duracion) {
        try {
            $stmt = $this->conn->prepare(
                "UPDATE simulacro SET titulo = :titulo, duracion_minutos = :duracion WHERE id_simulacro = :id"
            );
            return $stmt->execute([":titulo" => $titulo, ":duracion" => (int)$duracion, ":id" => (int)$idSimulacro]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // ELIMINAR: borra la sesión y sus preguntas. Falla si ya tiene resultados de estudiantes.
    public function eliminar($idSimulacro) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM simulacro WHERE id_simulacro = :id");
            return $stmt->execute([":id" => (int)$idSimulacro]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
