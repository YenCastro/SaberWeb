<?php
// app/models/Resultado.php
// Un RESULTADO es un intento de un estudiante en una sesión (simulacro).
// Nace "En curso" cuando empieza el examen y pasa a "Finalizado" al enviarlo o al acabarse el tiempo.
// El tiempo se calcula SIEMPRE en la base de datos (NOW()), así el estudiante no puede alterarlo.
// puntaje = porcentaje de aciertos (0 a 100). Las preguntas sin responder cuentan como incorrectas.
require_once __DIR__ . '/../config/database.php';

class Resultado {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Devuelve el id del intento en curso de este estudiante en esta sesión, o crea uno nuevo.
    // Si el intento en curso ya se había quedado sin tiempo, lo cierra y empieza uno nuevo.
    public function iniciar($idCuenta, $idSimulacro) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT id_resultado FROM resultados
                 WHERE id_cuenta = :c AND id_simulacro = :s AND estado = 'En curso'
                 ORDER BY id_resultado DESC LIMIT 1"
            );
            $stmt->execute([":c" => (int)$idCuenta, ":s" => (int)$idSimulacro]);
            $existente = $stmt->fetchColumn();

            if ($existente) {
                $detalle = $this->obtener($existente, $idCuenta);
                if ($detalle && (int)$detalle['segundos_restantes'] > 0) {
                    return (int)$existente;
                }
                $this->finalizar($existente);
            }

            $stmt = $this->conn->prepare("INSERT INTO resultados (id_cuenta, id_simulacro) VALUES (:c, :s)");
            $stmt->execute([":c" => (int)$idCuenta, ":s" => (int)$idSimulacro]);
            return (int)$this->conn->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    // CONSULTAR: un intento (solo si es de esa cuenta), con los segundos que le quedan
    public function obtener($idResultado, $idCuenta) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT r.*, s.titulo AS sesion_titulo, s.duracion_minutos,
                        p.titulo AS prueba_titulo, p.tipo AS prueba_tipo,
                        TIMESTAMPDIFF(SECOND, NOW(), DATE_ADD(r.fecha_realizacion, INTERVAL s.duracion_minutos MINUTE)) AS segundos_restantes,
                        TIMESTAMPDIFF(SECOND, r.fecha_realizacion, r.fecha_fin) AS segundos_usados
                 FROM resultados r
                 INNER JOIN simulacro s ON s.id_simulacro = r.id_simulacro
                 INNER JOIN pruebas p ON p.id_prueba = s.id_prueba
                 WHERE r.id_resultado = :r AND r.id_cuenta = :c LIMIT 1"
            );
            $stmt->execute([":r" => (int)$idResultado, ":c" => (int)$idCuenta]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Cierra el intento: cuenta aciertos, calcula el puntaje y lo marca "Finalizado".
    // Devuelve false si ya estaba finalizado (así no se calcula dos veces).
    public function finalizar($idResultado) {
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare(
                "SELECT id_simulacro FROM resultados WHERE id_resultado = :r AND estado = 'En curso' FOR UPDATE"
            );
            $stmt->execute([":r" => (int)$idResultado]);
            $idSimulacro = $stmt->fetchColumn();
            if (!$idSimulacro) {
                $this->conn->rollBack();
                return false;
            }

            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM preguntas WHERE id_simulacro = :s");
            $stmt->execute([":s" => $idSimulacro]);
            $total = (int)$stmt->fetchColumn();

            $stmt = $this->conn->prepare(
                "SELECT COUNT(*) FROM respuestas_estudiante WHERE id_resultado = :r AND es_correcta = 1"
            );
            $stmt->execute([":r" => (int)$idResultado]);
            $correctas = (int)$stmt->fetchColumn();

            $incorrectas = max(0, $total - $correctas);
            $puntaje = ($total > 0) ? (int)round($correctas * 100 / $total) : 0;

            $stmt = $this->conn->prepare(
                "UPDATE resultados
                 SET puntaje = :p, respuestas_correctas = :c, respuestas_incorrectas = :i,
                     estado = 'Finalizado', fecha_fin = NOW()
                 WHERE id_resultado = :r"
            );
            $stmt->execute([":p" => $puntaje, ":c" => $correctas, ":i" => $incorrectas, ":r" => (int)$idResultado]);

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return false;
        }
    }

    // CONSULTAR: aciertos por área de un intento
    public function detallePorArea($idResultado, $idSimulacro) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT q.area, COUNT(*) AS total, COALESCE(SUM(re.es_correcta), 0) AS correctas
                 FROM preguntas q
                 LEFT JOIN respuestas_estudiante re ON re.id_pregunta = q.id_pregunta AND re.id_resultado = :r
                 WHERE q.id_simulacro = :s
                 GROUP BY q.area ORDER BY q.area ASC"
            );
            $stmt->execute([":r" => (int)$idResultado, ":s" => (int)$idSimulacro]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // CONSULTAR: por cada sesión, si el estudiante tiene un intento en curso o ya terminó alguno
    // Devuelve [id_simulacro => ['en_curso' => id|null, 'ultimo_finalizado' => id|null, 'intentos' => n]]
    public function resumenPorCuenta($idCuenta) {
        $resumen = [];
        try {
            $stmt = $this->conn->prepare(
                "SELECT id_simulacro,
                        MAX(CASE WHEN estado = 'En curso' THEN id_resultado END) AS en_curso,
                        MAX(CASE WHEN estado = 'Finalizado' THEN id_resultado END) AS ultimo_finalizado,
                        COUNT(*) AS intentos
                 FROM resultados WHERE id_cuenta = :c GROUP BY id_simulacro"
            );
            $stmt->execute([":c" => (int)$idCuenta]);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
                $resumen[$fila['id_simulacro']] = $fila;
            }
        } catch (PDOException $e) {
        }
        return $resumen;
    }
}
