<?php
session_start(); // 1. Iniciar sesión para leer roles

// Cargar configuraciones
require_once __DIR__ . '/../../config/FtpConfig.php';

// 2. SEGURIDAD ESTRICTA: Solo Admin puede entrar aquí
// Si no hay sesión o el rol no es admin, fuera.
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: /index.php?msg=AccesoDenegado");
    exit();
}

include __DIR__ . '/../includes/header.php';

$ftpConfig = new FtpConfig();
$conn = $ftpConfig->connect();
$msg = "";

// --- LÓGICA: PROCESAR ACCIONES (Borrar o Subir) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $conn) {
    
    // A. BORRAR ARCHIVO
    if (isset($_POST['accion']) && $_POST['accion'] === 'borrar') {
        $archivo_a_borrar = $_POST['nombre_archivo'];
        // Seguridad: Evitar que borren cosas fuera de la carpeta (../)
        $archivo_limpio = basename($archivo_a_borrar); 
        $ruta_completa = $ftpConfig->getRootPath() . $archivo_limpio;

        if (ftp_delete($conn, $ruta_completa)) {
            $msg = "<div class='msg-success'>🗑️ Archivo '$archivo_limpio' eliminado correctamente.</div>";
        } else {
            $msg = "<div class='msg-error'>❌ No se pudo eliminar. Revisa permisos.</div>";
        }
    }

    // B. SUBIR ARCHIVO SUELTO
    if (isset($_POST['accion']) && $_POST['accion'] === 'subir') {
        if (isset($_FILES['nuevo_archivo']) && $_FILES['nuevo_archivo']['error'] === 0) {
            $remoto = $ftpConfig->getRootPath() . basename($_FILES['nuevo_archivo']['name']);
            if (ftp_put($conn, $remoto, $_FILES['nuevo_archivo']['tmp_name'], FTP_BINARY)) {
                $msg = "<div class='msg-success'>✅ Archivo subido exitosamente.</div>";
            } else {
                $msg = "<div class='msg-error'>❌ Error al subir archivo.</div>";
            }
        }
    }
}

// --- LÓGICA: LISTAR ARCHIVOS ---
$archivos = [];
if ($conn) {
    $lista_cruda = ftp_nlist($conn, $ftpConfig->getRootPath());
    if (is_array($lista_cruda)) {
        foreach ($lista_cruda as $f) {
            $n = basename($f);
            if ($n != '.' && $n != '..' && $n != '.htaccess') {
                $archivos[] = $n;
            }
        }
    }
    // No cerramos conexión aquí para no romper el flujo si hubiera más lógica, 
    // pero al acabar el script PHP la cierra sola.
} else {
    $msg = "<div class='msg-error'>❌ Error de conexión al Servidor de Imágenes.</div>";
}
?>

<div class="container" style="padding: 40px; max-width: 1200px; margin: 0 auto;">
    
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h1>🎛️ Gestor de Servidor de Imágenes</h1>
        <div style="text-align:right;">
            </span>
        </div>
    </div>

    <?= $msg ?>

    <div style="background:white; padding:20px; border-radius:8px; margin-bottom:30px; border:1px dashed #cbd5e1;">
        <form method="POST" enctype="multipart/form-data" style="display:flex; gap:10px; align-items:center;">
            <input type="hidden" name="accion" value="subir">
            <strong>Subir nuevo:</strong>
            <input type="file" name="nuevo_archivo" required>
            <button type="submit" class="btn-cta" style="padding:5px 15px;">⬆ Subir</button>
        </form>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 20px;">
        <?php foreach ($archivos as $archivo): ?>
            <?php $url_img = $ftpConfig->public_url . $archivo; ?>
            
            <div style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; position:relative;">
                
                <a href="<?= $url_img ?>" target="_blank" style="display:block;">
                    <img src="<?= $url_img ?>" style="width: 100%; height: 150px; object-fit: cover; background: #f8fafc;">
                </a>

                <div style="padding: 10px; text-align: center;">
                    <div style="font-size: 12px; color: #64748b; margin-bottom:8px; word-break:break-all;">
                        <?= $archivo ?>
                    </div>
                    
                    <form method="POST" onsubmit="return confirm('¿Estás seguro de BORRAR este archivo permanentemente?');">
                        <input type="hidden" name="accion" value="borrar">
                        <input type="hidden" name="nombre_archivo" value="<?= $archivo ?>">
                        <button type="submit" style="background:#fee2e2; color:#ef4444; border:none; padding:5px 10px; border-radius:4px; cursor:pointer; font-size:12px; width:100%;">
                            🗑️ Eliminar
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if(empty($archivos)): ?>
            <p style="color:#64748b;">La carpeta está vacía.</p>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>