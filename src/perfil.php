<?php
// Perfil: nombre y apellidos del usuario con sesión (RF-18). Página privada.
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/funciones.php';
require_once __DIR__ . '/includes/db.php';

$stmt = db()->prepare('SELECT nombre, apellidos FROM usuarios WHERE id = :id');
$stmt->execute([':id' => $_SESSION['usuario_id']]);
$usuario = $stmt->fetch();

$nombre = $usuario['nombre'] ?? '';
$apellidos = $usuario['apellidos'] ?? '';

$titulo = 'Mi perfil';
require __DIR__ . '/includes/cabecera.php';
?>
<h1>Mi perfil</h1>

<dl class="datos">
    <dt>Nombre</dt>
    <dd><?= e($nombre) ?></dd>
    <dt>Apellidos</dt>
    <dd><?= e($apellidos) ?></dd>
</dl>

<p><a class="boton" href="mis_reservas.php">Ver mis reservas</a></p>
<?php require __DIR__ . '/includes/pie.php'; ?>
