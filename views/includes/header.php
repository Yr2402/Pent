<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$rol = $_SESSION['usuario_rol'] ?? 'invitado';

// Definir la URL de Catálogo dinámicamente:
$catalogo_nav_url = ($rol === 'admin' || $rol === 'operador') 
    ? '/views/admin/productos.php' 
    : '/views/productos.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soluciones Tecnológicas</title>
    <link rel="stylesheet" href="/public/css/styles.css?v=20240228"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav>
        <ul>
            <li><a href="/index.php">Inicio</a></li>
            <li><a href="<?= $catalogo_nav_url ?>">Catálogo</a></li> 
            <li><a href="/views/solicitud.php">Solicitar</a></li> 
            
            <?php if(isset($_SESSION['usuario_id'])): ?>
                
                <?php if($rol === 'admin' || $rol === 'operador'): ?>
                    
                    <?php if($rol === 'admin'): ?>
                        <li><a href="/views/admin/usuarios.php" style="color: #f87171;">👥 Usuarios</a></li>
                    <?php endif; ?>

                    <li><a href="/views/admin/archivos.php" style="color: #38bdf8;">📂 Archivos</a></li>
                    
                    <li><a href="https://mail.zoho.com/" target="_blank" style="color: #fbbf24;">📧 Correo</a></li>
                    
                    <li class="user-tag" style="border-color: #fbbf24; color: #fbbf24;">
                        <?= $rol === 'admin' ? '🛡️ Admin' : '🔧 Operador' ?>
                    </li>

                <?php else: ?>
                    <?php $cart_count = isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0; ?>
                    <li><a href="/views/carrito.php">🛒 Carrito (<?= $cart_count ?>)</a></li>
                    <li class="user-tag">👤 <?= htmlspecialchars($_SESSION['usuario_nombre']) ?></li>
                <?php endif; ?>

                <li><a href="/logout.php" class="logout-btn">Salir</a></li>

            <?php else: ?>
                <li><a href="/views/login.php" class="logout-btn">👤 Ingresar</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <main>