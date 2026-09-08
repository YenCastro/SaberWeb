<?php
class Database {
    private $host = "localhost";
    private $db_name = "saberweb";
    private $username = "root";
    private $password = "";
    private $conn;

    // Obtener la conexión a la BD
    public function getConnection() {
        $this->conn = null;

        try {
            // Instancia de PDO para MySQL
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            // Configurar el modo de error de PDO a excepción
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>
