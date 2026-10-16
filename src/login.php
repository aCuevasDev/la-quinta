<?php
// Login (RF-01 a RF-03, RF-06). Página pública.
session_start();
require_once __DIR__ . '/includes/funciones.php';
require_once __DIR__ . '/includes/db.php';

if (!empty($_SESSION['usuario_id'])) {
    header('Location: inicio.php');
    exit;
}

$errores = [];
$nombre = '';
$apellidos = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = normalizar_nombre((string) ($_POST['nombre'] ?? ''));
    $apellidos = normalizar_nombre((string) ($_POST['apellidos'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $vacios = [];
    if ($nombre === '')   { $vacios[] = 'nombre'; }
    if ($apellidos === '') { $vacios[] = 'apellidos'; }
    if ($password === '')  { $vacios[] = 'contraseña'; }

    if ($vacios) {
        $errores[] = 'Faltan por rellenar: ' . implode(', ', $vacios) . '.';
    } else {
        $stmt = db()->prepare(
            'SELECT id, nombre, apellidos, password_hash
               FROM usuarios
              WHERE nombre = :nombre AND apellidos = :apellidos
              LIMIT 1'
        );
        $stmt->execute([':nombre' => $nombre, ':apellidos' => $apellidos]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = (int) $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'] . ' ' . $usuario['apellidos'];
            header('Location: inicio.php');
            exit;
        }

        // RF-03: mensaje genérico, sin decir qué dato ha fallado.
        $errores[] = 'El nombre, los apellidos o la contraseña no son correctos.';
    }
}

$titulo = 'Iniciar sesión';
$publica = true;
require __DIR__ . '/includes/cabecera.php';
?>
<h1>Iniciar sesión</h1>

<?php if (isset($_GET['registro']) && $_GET['registro'] === 'ok'): ?>
    <?= mensaje('Tu cuenta se ha creado. Ya puedes iniciar sesión.', 'exito') ?>
<?php endif; ?>

<?php if ($errores): ?>
    <div class="resumen-errores" role="alert">
        <p><strong>No se ha podido iniciar sesión.</strong></p>
        <ul>
            <?php foreach ($errores as $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="login.php" novalidate>
    <div class="campo">
        <label for="nombre">Nombre</label>
        <input type="text" id="nombre" name="nombre" value="<?= e($nombre) ?>"
               autocomplete="given-name" required>
    </div>

    <div class="campo">
        <label for="apellidos">Apellidos</label>
        <input type="text" id="apellidos" name="apellidos" value="<?= e($apellidos) ?>"
               autocomplete="family-name" required>
    </div>

    <div class="campo">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password"
               autocomplete="current-password" required>
        <button type="button" class="boton-secundario" data-mostrar-password="password"
                aria-pressed="false">Mostrar contraseña</button>
    </div>

    <button type="submit" class="boton">Entrar</button>
</form>

<p class="ayuda">¿Todavía no tienes cuenta? <a href="registro.php">Regístrate</a>.</p>
<?php require __DIR__ . '/includes/pie.php'; ?>
