<?php
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../config/FtpConfig.php'; 
include __DIR__ . '/includes/header.php';

if (!isset($_GET['id'])) { header("Location: /views/productos.php"); exit; }

$id = (int)$_GET['id'];
$db = (new Database())->connect();
$stmt = $db->prepare("SELECT * FROM productos WHERE id = :id");
$stmt->execute([':id' => $id]);
$producto = $stmt->fetch();

if (!$producto) { echo "Producto no encontrado."; exit; }

$ftpConfig = new FtpConfig();
$url_img_server = $ftpConfig->public_url;
$img = !empty($producto['imagen']) ? $url_img_server . $producto['imagen'] : "https://via.placeholder.com/500"; 
?>

<div class="container" style="max-width: 900px; margin: 40px auto; padding: 20px;">
    <a href="/views/productos.php">&larr; Volver</a>
    <div style="display: flex; gap: 40px; margin-top: 20px;">
        <div style="flex: 1;">
            <img src="<?= $img ?>" style="width: 100%; border-radius: 8px;">
        </div>
        <div style="flex: 1;">
            <h1><?= htmlspecialchars($producto['nombre']) ?></h1>
            <p><?= htmlspecialchars($producto['descripcion']) ?></p>
            <h2 style="color: #2563eb;">$<?= number_format($producto['precio'], 2) ?></h2>
            <p><strong>Stock:</strong> <?= $producto['stock'] ?></p>
            
            <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] == 'cliente' && $producto['stock'] > 0): ?>
                <form action="/procesar_carrito.php" method="POST">
                    <input type="hidden" name="accion" value="agregar">
                    <input type="hidden" name="producto_id" value="<?= $producto['id'] ?>">
                    <button type="submit" class="btn-cta" style="width:100%;">Agregar al Carrito</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>