<?php
// index.php
session_start();

// Determinar la URL del catálogo basada en el rol
$rol = $_SESSION['usuario_rol'] ?? 'invitado';
$catalogo_url = ($rol === 'admin' || $rol === 'operador') 
    ? '/views/admin/productos.php' 
    : '/views/productos.php';

// Incluimos header desde la nueva ruta
include __DIR__ . '/views/includes/header.php'; 
?>

<div class="hero">
    <h1>Soluciones Tecnológicas</h1>
    <p>Equipamiento, seguridad y soporte para empresas modernas.</p>
    <a href="<?= $catalogo_url ?>" class="btn-cta">Ver Catálogo</a> </div>

<section>
    <h2 class="section-title">Nuestros Servicios</h2>
    <div class="features-grid">
        <div class="feature-item"><span class="icon">☁️</span><h3>Cloud Hosting</h3></div>
        <div class="feature-item"><span class="icon">🛡️</span><h3>Ciberseguridad</h3></div>
        <div class="feature-item"><span class="icon">🔧</span><h3>Soporte IT</h3></div>
    </div>
</section>

<?php include __DIR__ . '/views/includes/footer.php'; ?>