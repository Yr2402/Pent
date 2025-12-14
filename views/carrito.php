<?php
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../config/FtpConfig.php'; 
include __DIR__ . '/includes/header.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$productos_carrito = [];
$total = 0;
$ftpConfig = new FtpConfig();
$url_img_server = $ftpConfig->public_url;

if (!empty($_SESSION['carrito'])) {
    $ids = array_keys($_SESSION['carrito']);
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $db = (new Database())->connect();
    $stmt = $db->prepare("SELECT * FROM productos WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $productos_carrito = $stmt->fetchAll();
}
?>

<div class="cart-container" style="padding: 40px; max-width: 900px; margin: 0 auto;">
    <h2>🛒 Carrito</h2>
    
    <?php if (empty($productos_carrito)): ?>
        <p>Tu carrito está vacío.</p>
    <?php else: ?>
        <table style="width: 100%;">
            <thead>
                <tr><th>Producto</th><th>Precio</th><th>Cant</th><th>Subtotal</th></tr>
            </thead>
            <tbody>
                <?php foreach ($productos_carrito as $p): 
                    $cant = $_SESSION['carrito'][$p['id']];
                    $sub = $p['precio'] * $cant;
                    $total += $sub;
                    $img = !empty($p['imagen']) ? $url_img_server . $p['imagen'] : "https://via.placeholder.com/50";
                ?>
                <tr>
                    <td>
                        <img src="<?= $img ?>" style="width:50px; vertical-align:middle;">
                        <?= htmlspecialchars($p['nombre']) ?>
                    </td>
                    <td>$<?= number_format($p['precio'], 2) ?></td>
                    <td><?= $cant ?></td>
                    <td>$<?= number_format($sub, 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div style="text-align:right; margin-top:20px;">
            <h3>Total: $<?= number_format($total, 2) ?></h3>
            <a href="/views/buyer/checkout.php" class="btn-cta">Pagar</a>
        </div>
        
        <form action="/procesar_carrito.php" method="POST" style="margin-top:20px;">
            <input type="hidden" name="accion" value="vaciar">
            <button type="submit" class="logout-btn">Vaciar Todo</button>
        </form>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>