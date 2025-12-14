<?php
require_once __DIR__ . '/../../core/Token.php';
require_once __DIR__ . '/../../models/ProductModel.php';
require_once __DIR__ . '/../../config/FtpConfig.php'; // Para obtener la URL base
include __DIR__ . '/../includes/header.php'; 

if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'admin' && $_SESSION['usuario_rol'] !== 'operador')) {
    echo "<div class='msg-error' style='text-align:center; margin:50px;'>⛔ Acceso Denegado</div>";
    include __DIR__ . '/../includes/footer.php';
    exit();
}

$productModel = new ProductModel();

// --- Lógica de Edición: Cargar producto si se recibe edit_id ---
$producto_a_editar = null;
if (isset($_GET['edit_id']) && is_numeric($_GET['edit_id'])) {
    $edit_id = (int)$_GET['edit_id'];
    $producto_a_editar = $productModel->obtenerPorId($edit_id); 
    // Si el producto no existe o fue eliminado, forzamos un formulario de creación
    if (!$producto_a_editar) {
        $producto_a_editar = null;
        header("Location: productos.php?error=Producto no encontrado para editar.");
        exit();
    }
}
$es_edicion = $producto_a_editar !== null;
// ---------------------------------------------------------------

$productos = $productModel->obtenerTodos();
$csrf_token = Token::generate();

// Obtenemos la URL base del servidor de imágenes
$ftpConfig = new FtpConfig();
$url_img_server = $ftpConfig->public_url;
?>

<div class="admin-container" style="padding: 40px;">
    <h1>⚙️ Gestión de Inventario</h1>
    
    <?php if(isset($_SESSION['success'])): ?>
        <div class="msg-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if(isset($_SESSION['error'])): ?>
        <div class="msg-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 40px;">
        <h3><?= $es_edicion ? '✏️ Editar Producto ID: ' . htmlspecialchars($producto_a_editar['id']) : '➕ Agregar Producto' ?></h3>
        
        <form action="/procesar_producto.php" method="POST" enctype="multipart/form-data" style="display: grid; gap: 15px; grid-template-columns: 1fr 1fr;">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="accion" value="<?= $es_edicion ? 'actualizar' : 'crear' ?>">
            
            <?php if ($es_edicion): ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($producto_a_editar['id']) ?>">
                <input type="hidden" name="imagen_actual" value="<?= htmlspecialchars($producto_a_editar['imagen'] ?? '') ?>">
            <?php endif; ?>

            <input type="text" name="nombre" placeholder="Nombre" value="<?= htmlspecialchars($producto_a_editar['nombre'] ?? '') ?>" required style="padding:10px; border:1px solid #ddd; border-radius:4px;">
            <input type="text" name="categoria" placeholder="Categoría" value="<?= htmlspecialchars($producto_a_editar['categoria'] ?? '') ?>" required style="padding:10px; border:1px solid #ddd; border-radius:4px;">
            <input type="number" name="precio" placeholder="Precio" step="0.01" value="<?= htmlspecialchars($producto_a_editar['precio'] ?? '') ?>" required style="padding:10px; border:1px solid #ddd; border-radius:4px;">
            <input type="number" name="stock" placeholder="Stock" value="<?= htmlspecialchars($producto_a_editar['stock'] ?? '') ?>" required style="padding:10px; border:1px solid #ddd; border-radius:4px;">
            
            <div style="grid-column: 1 / -1; background: #f8fafc; padding: 10px; border-radius: 4px;">
                <label style="display:block; font-weight:bold;">Imagen: <?= $es_edicion ? '(Opcional, dejar vacío para no cambiar)' : '' ?></label>
                <input type="file" name="imagen" accept="image/*" <?= !$es_edicion ? 'required' : '' ?>>
                <?php if ($es_edicion && !empty($producto_a_editar['imagen'])): ?>
                    <small style="color:green; display: block;">Imagen actual: <?= htmlspecialchars($producto_a_editar['imagen']) ?></small>
                <?php endif; ?>
                <small style="color:#666;">Se subirá al servidor: <?= $url_img_server ?></small>
            </div>

            <textarea name="descripcion" placeholder="Descripción..." rows="3" style="grid-column: 1 / -1; padding:10px; border:1px solid #ddd; border-radius:4px;"><?= htmlspecialchars($producto_a_editar['descripcion'] ?? '') ?></textarea>

            <button type="submit" class="btn-cta" style="grid-column: 1 / -1; cursor:pointer;"><?= $es_edicion ? 'Guardar Cambios' : 'Guardar' ?></button>
            
            <?php if ($es_edicion): ?>
                <a href="productos.php" style="grid-column: 1 / -1; text-align: center; color: #007bff; margin-top: -10px;">Cancelar Edición</a>
            <?php endif; ?>
        </form>
    </div>

    <table style="width: 100%; border-collapse: collapse; background: white;">
        <thead>
            <tr style="background: #0f172a; color: white;">
                <th style="padding: 10px;">Foto</th>
                <th style="padding: 10px;">Producto</th>
                <th style="padding: 10px;">Precio</th>
                <th style="padding: 10px;">Stock</th>
                <th style="padding: 10px;">Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productos as $p): ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 10px;">
                    <?php $img = !empty($p['imagen']) ? $url_img_server . $p['imagen'] : "https://via.placeholder.com/50"; ?>
                    <img src="<?= $img ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                </td>
                <td style="padding: 10px;">
                    <strong><?= htmlspecialchars($p['nombre']) ?></strong><br>
                    <small><?= htmlspecialchars($p['categoria']) ?></small>
                </td>
                <td style="padding: 10px;">$<?= number_format($p['precio'], 2) ?></td>
                <td style="padding: 10px;"><?= $p['stock'] ?></td>
                <td style="padding: 10px; white-space: nowrap;">
                    <?php if($_SESSION['usuario_rol'] === 'admin' || $_SESSION['usuario_rol'] === 'operador'): ?>
                        <a href="productos.php?edit_id=<?= $p['id'] ?>" style="color:#0284c7; margin-right: 15px; text-decoration: none; font-weight: bold;">✏️ Editar</a>
                    <?php endif; ?>
                    
                    <?php if($_SESSION['usuario_rol'] === 'admin'): ?>
                    <form action="/procesar_producto.php" method="POST" onsubmit="return confirm('¿Estás seguro de ELIMINAR el producto: <?= htmlspecialchars($p['nombre']) ?>?');" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <button type="submit" style="color:red; border:none; background:none; cursor:pointer; font-weight: bold;">🗑️ Eliminar</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>