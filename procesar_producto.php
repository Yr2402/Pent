<?php
require_once 'config/Database.php';
require_once 'config/FtpConfig.php'; // <--- Cargamos la config FTP
require_once 'core/Auth.php';
require_once 'core/Token.php';
require_once 'models/ProductModel.php';

session_start();

// Validar Permisos (Admin y Operador)
$rol = $_SESSION['usuario_rol'] ?? '';
if ($rol !== 'admin' && $rol !== 'operador') {
    header("Location: /index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!Token::check($_POST['csrf_token'] ?? '')) {
        die("Error de seguridad: Token inválido.");
    }

    $productModel = new ProductModel();
    $accion = $_POST['accion'] ?? '';

    // --- ACCIÓN: CREAR ---
    if ($accion === 'crear') {
        $nombre = $_POST['nombre'];
        $desc = $_POST['descripcion'];
        $precio = $_POST['precio'];
        $stock = $_POST['stock'];
        $cat = $_POST['categoria'];
        
        $nombre_imagen_bd = 'default.jpg';
        
        // Procesar subida de imagen (Lógica existente)
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            $permitidos = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            
            if (in_array($ext, $permitidos)) {
                $nombre_unico = time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                $archivo_local = $_FILES['imagen']['tmp_name'];

                $ftp = new FtpConfig();
                $conn_id = $ftp->connect();

                if ($conn_id) {
                    $ruta_destino = $ftp->getRootPath() . $nombre_unico;
                    
                    if (ftp_put($conn_id, $ruta_destino, $archivo_local, FTP_BINARY)) {
                        $nombre_imagen_bd = $nombre_unico; 
                    } else {
                        $_SESSION['error'] = "Conectado, pero falló la subida (Revisar permisos en server destino).";
                    }
                    ftp_close($conn_id);
                } else {
                    $_SESSION['error'] = "No se pudo conectar al Servidor de Imágenes (Revise FtpConfig).";
                }
            } else {
                $_SESSION['error'] = "Formato de imagen inválido.";
            }
        }

        if ($productModel->crear($nombre, $desc, $precio, $stock, $nombre_imagen_bd, $cat)) {
            $_SESSION['success'] = "Producto creado y subido correctamente.";
        } else {
            $_SESSION['error'] = !empty($_SESSION['error']) ? $_SESSION['error'] : "Error al guardar en BD.";
        }
    }
    
    // --- ACCIÓN: ACTUALIZAR (NUEVO - Admin y Operador) ---
    elseif ($accion === 'actualizar') {
        $id = $_POST['id'] ?? null;
        
        if ($rol === 'admin' || $rol === 'operador') {
            
            $nombre = $_POST['nombre'];
            $desc = $_POST['descripcion'];
            $precio = $_POST['precio'];
            $stock = $_POST['stock'];
            $cat = $_POST['categoria'];
            
            // Si no se sube una nueva imagen, mantenemos la que ya estaba (enviada por campo oculto)
            $nombre_imagen_bd = $_POST['imagen_actual'] ?? null; 

            // Procesar subida de imagen SOLO si se proporciona una nueva
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
                $permitidos = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                
                if (in_array($ext, $permitidos)) {
                    $nombre_unico = time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                    $archivo_local = $_FILES['imagen']['tmp_name'];

                    $ftp = new FtpConfig();
                    $conn_id = $ftp->connect();

                    if ($conn_id) {
                        $ruta_destino = $ftp->getRootPath() . $nombre_unico;
                        
                        if (ftp_put($conn_id, $ruta_destino, $archivo_local, FTP_BINARY)) {
                            $nombre_imagen_bd = $nombre_unico; // Éxito: Usar la nueva imagen
                        } else {
                            $_SESSION['error'] = "Conectado, pero falló la subida (Revisar permisos en server destino).";
                        }
                        ftp_close($conn_id);
                    } else {
                        $_SESSION['error'] = "No se pudo conectar al Servidor de Imágenes (Revise FtpConfig).";
                    }
                } else {
                    $_SESSION['error'] = "Formato de imagen inválido.";
                }
            }
            
            // NOTA: Si $nombre_imagen_bd es NULL o vacío, la función ProductModel::actualizar lo ignora.
            if (empty($_SESSION['error']) && $productModel->actualizar($id, $nombre, $desc, $precio, $stock, $nombre_imagen_bd, $cat)) {
                $_SESSION['success'] = "Producto ID {$id} actualizado correctamente.";
            } else {
                $_SESSION['error'] = !empty($_SESSION['error']) ? $_SESSION['error'] : "Error al actualizar en BD.";
            }
        } else {
            $_SESSION['error'] = "Permisos insuficientes para actualizar productos.";
        }
    }


    // --- ACCIÓN: ELIMINAR (SOLO ADMIN) ---
    elseif ($accion === 'eliminar') {
        if ($rol === 'admin') { // <-- RESTRICCIÓN SOLICITADA
            $id = $_POST['id'];
            if ($productModel->eliminar($id)) {
                $_SESSION['success'] = "Producto eliminado.";
            } else {
                $_SESSION['error'] = "Error al eliminar.";
            }
        } else {
            $_SESSION['error'] = "Solo Admin puede eliminar."; // <-- Mensaje corregido
        }
    }

    header("Location: /views/admin/productos.php");
    exit();
}
header("Location: /index.php");
?>