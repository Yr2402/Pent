<?php
require_once __DIR__ . '/../config/Database.php';

class RequestModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function crearSolicitud($cliente, $servicio, $detalle) {
        $sql = "INSERT INTO solicitudes (cliente, servicio, detalle, fecha) VALUES (:cliente, :servicio, :detalle, NOW())";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':cliente' => $cliente,
            ':servicio' => $servicio,
            ':detalle' => $detalle
        ]);
    }
}
?>