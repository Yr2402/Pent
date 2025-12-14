<?php
require_once __DIR__ . '/../core/Token.php';
include __DIR__ . '/includes/header.php';

$csrf_token = Token::generate();
?>

<div class="container-form" style="max-width: 600px;">
    <h2>📄 Solicitud de Servicios</h2>
    
    <?php if(isset($_SESSION['success'])): ?>
        <div class="msg-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['error'])): ?>
        <div class="msg-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form action="/procesar_solicitud.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

        <label for="servicio">Servicio a Contratar:</label>
        <select name="servicio" id="servicio" required>
            <option value="Cloud Hosting">Cloud Hosting</option>
            <option value="Ciberseguridad">Ciberseguridad</option>
            <option value="Soporte IT">Soporte IT</option>
            <option value="Redes">Redes</option>
        </select>

        <label for="cliente">Nombre de Empresa / Persona:</label>
        <input type="text" name="cliente" required placeholder="Ej: Tech Solutions">

        <label for="detalle">Detalle:</label>
        <textarea name="detalle" rows="5" required></textarea>

        <button type="submit">Enviar Solicitud</button>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>