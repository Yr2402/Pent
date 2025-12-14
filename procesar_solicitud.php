<?php
require_once 'core/Token.php';
require_once 'models/RequestModel.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!Token::check($_POST['csrf_token'] ?? '')) {
        die("Error de seguridad: Token inválido.");
    }

    $cliente = trim($_POST['cliente']);
    $servicio = trim($_POST['servicio']);
    $detalle = trim($_POST['detalle']);

    if (empty($cliente) || empty($detalle)) {
        $_SESSION['error'] = "Todos los campos son obligatorios.";
        header("Location: /views/solicitud.php");
        exit();
    }

    $requestModel = new RequestModel();
    if ($requestModel->crearSolicitud($cliente, $servicio, $detalle)) {
        $_SESSION['success'] = "Solicitud enviada correctamente.";
    } else {
        $_SESSION['error'] = "Error al procesar la solicitud.";
    }

    header("Location: /views/solicitud.php");
    exit();
}
header("Location: /views/solicitud.php");