<?php
// app/models/Cuenta.php
require_once __DIR__ . '/../config/Database.php';

class Cuenta {
    private $conn;
    private $tableName = "cuentas";
 
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
 
    // INSERTAR: registrar una nueva cuenta
    public function registrar($nombreCompleto, $correo, $contrasenia, $rol = 'Estudiante') {
        try {
            $contraseniaHash = password_hash($contrasenia, PASSWORD_BCRYPT);
 
            $query = "INSERT INTO " . $this->tableName . " (nombre_completo, correo, contrasenia, rol) 
                      VALUES (:nombreCompleto, :correo, :contrasenia, :rol)";
 
            $stmt = $this->conn->prepare($query);
 
            $nombreCompleto = htmlspecialchars(strip_tags($nombreCompleto));
            $correo = htmlspecialchars(strip_tags($correo));
 
            $stmt->bindParam(":nombreCompleto", $nombreCompleto);
            $stmt->bindParam(":correo", $correo);
            $stmt->bindParam(":contrasenia", $contraseniaHash);
            $stmt->bindParam(":rol", $rol);
 
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
 
    // CONSULTAR: verificar credenciales de inicio de sesión
    public function login($correo, $contrasenia) {
        $query = "SELECT * FROM " . $this->tableName . " WHERE correo = :correo LIMIT 1";
        $stmt = $this->conn->prepare($query);
 
        $correo = htmlspecialchars(strip_tags($correo));
        $stmt->bindParam(":correo", $correo);
        $stmt->execute();
 
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
 
            if (password_verify($contrasenia, $row['contrasenia'])) {
                return $row;
            }
        }
        return false;
    }
 
    // CONSULTAR: listar todas las cuentas (sin exponer la contraseña)
    public function obtenerTodos() {
        try {
            $query = "SELECT id_cuenta, nombre_completo, correo, rol 
                      FROM " . $this->tableName . " ORDER BY nombre_completo ASC";
 
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
 
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
 
    // CONSULTAR: obtener una cuenta por su id (para el formulario de edición)
    public function obtenerPorId($idCuenta) {
        try {
            $query = "SELECT id_cuenta, nombre_completo, correo, rol 
                      FROM " . $this->tableName . " WHERE id_cuenta = :idCuenta LIMIT 1";
 
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":idCuenta", $idCuenta);
            $stmt->execute();
 
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }
 
    // ACTUALIZAR: modificar los datos de una cuenta
    public function actualizar($idCuenta, $nombreCompleto, $correo, $contrasenia = null) {
        try {
            $nombreCompleto = htmlspecialchars(strip_tags($nombreCompleto));
            $correo = htmlspecialchars(strip_tags($correo));
 
            if ($contrasenia) {
                $contraseniaHash = password_hash($contrasenia, PASSWORD_BCRYPT);
 
                $query = "UPDATE " . $this->tableName . " 
                          SET nombre_completo = :nombreCompleto, correo = :correo, contrasenia = :contrasenia 
                          WHERE id_cuenta = :idCuenta";
            } else {
                $query = "UPDATE " . $this->tableName . " 
                          SET nombre_completo = :nombreCompleto, correo = :correo 
                          WHERE id_cuenta = :idCuenta";
            }
 
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":nombreCompleto", $nombreCompleto);
            $stmt->bindParam(":correo", $correo);
            $stmt->bindParam(":idCuenta", $idCuenta);
 
            if ($contrasenia) {
                $stmt->bindParam(":contrasenia", $contraseniaHash);
            }
 
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
 
    // ELIMINAR: borrar una cuenta
    public function eliminar($idCuenta) {
        try {
            $query = "DELETE FROM " . $this->tableName . " WHERE id_cuenta = :idCuenta";
 
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":idCuenta", $idCuenta);
 
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
 