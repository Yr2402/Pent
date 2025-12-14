<?php
require_once 'core/Token.php'; // Si quieres validar CSRF en agregar al carrito, recomendado.
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $accion = $_POST['accion'];
    $id = $_POST['producto_id'];

    if ($accion === 'agregar') {
        if (!isset($_SESSION['carrito'])) $_SESSION['carrito'] = [];
        
        if (isset($_SESSION['carrito'][$id])) {
            $_SESSION['carrito'][$id]++;
        } else {
            $_SESSION['carrito'][$id] = 1;
        }
        $_SESSION['success'] = "Producto agregado.";
        header("Location: /views/productos.php"); // O volver a donde estaba
    }
    
    if ($accion === 'vaciar') {
        unset($_SESSION['carrito']);
        header("Location: /views/carrito.php");
    }
}
exit();
?>