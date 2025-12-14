<?php
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../config/FtpConfig.php'; 
include __DIR__ . '/includes/header.php';

$productModel = new ProductModel();
$productos = $productModel->obtenerTodos();

$ftpConfig = new FtpConfig();
$url_img_server = $ftpConfig->public_url;

// --- Lógica de control de vista (Grid o List) ---
// Obtener la vista preferida de GET o establecer la predeterminada (grid)
$view_mode = $_GET['view'] ?? ($_SESSION['product_view'] ?? 'grid');

// Validar y almacenar la vista en sesión para persistencia
if ($view_mode === 'list' || $view_mode === 'grid') {
    $_SESSION['product_view'] = $view_mode;
} else {
    $view_mode = 'grid'; // Default fallback
}

$grid_class = $view_mode === 'grid' ? 'product-grid' : 'product-list';
$is_list_view = $view_mode === 'list';
// --------------------------------------------------
?>

<div style="text-align:center; padding: 40px; background: #f8fafc;">
    <h1>Catálogo de Hardware</h1>
    
    <div style="margin-top: 20px;">
        <a href="?view=grid" class="btn-secondary" style="border: 1px solid <?= $is_list_view ? '#ccc' : '#2563eb' ?>; background: <?= $is_list_view ? '#fff' : '#2563eb' ?>; color: <?= $is_list_view ? '#333' : '#fff' ?>; padding: 10px 20px; font-weight: bold;">
            <i class="fas fa-th-large"></i> Cuadros
        </a>
        <a href="?view=list" class="btn-secondary" style="border: 1px solid <?= !$is_list_view ? '#ccc' : '#2563eb' ?>; background: <?= !$is_list_view ? '#fff' : '#2563eb' ?>; color: <?= !$is_list_view ? '#333' : '#fff' ?>; margin-left: 10px; padding: 10px 20px; font-weight: bold;">
            <i class="fas fa-list"></i> Lista
        </a>
    </div>
</div>

<?php if(isset($_SESSION['success'])): ?>
    <div class="msg-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<?php if ($is_list_view): ?>
    <div class="product-list-header">
        <div></div> <div>PRODUCTO / DESCRIPCIÓN</div>
        <div style="text-align:center;">PRECIO</div>
        <div style="text-align:center;">STOCK</div>
        <div style="text-align:center;">ACCIÓN</div>
    </div>
<?php endif; ?>

<div class="<?= $grid_class ?>">
    <?php foreach ($productos as $row): ?>
        <div class="product-card">
            <?php $img = !empty($row['imagen']) ? $url_img_server . $row['imagen'] : "https://via.placeholder.com/300"; ?>
            
            <img src="<?= $img ?>" alt="<?= htmlspecialchars($row['nombre']) ?>">
            
            <div class="card-content">
                <h3><?= htmlspecialchars($row['nombre']) ?></h3>
                <p><?= htmlspecialchars($row['descripcion']) ?></p>
                
                <?php if (!$is_list_view): ?>
                    <div style="display:flex; justify-content:space-between; margin:15px 0;">
                        <span class="price">$<?= number_format($row['precio'], 2) ?></span>
                        <span class="stock"><?= $row['stock'] > 0 ? 'En Stock: ' . $row['stock'] : 'Agotado' ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] == 'cliente' && $row['stock'] > 0): ?>
                    <form action="/procesar_carrito.php" method="POST">
                        <input type="hidden" name="accion" value="agregar">
                        <input type="hidden" name="producto_id" value="<?= $row['id'] ?>">
                        <button type="submit" class="add-btn">🛒 Agregar</button>
                    </form>
                <?php endif; ?>
                
                <a href="/views/detalle.php?id=<?= $row['id'] ?>" class="btn-secondary" style="display:block; text-align:center; margin-top:10px;">Ver Detalles</a>
            </div>
            
            <?php if ($is_list_view): ?>
                <span class="price">$<?= number_format($row['precio'], 2) ?></span>
                <span class="stock" style="color: <?= $row['stock'] > 0 ? 'var(--success)' : 'var(--danger)' ?>;">
                    <?= $row['stock'] > 0 ? $row['stock'] . ' unid.' : 'Agotado' ?>
                </span>
                <div>
                    <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] == 'cliente' && $row['stock'] > 0): ?>
                        <form action="/procesar_carrito.php" method="POST" style="margin-bottom: 5px;">
                            <input type="hidden" name="accion" value="agregar">
                            <input type="hidden" name="producto_id" value="<?= $row['id'] ?>">
                            <button type="submit" class="add-btn" style="width: 100%;">🛒 Agregar</button>
                        </form>
                    <?php endif; ?>
                    <a href="/views/detalle.php?id=<?= $row['id'] ?>" class="btn-secondary" style="display:block; text-align:center; width: 100%;">Detalles</a>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>