<?php
session_start();

// 1. SEGURIDAD STRICTA (Solo Admin)
// Si no hay rol o NO es admin, lo sacamos de aquí inmediatamente.
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    // Lo redirigimos a la vista de productos (donde sí tiene permiso)
    header("Location: /views/admin/productos.php");
    exit();
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../core/Token.php'; // Para seguridad CSRF

$db = (new Database())->connect();
$mensaje = "";
$csrf_token = Token::generate();

// --- Lógica de Edición: Obtener datos del usuario si se pasa 'edit_id' por GET ---
$usuario_a_editar = null;
if (isset($_GET['edit_id']) && is_numeric($_GET['edit_id'])) {
    $edit_id = (int)$_GET['edit_id'];
    
    // Seguridad: No permitir editar la cuenta propia
    if ($edit_id == ($_SESSION['usuario_id'] ?? 0)) {
        $mensaje = "<div class='msg-error'>❌ No puedes editar tu propia cuenta desde aquí.</div>";
    } else {
        // Seleccionar todos los campos relevantes, incluyendo teléfono y dirección
        $stmt = $db->prepare("SELECT id, nombre, email, rol, telefono, direccion FROM usuarios WHERE id = ?");
        $stmt->execute([$edit_id]);
        $usuario_a_editar = $stmt->fetch();
        
        if (!$usuario_a_editar) {
            $mensaje = "<div class='msg-error'>❌ Usuario no encontrado.</div>";
        }
    }
}
$es_edicion = $usuario_a_editar !== null;


// 2. PROCESAR ACCIONES (Crear, Actualizar o Eliminar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Validar Token de seguridad
    if (!Token::check($_POST['csrf_token'] ?? '')) {
        $mensaje = "<div class='msg-error'>❌ Error de seguridad (Token inválido).</div>";
    } else {
        
        $accion = $_POST['accion'] ?? '';
        
        // A. CREAR USUARIO
        if ($accion === 'crear') {
            $nombre = trim($_POST['nombre']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $rol = $_POST['rol'];

            // Verificar si el email ya existe
            $check = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
            $check->execute([$email]);
            
            if ($check->rowCount() > 0) {
                $mensaje = "<div class='msg-error'>❌ Ese correo ya está registrado.</div>";
            } else {
                // Encriptar contraseña
                $pass_hash = password_hash($password, PASSWORD_DEFAULT);
                
                $sql = "INSERT INTO usuarios (nombre, email, contrasena_hash, rol) VALUES (:n, :e, :p, :r)";
                $stmt = $db->prepare($sql);
                
                if ($stmt->execute([':n' => $nombre, ':e' => $email, ':p' => $pass_hash, ':r' => $rol])) {
                    $mensaje = "<div class='msg-success'>✅ Usuario creado correctamente.</div>";
                } else {
                    $mensaje = "<div class='msg-error'>❌ Error al crear usuario.</div>";
                }
            }
        }
        
        // B. ACTUALIZAR USUARIO
        elseif ($accion === 'actualizar') {
            $id_actualizar = $_POST['id'];
            $nombre = trim($_POST['nombre']);
            $email = trim($_POST['email']);
            $password = $_POST['password'] ?? '';
            $rol = $_POST['rol'];
            $telefono = trim($_POST['telefono']) ?? null;
            $direccion = trim($_POST['direccion']) ?? null;

            // Seguridad: No permitir actualizar la cuenta propia desde aquí
            if ($id_actualizar == ($_SESSION['usuario_id'] ?? 0)) {
                 $mensaje = "<div class='msg-error'>❌ No puedes actualizar tu propia cuenta desde este formulario.</div>";
            } else {
                // 1. Verificar si el email ya existe en OTRO usuario
                $check = $db->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
                $check->execute([$email, $id_actualizar]);
                
                if ($check->rowCount() > 0) {
                    $mensaje = "<div class='msg-error'>❌ Ese correo ya está registrado por otro usuario.</div>";
                } else {
                    
                    $params = [
                        ':n' => $nombre, 
                        ':e' => $email, 
                        ':r' => $rol,
                        ':t' => $telefono,
                        ':d' => $direccion,
                        ':id' => $id_actualizar
                    ];
                    
                    // 2. Definir SQL y parámetros para actualizar
                    $sql_update = "UPDATE usuarios SET nombre = :n, email = :e, rol = :r, telefono = :t, direccion = :d";
                    
                    // Si se proporcionó una nueva contraseña, la encriptamos y la incluimos en el SQL
                    if (!empty($password)) {
                        $pass_hash = password_hash($password, PASSWORD_DEFAULT);
                        $sql_update .= ", contrasena_hash = :p";
                        $params[':p'] = $pass_hash;
                    }
                    
                    $sql_update .= " WHERE id = :id";
                    
                    $stmt = $db->prepare($sql_update);
                    
                    if ($stmt->execute($params)) {
                        $mensaje = "<div class='msg-success'>✅ Usuario ID {$id_actualizar} actualizado correctamente.</div>";
                    } else {
                        $mensaje = "<div class='msg-error'>❌ Error al actualizar usuario.</div>";
                    }
                }
            }
        }
        
        // C. ELIMINAR USUARIO
        elseif ($accion === 'eliminar') {
            $id_borrar = $_POST['id'];

            // Seguridad: No permitir borrarse a sí mismo
            if ($id_borrar == $_SESSION['usuario_id']) {
                $mensaje = "<div class='msg-error'>❌ No puedes eliminar tu propia cuenta.</div>";
            } else {
                $stmt = $db->prepare("DELETE FROM usuarios WHERE id = ?");
                $stmt->execute([$id_borrar]);
                $mensaje = "<div class='msg-success'>🗑️ Usuario eliminado.</div>";
            }
        }
    }
    
    // Redireccionamos para limpiar el POST y el GET si no hubo un error grave
    if (!strpos($mensaje, 'msg-error')) {
        header("Location: usuarios.php");
        exit();
    }
}

// 3. OBTENER LISTA DE USUARIOS
$usuarios = $db->query("SELECT * FROM usuarios ORDER BY id DESC")->fetchAll();

include __DIR__ . '/../includes/header.php'; 
?>

<div class="admin-container" style="padding: 40px; max-width: 1000px; margin: 0 auto;">
    <h1>👥 Gestión de Usuarios</h1>
    <p style="color: gray;">Zona exclusiva para Administradores</p>

    <?= $mensaje ?>

    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 40px; border-left: 5px solid #38bdf8;">
        <h3><?= $es_edicion ? '✏️ Editar Usuario ID: ' . htmlspecialchars($usuario_a_editar['id']) : '➕ Registrar Nuevo Usuario' ?></h3>
        
        <form method="POST" style="display: grid; gap: 15px; grid-template-columns: 1fr 1fr;">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="accion" value="<?= $es_edicion ? 'actualizar' : 'crear' ?>">
            
            <?php if ($es_edicion): ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($usuario_a_editar['id']) ?>">
            <?php endif; ?>

            <input type="text" name="nombre" placeholder="Nombre completo" value="<?= htmlspecialchars($usuario_a_editar['nombre'] ?? '') ?>" required style="padding:10px; border:1px solid #ddd; border-radius:4px;">
            <input type="email" name="email" placeholder="Correo Electrónico" value="<?= htmlspecialchars($usuario_a_editar['email'] ?? '') ?>" required style="padding:10px; border:1px solid #ddd; border-radius:4px;">
            
            <input type="password" name="password" placeholder="Contraseña <?= $es_edicion ? '(dejar vacío para no cambiar)' : '' ?>" <?= !$es_edicion ? 'required' : '' ?> style="padding:10px; border:1px solid #ddd; border-radius:4px;">
            
            <input type="text" name="telefono" placeholder="Teléfono" value="<?= htmlspecialchars($usuario_a_editar['telefono'] ?? '') ?>" style="padding:10px; border:1px solid #ddd; border-radius:4px;">
            
            <textarea name="direccion" placeholder="Dirección" rows="2" style="grid-column: 1 / -1; padding:10px; border:1px solid #ddd; border-radius:4px;"><?= htmlspecialchars($usuario_a_editar['direccion'] ?? '') ?></textarea>
            
            <select name="rol" style="padding:10px; border:1px solid #ddd; border-radius:4px;">
                <?php $current_rol = $usuario_a_editar['rol'] ?? 'cliente'; // Por defecto es cliente si se está creando ?>
                <option value="cliente" <?= $current_rol === 'cliente' ? 'selected' : '' ?>>Cliente</option>
                <option value="operador" <?= $current_rol === 'operador' ? 'selected' : '' ?>>Operador (Solo Productos)</option>
                <option value="admin" <?= $current_rol === 'admin' ? 'selected' : '' ?>>Administrador (Total)</option>
            </select>

            <button type="submit" class="btn-cta" style="grid-column: 1 / -1;"><?= $es_edicion ? 'Guardar Cambios' : 'Crear Usuario' ?></button>
            
            <?php if ($es_edicion): ?>
                <a href="usuarios.php" style="grid-column: 1 / -1; text-align: center; color: #007bff; margin-top: -10px;">Cancelar Edición</a>
            <?php endif; ?>
        </form>
    </div>

    <table style="width: 100%; border-collapse: collapse; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <thead>
            <tr style="background: #0f172a; color: white; text-align: left;">
                <th style="padding: 12px;">Nombre</th>
                <th style="padding: 12px;">Email</th>
                <th style="padding: 12px;">Rol</th>
                <th style="padding: 12px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $u): ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 12px;">
                    <?= htmlspecialchars($u['nombre']) ?>
                    <?php if($u['id'] == $_SESSION['usuario_id']) echo " <span style='color:green; font-size:12px;'>(Tú)</span>"; ?>
                </td>
                <td style="padding: 12px;"><?= htmlspecialchars($u['email']) ?></td>
                <td style="padding: 12px;">
                    <?php if($u['rol'] === 'admin'): ?>
                        <span style="background:#fef3c7; color:#d97706; padding:2px 8px; border-radius:10px; font-size:12px; font-weight:bold;">ADMIN</span>
                    <?php elseif($u['rol'] === 'operador'): ?>
                         <span style="background:#e0f2fe; color:#0284c7; padding:2px 8px; border-radius:10px; font-size:12px; font-weight:bold;">OPERADOR</span>
                    <?php else: ?>
                         <span style="background:#f0fdf4; color:#10b981; padding:2px 8px; border-radius:10px; font-size:12px; font-weight:bold;">CLIENTE</span>
                    <?php endif; ?>
                </td>
                <td style="padding: 12px;">
                    <?php if($u['id'] != $_SESSION['usuario_id']): ?>
                        
                        <a href="usuarios.php?edit_id=<?= $u['id'] ?>" style="color: #0284c7; margin-right: 15px; text-decoration: none; font-weight: bold;">✏️ Editar</a>
                        
                        <form method="POST" onsubmit="return confirm('¿Estás seguro de ELIMINAR a este usuario?');" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <button type="submit" style="color: #ef4444; background: none; border: none; cursor: pointer; font-weight: bold;">🗑️ Eliminar</button>
                        </form>
                    <?php else: ?>
                        <span style="color: #ccc;">--</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>