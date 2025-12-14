<?php
require_once __DIR__ . '/../core/Token.php';
include __DIR__ . '/includes/header.php'; 
$csrf_token = Token::generate();
?>

<div class="container-form">
    <h2>📝 Crear Cuenta</h2>
    
    <?php if(isset($_SESSION['error'])): ?>
        <div class="msg-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form method="POST" action="/procesar_registro.php">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

        <label>Nombre:</label>
        <input type="text" name="nombre" required>
        
        <label>Email:</label>
        <input type="email" name="email" required>
        
        <label>Contraseña:</label>
        <input type="password" name="password" required>
        
        <button type="submit">Registrarse</button>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>