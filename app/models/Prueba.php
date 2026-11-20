<?php
// app/models/Prueba.php
// Una PRUEBA es el examen completo (ej: "ICFES Saber 11 - 2026").
// Cada prueba tiene una o más sesiones (tabla `simulacro`).
require_once __DIR__ . '/../config/database.php';

class Prueba {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // CONSULTAR: todas las pruebas con el conteo de sesiones y preguntas
    public function listar() {
        try {
            $query = "SELECT p.*,
                        (SELECT COUNT(*) FROM simulacro s WHERE s.id_prueba = p.id_prueba) AS num_sesiones,
                        (SELECT COUNT(*) FROM preguntas q
                            INNER JOIN simulacro s2 ON s2.id_simulacro = q.id_simulacro
                            WHERE s2.id_prueba = p.id_prueba) AS num_preguntas
                      FROM pruebas p
                      ORDER BY p.fecha_creacion DESC, p.id_prueba DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // CONSULTAR: una prueba por id
    public function obtener($idPrueba) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM pruebas WHERE id_prueba = :id LIMIT 1");
            $stmt->bindValue(":id", (int)$idPrueba, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    // INSERTAR: crea la prueba y sus sesiones en una sola transacción.
    // $sesiones = [['titulo' => '...', 'duracion' => 270], ...]
    // Devuelve el id de la prueba o false.
    public function crearConSesiones($titulo, $tipo, $anio, $descripcion, array $sesiones) {
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare(
                "INSERT INTO pruebas (titulo, tipo, anio, descripcion, estado)
                 VALUES (:titulo, :tipo, :anio, :descripcion, 'Prueba')"
            );
            $stmt->execute([
                ":titulo" => $titulo, ":tipo" => $tipo,
                ":anio" => $anio, ":descripcion" => $descripcion
            ]);
            $idPrueba = (int)$this->conn->lastInsertId();

            $stmtSesion = $this->conn->prepare(
                "INSERT INTO simulacro (id_prueba, titulo, duracion_minutos)
                 VALUES (:idPrueba, :titulo, :duracion)"
            );
            foreach ($sesiones as $sesion) {
                $stmtSesion->execute([
                    ":idPrueba" => $idPrueba,
                    ":titulo" => $sesion['titulo'],
                    ":duracion" => $sesion['duracion']
                ]);
            }

            $this->conn->commit();
            return $idPrueba;
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return false;
        }
    }

    // ACTUALIZAR: datos generales de la prueba
    public function actualizar($idPrueba, $titulo, $tipo, $anio, $descripcion) {
        try {
            $stmt = $this->conn->prepare(
                "UPDATE pruebas SET titulo = :titulo, tipo = :tipo, anio = :anio, descripcion = :descripcion
                 WHERE id_prueba = :id"
            );
            return $stmt->execute([
                ":titulo" => $titulo, ":tipo" => $tipo, ":anio" => $anio,
                ":descripcion" => $descripcion, ":id" => (int)$idPrueba
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // ACTUALIZAR: publicar o volver a modo "Prueba"
    public function cambiarEstado($idPrueba, $estado) {
        if (!in_array($estado, ['Prueba', 'Publicado'], true)) {
            return false;
        }
        try {
            $stmt = $this->conn->prepare("UPDATE pruebas SET estado = :estado WHERE id_prueba = :id");
            return $stmt->execute([":estado" => $estado, ":id" => (int)$idPrueba]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // ELIMINAR: borra la prueba (sus sesiones, preguntas y opciones se borran en cascada).
    // Falla si algún estudiante ya presentó un simulacro de esta prueba.
    public function eliminar($idPrueba) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM pruebas WHERE id_prueba = :id");
            return $stmt->execute([":id" => (int)$idPrueba]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Revisa si la prueba se puede publicar. Devuelve una lista de problemas (vacía = todo bien).
    public function problemasParaPublicar($idPrueba) {
        $problemas = [];
        try {
            // 1) Sesiones y cuántas preguntas tiene cada una
            $stmt = $this->conn->prepare(
                "SELECT s.titulo, COUNT(q.id_pregunta) AS total
                 FROM simulacro s
                 LEFT JOIN preguntas q ON q.id_simulacro = s.id_simulacro
                 WHERE s.id_prueba = :id
                 GROUP BY s.id_simulacro, s.titulo"
            );
            $stmt->execute([":id" => (int)$idPrueba]);
            $sesiones = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($sesiones) === 0) {
                $problemas[] = "La prueba no tiene ninguna sesión.";
            }
            foreach ($sesiones as $sesion) {
                if ((int)$sesion['total'] === 0) {
                    $problemas[] = "La sesión \"" . $sesion['titulo'] . "\" no tiene preguntas.";
                }
            }

            // 2) Preguntas con menos de 2 opciones o sin exactamente 1 correcta
            $stmt = $this->conn->prepare(
                "SELECT s.titulo AS sesion, q.numero_pregunta,
                        COUNT(o.id_opcion) AS num_opciones,
                        COALESCE(SUM(o.es_correcta), 0) AS num_correctas
                 FROM preguntas q
                 INNER JOIN simulacro s ON s.id_simulacro = q.id_simulacro
                 LEFT JOIN opciones_rta o ON o.id_pregunta = q.id_pregunta
                 WHERE s.id_prueba = :id
                 GROUP BY q.id_pregunta, s.titulo, q.numero_pregunta
                 HAVING num_opciones < 2 OR num_correctas <> 1
                 ORDER BY s.id_simulacro, q.numero_pregunta"
            );
            $stmt->execute([":id" => (int)$idPrueba]);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
                $problemas[] = "Sesión \"" . $fila['sesion'] . "\", pregunta " . $fila['numero_pregunta']
                             . ": debe tener al menos 2 opciones y una sola respuesta correcta.";
            }
        } catch (PDOException $e) {
            $problemas[] = "No se pudo validar la prueba. Intenta de nuevo.";
        }
        return $problemas;
    }

    // Conteos para el panel del administrador
    public function resumenAdmin() {
        $vacio = ['pruebas' => 0, 'publicadas' => 0, 'sesiones' => 0, 'preguntas' => 0,
                  'estudiantes' => 0, 'reportes_pendientes' => 0];
        try {
            $query = "SELECT
                (SELECT COUNT(*) FROM pruebas) AS pruebas,
                (SELECT COUNT(*) FROM pruebas WHERE estado = 'Publicado') AS publicadas,
                (SELECT COUNT(*) FROM simulacro) AS sesiones,
                (SELECT COUNT(*) FROM preguntas) AS preguntas,
                (SELECT COUNT(*) FROM cuentas WHERE rol = 'Estudiante') AS estudiantes,
                (SELECT COUNT(*) FROM reportes WHERE estado = 'Pendiente') AS reportes_pendientes";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: $vacio;
        } catch (PDOException $e) {
            return $vacio;
        }
    }
}
