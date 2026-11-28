<?php
// app/models/Notificacion.php
// Avisos para el estudiante (tabla `notificaciones`): respuesta a un reporte, nuevo simulacro publicado...
require_once __DIR__ . '/../config/database.php';

class Notificacion {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // INSERTAR: una notificación para una cuenta
    public function crear($idCuenta, $titulo, $mensaje) {
        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO notificaciones (id_cuenta, titulo, mensaje) VALUES (:c, :t, :m)"
            );
            return $stmt->execute([
                ":c" => (int)$idCuenta,
                ":t" => mb_substr($titulo, 0, 200),
                ":m" => $mensaje
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // INSERTAR: la misma notificación para todos los estudiantes.
    // No repite el aviso a quien ya recibió uno con el mismo título en las últimas 24 horas
    // (así, publicar y despublicar varias veces seguidas no llena de avisos a nadie).
    // Devuelve cuántas notificaciones se crearon.
    public function crearParaEstudiantes($titulo, $mensaje) {
        try {
            $titulo = mb_substr($titulo, 0, 200);
            $stmt = $this->conn->prepare(
                "INSERT INTO notificaciones (id_cuenta, titulo, mensaje)
                 SELECT c.id_cuenta, :t1, :m
                 FROM cuentas c
                 WHERE c.rol = 'Estudiante'
                   AND NOT EXISTS (
                       SELECT 1 FROM notificaciones n
                       WHERE n.id_cuenta = c.id_cuenta AND n.titulo = :t2
                         AND n.fecha_notificacion > (NOW() - INTERVAL 1 DAY)
                   )"
            );
            $stmt->execute([":t1" => $titulo, ":m" => $mensaje, ":t2" => $titulo]);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            return 0;
        }
    }

    // CONSULTAR: notificaciones de una cuenta, la más reciente primero
    public function listarPorCuenta($idCuenta, $limite = 100) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT * FROM notificaciones WHERE id_cuenta = :c
                 ORDER BY fecha_notificacion DESC, id_notificacion DESC LIMIT :l"
            );
            $stmt->bindValue(":c", (int)$idCuenta, PDO::PARAM_INT);
            $stmt->bindValue(":l", (int)$limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // CONSULTAR: cuántas no ha leído (para el numerito del navbar)
    public function contarNoLeidas($idCuenta) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT COUNT(*) FROM notificaciones WHERE id_cuenta = :c AND visto = 0"
            );
            $stmt->execute([":c" => (int)$idCuenta]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    // ACTUALIZAR: marcar una como leída (solo si es de esa cuenta)
    public function marcarLeida($idNotificacion, $idCuenta) {
        try {
            $stmt = $this->conn->prepare(
                "UPDATE notificaciones SET visto = 1 WHERE id_notificacion = :n AND id_cuenta = :c"
            );
            return $stmt->execute([":n" => (int)$idNotificacion, ":c" => (int)$idCuenta]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // ACTUALIZAR: marcar todas como leídas
    public function marcarTodasLeidas($idCuenta) {
        try {
            $stmt = $this->conn->prepare("UPDATE notificaciones SET visto = 1 WHERE id_cuenta = :c AND visto = 0");
            return $stmt->execute([":c" => (int)$idCuenta]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
