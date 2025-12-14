<?php
require_once 'config/Database.php';
require_once 'core/Token.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!Token::check($_POST['csrf_token'] ?? '')) {
        die("Token inválido.");
    }

    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $db = (new Database())->connect();

    // Verificar email duplicado
    $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = :email");
    $stmt->execute([':email' => $email]);
    
    if ($stmt->rowCount() > 0) {
        $_SESSION['error'] = "El email ya está registrado.";
        header("Location: /views/register.php");
        exit();
    }

    // Crear usuario
    $pass_hash = password_hash($password, PASSWORD_DEFAULT);
    $rol = 'cliente'; // Default

    $sql = "INSERT INTO usuarios (nombre, email, contrasena_hash, rol) VALUES (:nombre, :email, :pass, :rol)";
    $stmt = $db->prepare($sql);
    
    if ($stmt->execute([':nombre'=>$nombre, ':email'=>$email, ':pass'=>$pass_hash, ':rol'=>$rol])) {
        $_SESSION['success'] = "Cuenta creada. Inicia sesión.";
        header("Location: /views/login.php");
    } else {
        $_SESSION['error'] = "Error al registrar.";
        header("Location: /views/register.php");
    }
    exit();
}