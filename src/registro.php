<?php
// Registro (RF-04 a RF-06, RF-21). Página pública.
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
    $nombre = trim((string) ($_POST['nombre'] ?? ''));
    $apellidos = trim((string) ($_POST['apellidos'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $vacios = [];
    if ($nombre === '')   { $vacios[] = 'nombre'; }
    if ($apellidos === '') { $vacios[] = 'apellidos'; }
    if ($password === '')  { $vacios[] = 'contraseña'; }

    if ($vacios) {
        $errores[] = 'Faltan por rellenar: ' . implode(', ', $vacios) . '.';
    } else {
        // RF-21: límites y formato.
        if (!nombre_valido($nombre)) {
            $errores[] = 'El nombre debe tener entre 2 y 50 letras (puede llevar tildes, espacios, guion y apóstrofo).';
        }
        if (!apellidos_validos($apellidos)) {
            $errores[] = 'Los apellidos deben tener entre 2 y 80 letras (pueden llevar tildes, espacios, guion y apóstrofo).';
        }
        if (!password_valida($password)) {
            $errores[] = 'La contraseña debe tener entre 8 y 64 caracteres.';
        }
    }

    if (!$errores) {
        // RF-05: mismo nombre y apellidos (sin distinguir mayúsculas ni tildes).
        $stmt = db()->prepare(
            'SELECT id FROM usuarios WHERE nombre = :nombre AND apellidos = :apellidos LIMIT 1'
        );
        $stmt->execute([':nombre' => $nombre, ':apellidos' => $apellidos]);

        if ($stmt->fetch()) {
            $errores[] = 'Ya existe una persona registrada con ese nombre y apellidos.';
        } else {
            try {
                $ins = db()->prepare(
                    'INSERT INTO usuarios (nombre, apellidos, password_hash)
                     VALUES (:nombre, :apellidos, :hash)'
                );
                $ins->execute([
                    ':nombre'    => $nombre,
                    ':apellidos' => $apellidos,
                    ':hash'      => password_hash($password, PASSWORD_DEFAULT),
                ]);
                // RF-04: cuenta creada; aviso de éxito en login.
                header('Location: login.php?registro=ok');
                exit;
            } catch (PDOException $ex) {
                error_log('La Quinta · error al registrar: ' . $ex->getMessage());
                $errores[] = 'No se ha podido crear la cuenta. Inténtalo de nuevo.';
            }
        }
    }
}

$titulo = 'Crear cuenta';
$publica = true;
require __DIR__ . '/includes/cabecera.php';
?>
<h1>Crear cuenta</h1>

<?php if ($errores): ?>
    <div class="resumen-errores" role="alert">
        <p><strong>No se ha podido crear la cuenta.</strong></p>
        <ul>
            <?php foreach ($errores as $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="registro.php" novalidate>
    <div class="campo">
        <label for="nombre">Nombre</label>
        <input type="text" id="nombre" name="nombre" value="<?= e($nombre) ?>"
               autocomplete="given-name" required>
        <p class="pista">Entre 2 y 50 letras.</p>
    </div>

    <div class="campo">
        <label for="apellidos">Apellidos</label>
        <input type="text" id="apellidos" name="apellidos" value="<?= e($apellidos) ?>"
               autocomplete="family-name" required>
        <p class="pista">Entre 2 y 80 letras.</p>
    </div>

    <div class="campo">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password"
               autocomplete="new-password" minlength="8" maxlength="64" required>
        <p class="pista">Entre 8 y 64 caracteres.</p>
        <button type="button" class="boton-secundario" data-mostrar-password="password"
                aria-pressed="false">Mostrar contraseña</button>
    </div>

    <button type="submit" class="boton">Crear cuenta</button>
</form>

<p class="ayuda">¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a>.</p>
<?php require __DIR__ . '/includes/pie.php'; ?>
