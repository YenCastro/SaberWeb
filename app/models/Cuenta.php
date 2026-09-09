<?php
// app/models/Cuenta.php
require_once __DIR__ . '/../config/Database.php';

class Cuenta {
    private $conn;
    private $table_name = "cuentas"; 

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Método para registrar una nueva cuenta
    public function registrar($nombre_completo, $correo, $contrasenia, $rol = 'Estudiante') {
        try {
            $contrasenia_hash = password_hash($contrasenia, PASSWORD_BCRYPT);

            $query = "INSERT INTO " . $this->table_name . " (nombre_completo, correo, contrasenia, rol) 
                      VALUES (:nombre_completo, :correo, :contrasenia, :rol)";
            
            $stmt = $this->conn->prepare($query);

            $nombre_completo = htmlspecialchars(strip_tags($nombre_completo));
            $correo = htmlspecialchars(strip_tags($correo));

            $stmt->bindParam(":nombre_completo", $nombre_completo);
            $stmt->bindParam(":correo", $correo);
            $stmt->bindParam(":contrasenia", $contrasenia_hash);
            $stmt->bindParam(":rol", $rol);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    // Método para verificar inicio de sesión
    public function login($correo, $contrasenia) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE correo = :correo LIMIT 1";
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
}
?>