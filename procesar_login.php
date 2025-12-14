<?php
require_once 'config/Database.php';
require_once 'core/Token.php';
// Asegúrate de incluir tu modelo de Bitácora si lo tienes en otra ruta
// require_once 'models/Bitacora.php'; 

session_start();

// Configurar zona horaria (Ajusta a 'America/Panama' si es necesario)
date_default_timezone_set('America/Panama');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Validar Token CSRF
    if (!Token::check($_POST['csrf_token'] ?? '')) {
        die("Error de seguridad: Token inválido. Recarga la página.");
    }

    $email = $_POST['email'];
    $password = $_POST['password'];

    // 2. Conectar BD
    $db = (new Database())->connect();

    // 3. Buscar Usuario + Datos de Seguridad (Intentos y Bloqueo)
    $stmt = $db->prepare("SELECT id, nombre, rol, contrasena_hash, intentos_fallidos, bloqueado_hasta FROM usuarios WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    // --- LÓGICA DE BLOQUEO (RATE LIMITING) ---
    if ($user) {
        // A. Verificar si está bloqueado actualmente
        if ($user['bloqueado_hasta']) {
            $hora_actual = new DateTime();
            $hora_bloqueo = new DateTime($user['bloqueado_hasta']);

            if ($hora_actual < $hora_bloqueo) {
                // Calcular segundos restantes
                $segundos = $hora_bloqueo->getTimestamp() - $hora_actual->getTimestamp();
                $_SESSION['error'] = "⛔ Cuenta bloqueada temporalmente por seguridad. Intenta de nuevo en " . $segundos . " segundos.";
                header("Location: /views/login.php");
                exit();
            }
        }
    }
    // -----------------------------------------

    // 4. Verificar Password
    if ($user && password_verify($password, $user['contrasena_hash'])) {
        
        // ✅ LOGIN CORRECTO
        
        // A. Reiniciar contadores de seguridad (Perdonar al usuario)
        $upd = $db->prepare("UPDATE usuarios SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id = :id");
        $upd->execute([':id' => $user['id']]);

        // B. Registrar en Bitácora (Opcional)
        // Bitacora::registrar($db, $user['id'], "LOGIN", "Inicio de sesión exitoso");

        // C. Crear Sesión
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario_nombre'] = $user['nombre'];
        $_SESSION['usuario_rol'] = $user['rol'];
        
        header("Location: /index.php");
        exit();

    } else {
        // ❌ LOGIN FALLIDO
        
        if ($user) {
            // A. Aumentar contador de fallos
            $intentos = $user['intentos_fallidos'] + 1;
            $bloqueo_sql = null;
            $msg_extra = "";

            // B. ¿Llegó al límite de 3? -> BLOQUEAR
            if ($intentos >= 3) {
                // Bloquear por 1 minuto desde AHORA
                $bloqueo_sql = date('Y-m-d H:i:s', strtotime("+1 minute"));
                $msg_extra = " Has sido bloqueado por 1 minuto.";
                
                // C. Registrar Bloqueo en Bitácora
                // Bitacora::registrar($db, $user['id'], "SEGURIDAD", "Usuario bloqueado por 3 intentos fallidos");
            }

            // D. Actualizar BD
            $upd = $db->prepare("UPDATE usuarios SET intentos_fallidos = :intentos, bloqueado_hasta = :bloqueo WHERE id = :id");
            $upd->execute([
                ':intentos' => $intentos,
                ':bloqueo' => $bloqueo_sql,
                ':id' => $user['id']
            ]);
        }

        $_SESSION['error'] = "Credenciales incorrectas." . (isset($msg_extra) ? $msg_extra : "");
        header("Location: /views/login.php");
        exit();
    }
}
header("Location: /views/login.php");
?>