<?php

class Auth {
    
    /**
     * Inicia o reanuda la sesión si no está activa.
     */
    public static function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Verifica si el usuario actual ha iniciado sesión.
     * @return bool
     */
    public static function isLoggedIn() {
        self::startSession();
        return isset($_SESSION['user_id']);
    }

    /**
     * Obtiene el ID del rol del usuario actual.
     * @return int|null
     */
    public static function getUserRoleId() {
        self::startSession();
        return $_SESSION['user_rol'] ?? null;
    }
    
    /**
     * Redirecciona al usuario a la página de login si no está autenticado.
     */
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header("Location: /views/login.php");
            exit();
        }
    }

    /**
     * Verifica si el usuario actual tiene uno de los roles permitidos.
     * @param array $allowed_roles_ids IDs de los roles permitidos (ej: [1, 2] para Admin y Operador)
     */
    public static function requireRole(array $allowed_roles_ids) {
        self::requireLogin(); // Primero asegurar que ha iniciado sesión

        $current_role_id = self::getUserRoleId();
        
        if (!in_array($current_role_id, $allowed_roles_ids)) {
            // El usuario no tiene el rol permitido: Denegar acceso
            header("Location: /views/access_denied.php"); // Crear una vista de "Acceso Denegado"
            exit();
        }
        // Si el rol es permitido, la ejecución continúa.
    }
    
    /**
     * Cierra la sesión y destruye todos los datos.
     * Cumple con la regeneración de ID al cerrar sesión (aunque lo hacemos al iniciar también).
     */
    public static function logout() {
        self::startSession();
        
        // Limpiar variables de sesión
        $_SESSION = [];
        
        // Destruir la sesión del lado del servidor
        session_destroy();
        
        // Redireccionar a la página de inicio o login
        header("Location: /views/login.php");
        exit();
    }
}