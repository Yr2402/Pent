<?php
require_once __DIR__ . '/../core/Token.php';
include __DIR__ . '/includes/header.php'; 
$csrf_token = Token::generate();
?>

<div class="container-form" style="max-width: 400px;">
    <h2>🔐 Iniciar Sesión</h2>
    
    <?php if(isset($_SESSION['error'])): ?>
        <div class="msg-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if(isset($_SESSION['success'])): ?>
        <div class="msg-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <form method="POST" action="/procesar_login.php">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        
        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Contraseña:</label>
        <input type="password" name="password" required>

        <button type="submit">Ingresar</button>
    </form>
    
    <p style="text-align: center; margin-top: 10px;">
        <a href="/views/register.php">Registrarse</a>
    </p>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>