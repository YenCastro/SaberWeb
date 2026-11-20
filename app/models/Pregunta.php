<?php
// app/models/Pregunta.php
require_once __DIR__ . '/../config/database.php';

class Pregunta {
    private $conn;
    const LETRAS = ['A', 'B', 'C', 'D', 'E'];

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // CONSULTAR: preguntas de una sesión, en orden
    public function listarPorSimulacro($idSimulacro) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT * FROM preguntas WHERE id_simulacro = :id ORDER BY numero_pregunta ASC"
            );
            $stmt->bindValue(":id", (int)$idSimulacro, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // CONSULTAR: una pregunta por id
    public function obtener($idPregunta) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM preguntas WHERE id_pregunta = :id LIMIT 1");
            $stmt->bindValue(":id", (int)$idPregunta, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    // INSERTAR o ACTUALIZAR una pregunta junto con sus opciones (A a E), en una transacción.
    // $opciones = ['A' => 'texto', 'B' => 'texto', 'C' => '', ...]  (vacío = esa opción no se usa)
    // Si $idPregunta es null se crea (con el siguiente número); si no, se actualiza.
    // Devuelve el id de la pregunta o false.
    public function guardarCompleta($idSimulacro, $idPregunta, $enunciado, $area, array $opciones, $letraCorrecta) {
        try {
            $this->conn->beginTransaction();

            if ($idPregunta) {
                $stmt = $this->conn->prepare(
                    "UPDATE preguntas SET enunciado = :enunciado, area = :area
                     WHERE id_pregunta = :id AND id_simulacro = :sim"
                );
                $stmt->execute([":enunciado" => $enunciado, ":area" => $area,
                                ":id" => (int)$idPregunta, ":sim" => (int)$idSimulacro]);
            } else {
                $stmt = $this->conn->prepare(
                    "SELECT COALESCE(MAX(numero_pregunta), 0) + 1 FROM preguntas WHERE id_simulacro = :sim"
                );
                $stmt->execute([":sim" => (int)$idSimulacro]);
                $numero = (int)$stmt->fetchColumn();

                $stmt = $this->conn->prepare(
                    "INSERT INTO preguntas (id_simulacro, enunciado, area, numero_pregunta)
                     VALUES (:sim, :enunciado, :area, :numero)"
                );
                $stmt->execute([":sim" => (int)$idSimulacro, ":enunciado" => $enunciado,
                                ":area" => $area, ":numero" => $numero]);
                $idPregunta = (int)$this->conn->lastInsertId();
            }

            foreach (self::LETRAS as $letra) {
                $texto = trim($opciones[$letra] ?? '');

                $stmt = $this->conn->prepare(
                    "SELECT id_opcion FROM opciones_rta WHERE id_pregunta = :p AND letra = :l LIMIT 1"
                );
                $stmt->execute([":p" => $idPregunta, ":l" => $letra]);
                $idOpcion = $stmt->fetchColumn();

                if ($texto === '') {
                    if ($idOpcion) {
                        $this->conn->prepare("DELETE FROM opciones_rta WHERE id_opcion = :o")
                                   ->execute([":o" => $idOpcion]);
                    }
                    continue;
                }

                $esCorrecta = ($letra === $letraCorrecta) ? 1 : 0;
                if ($idOpcion) {
                    $this->conn->prepare("UPDATE opciones_rta SET texto = :t, es_correcta = :c WHERE id_opcion = :o")
                               ->execute([":t" => $texto, ":c" => $esCorrecta, ":o" => $idOpcion]);
                } else {
                    $this->conn->prepare(
                        "INSERT INTO opciones_rta (id_pregunta, letra, texto, es_correcta) VALUES (:p, :l, :t, :c)"
                    )->execute([":p" => $idPregunta, ":l" => $letra, ":t" => $texto, ":c" => $esCorrecta]);
                }
            }

            $this->conn->commit();
            return $idPregunta;
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return false;
        }
    }

    // ELIMINAR: borra la pregunta (sus opciones se borran en cascada) y renumera las demás.
    // Falla si algún estudiante ya respondió esta pregunta.
    public function eliminar($idPregunta) {
        $pregunta = $this->obtener($idPregunta);
        if (!$pregunta) {
            return false;
        }
        try {
            $stmt = $this->conn->prepare("DELETE FROM preguntas WHERE id_pregunta = :id");
            $stmt->execute([":id" => (int)$idPregunta]);
            $this->renumerar($pregunta['id_simulacro']);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Deja los números de pregunta seguidos (1, 2, 3...) después de borrar una
    private function renumerar($idSimulacro) {
        $stmt = $this->conn->prepare(
            "SELECT id_pregunta FROM preguntas WHERE id_simulacro = :sim ORDER BY numero_pregunta ASC, id_pregunta ASC"
        );
        $stmt->execute([":sim" => (int)$idSimulacro]);
        $upd = $this->conn->prepare("UPDATE preguntas SET numero_pregunta = :n WHERE id_pregunta = :id");
        $n = 1;
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $id) {
            $upd->execute([":n" => $n++, ":id" => $id]);
        }
    }
}
