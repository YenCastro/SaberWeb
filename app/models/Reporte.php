<?php
// app/models/Reporte.php
// Reportes de los estudiantes (tabla `reportes`):
//   tipo 'Error'      -> hay un error en una pregunta
//   tipo 'Pregunta'   -> duda sobre una pregunta
//   tipo 'Sugerencia' -> idea o comentario general (sin pregunta)
require_once __DIR__ . '/../config/database.php';

class Reporte {
    private $conn;
    const TIPOS   = ['Error', 'Pregunta', 'Sugerencia'];
    const ESTADOS = ['Pendiente', 'Visto', 'Resuelto'];
    const MAX_POR_DIA = 10;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Devuelve la pregunta (con su prueba y sesión) SOLO si este estudiante ya la vio en un intento finalizado.
    // Así nadie puede reportar o leer preguntas de simulacros que no ha presentado.
    public function preguntaParaReporte($idCuenta, $idPregunta) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT q.id_pregunta, q.numero_pregunta, q.area, q.enunciado,
                        s.titulo AS sesion_titulo, p.titulo AS prueba_titulo
                 FROM preguntas q
                 INNER JOIN simulacro s ON s.id_simulacro = q.id_simulacro
                 INNER JOIN pruebas p ON p.id_prueba = s.id_prueba
                 WHERE q.id_pregunta = :p
                   AND EXISTS (SELECT 1 FROM resultados r
                               WHERE r.id_simulacro = q.id_simulacro AND r.id_cuenta = :c AND r.estado = 'Finalizado')
                 LIMIT 1"
            );
            $stmt->execute([":p" => (int)$idPregunta, ":c" => (int)$idCuenta]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    // ¿Ya envió un reporte igual (misma pregunta y tipo) que sigue pendiente?
    public function existePendiente($idCuenta, $idPregunta, $tipo) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT COUNT(*) FROM reportes
                 WHERE id_cuenta = :c AND id_pregunta = :p AND tipo = :t AND estado = 'Pendiente'"
            );
            $stmt->execute([":c" => (int)$idCuenta, ":p" => (int)$idPregunta, ":t" => $tipo]);
            return (int)$stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Cuántos reportes ha enviado hoy (límite contra el spam)
    public function contarHoy($idCuenta) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT COUNT(*) FROM reportes WHERE id_cuenta = :c AND fecha > (NOW() - INTERVAL 1 DAY)"
            );
            $stmt->execute([":c" => (int)$idCuenta]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    // INSERTAR
    public function crear($idCuenta, $idPregunta, $tipo, $descripcion) {
        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO reportes (id_cuenta, id_pregunta, tipo, descripcion) VALUES (:c, :p, :t, :d)"
            );
            return $stmt->execute([
                ":c" => (int)$idCuenta,
                ":p" => $idPregunta ? (int)$idPregunta : null,
                ":t" => $tipo,
                ":d" => $descripcion
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // CONSULTAR: reportes de un estudiante, con los datos de la pregunta si la hay
    public function listarPorCuenta($idCuenta) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT r.*, q.numero_pregunta, q.area, p.titulo AS prueba_titulo
                 FROM reportes r
                 LEFT JOIN preguntas q ON q.id_pregunta = r.id_pregunta
                 LEFT JOIN simulacro s ON s.id_simulacro = q.id_simulacro
                 LEFT JOIN pruebas p ON p.id_prueba = s.id_prueba
                 WHERE r.id_cuenta = :c
                 ORDER BY r.fecha DESC, r.id_reporte DESC"
            );
            $stmt->execute([":c" => (int)$idCuenta]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // CONSULTAR (admin): todos los reportes con filtros opcionales. Los pendientes salen primero.
    public function listarAdmin($estado = '', $tipo = '') {
        try {
            $stmt = $this->conn->prepare(
                "SELECT r.*, c.nombre_completo, c.correo,
                        q.numero_pregunta, q.area, q.enunciado, q.id_simulacro,
                        s.titulo AS sesion_titulo, p.titulo AS prueba_titulo
                 FROM reportes r
                 INNER JOIN cuentas c ON c.id_cuenta = r.id_cuenta
                 LEFT JOIN preguntas q ON q.id_pregunta = r.id_pregunta
                 LEFT JOIN simulacro s ON s.id_simulacro = q.id_simulacro
                 LEFT JOIN pruebas p ON p.id_prueba = s.id_prueba
                 WHERE (:e1 = '' OR r.estado = :e2) AND (:t1 = '' OR r.tipo = :t2)
                 ORDER BY FIELD(r.estado, 'Pendiente', 'Visto', 'Resuelto'), r.fecha DESC, r.id_reporte DESC
                 LIMIT 200"
            );
            $stmt->execute([":e1" => $estado, ":e2" => $estado, ":t1" => $tipo, ":t2" => $tipo]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // CONSULTAR: un reporte con los datos para armar la notificación
    public function obtener($idReporte) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT r.*, q.numero_pregunta, p.titulo AS prueba_titulo
                 FROM reportes r
                 LEFT JOIN preguntas q ON q.id_pregunta = r.id_pregunta
                 LEFT JOIN simulacro s ON s.id_simulacro = q.id_simulacro
                 LEFT JOIN pruebas p ON p.id_prueba = s.id_prueba
                 WHERE r.id_reporte = :id LIMIT 1"
            );
            $stmt->execute([":id" => (int)$idReporte]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    // ACTUALIZAR (admin)
    public function actualizarEstado($idReporte, $estado) {
        if (!in_array($estado, self::ESTADOS, true)) {
            return false;
        }
        try {
            $stmt = $this->conn->prepare("UPDATE reportes SET estado = :e WHERE id_reporte = :id");
            return $stmt->execute([":e" => $estado, ":id" => (int)$idReporte]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // CONSULTAR: cuántos hay por estado -> ['Pendiente' => n, 'Visto' => n, 'Resuelto' => n]
    public function contarPorEstado() {
        $conteo = ['Pendiente' => 0, 'Visto' => 0, 'Resuelto' => 0];
        try {
            $stmt = $this->conn->prepare("SELECT estado, COUNT(*) AS total FROM reportes GROUP BY estado");
            $stmt->execute();
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
                $conteo[$fila['estado']] = (int)$fila['total'];
            }
        } catch (PDOException $e) {
        }
        return $conteo;
    }

    public function contarPendientes() {
        return $this->contarPorEstado()['Pendiente'];
    }
}
