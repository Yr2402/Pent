<?php
class Database {
    // Credenciales extraídas de tu archivo PENTEST/db.php
    private $host = '192.168.3.10'; 
    private $db_name = 'projects_db';
    private $username = 'adminapp';
    private $password = 'Ciber.123';
    private $conn;

    public function connect() {
        $this->conn = null;
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            // En producción, loguear error y mostrar mensaje genérico
            error_log("Error de conexión: " . $e->getMessage());
            die("Error de conexión al sistema.");
        }
        return $this->conn;
    }
}
?>