<?php
require_once __DIR__ . '/../config/Database.php';

class ProductModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function obtenerTodos() {
        $sql = "SELECT * FROM productos ORDER BY id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // NUEVO: Obtener un producto por su ID para el formulario de edición
    public function obtenerPorId($id) {
        $sql = "SELECT * FROM productos WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function crear($nombre, $descripcion, $precio, $stock, $imagen, $categoria) {
        $sql = "INSERT INTO productos (nombre, descripcion, precio, stock, imagen, categoria) 
                VALUES (:nombre, :desc, :precio, :stock, :img, :cat)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':nombre' => $nombre,
            ':desc' => $descripcion,
            ':precio' => $precio,
            ':stock' => $stock,
            ':img' => $imagen,
            ':cat' => $categoria
        ]);
    }
    
    // NUEVO: Actualizar un producto (CRUD Update)
    public function actualizar($id, $nombre, $descripcion, $precio, $stock, $imagen, $categoria) {
        $sql = "UPDATE productos SET nombre = :nombre, descripcion = :desc, precio = :precio, stock = :stock, categoria = :cat";
        
        $params = [
            ':nombre' => $nombre,
            ':desc' => $descripcion,
            ':precio' => $precio,
            ':stock' => $stock,
            ':cat' => $categoria,
            ':id' => $id
        ];

        // Solo actualiza la imagen si se proporciona un nuevo nombre que no sea 'default.jpg' (para evitar borrar la imagen)
        if (!empty($imagen) && $imagen !== 'default.jpg') {
            $sql .= ", imagen = :img";
            $params[':img'] = $imagen;
        }
        
        $sql .= " WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    public function eliminar($id) {
        $sql = "DELETE FROM productos WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
?>