<?php
// Reservar una actividad en un día (RF-11 a RF-15, CL-03, CL-04, CL-06). Página privada.
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/funciones.php';
require_once __DIR__ . '/includes/db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // El id de la actividad viaja en el formulario; el usuario NO (regla 3).
    $id = filter_input(INPUT_POST, 'actividad_id', FILTER_VALIDATE_INT);
}

$actividad = null;
if ($id !== null && $id !== false) {
    $stmt = db()->prepare(
        'SELECT a.id, a.nombre, a.descripcion, c.nombre AS categoria
           FROM actividades a
           JOIN categorias c ON c.id = a.categoria_id
          WHERE a.id = :id'
    );
    $stmt->execute([':id' => $id]);
    $actividad = $stmt->fetch();
}

// CL-06: actividad inexistente o id inválido.
if (!$actividad) {
    $titulo = 'Actividad no encontrada';
    require __DIR__ . '/includes/cabecera.php';
    echo '<h1>Actividad no encontrada</h1>';
    echo mensaje('No hemos encontrado esa actividad.', 'error');
    echo '<p><a class="boton" href="inicio.php">Volver al inicio</a></p>';
    require __DIR__ . '/includes/pie.php';
    exit;
}

$errores = [];
$aviso = '';
$fecha = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha = trim((string) ($_POST['fecha'] ?? ''));

    if ($fecha === '') {
        // RF-12: sin día.
        $errores[] = 'Elige un día para la reserva.';
    } elseif (!fecha_valida($fecha)) {
        // CL-11: fecha inexistente o con formato raro.
        $errores[] = 'La fecha elegida no es válida.';
    } elseif ($fecha < date('Y-m-d')) {
        // RF-13 y CL-03: día anterior a hoy, aunque se manipule el formulario.
        $errores[] = 'No puedes reservar un día que ya ha pasado.';
    }

    if (!$errores) {
        // RF-14 y CL-04: ¿ya existe esa actividad ese día para este usuario?
        $stmt = db()->prepare(
            'SELECT id FROM reservas
              WHERE usuario_id = :usuario_id AND actividad_id = :actividad_id AND fecha = :fecha
              LIMIT 1'
        );
        $stmt->execute([
            ':usuario_id'   => $_SESSION['usuario_id'],
            ':actividad_id' => $actividad['id'],
            ':fecha'        => $fecha,
        ]);

        if ($stmt->fetch()) {
            $aviso = 'Ya tienes esa actividad reservada ese día.';
        } else {
            try {
                // RF-15: se guarda asociada al usuario de la sesión.
                $ins = db()->prepare(
                    'INSERT INTO reservas (usuario_id, actividad_id, fecha)
                     VALUES (:usuario_id, :actividad_id, :fecha)'
                );
                $ins->execute([
                    ':usuario_id'   => $_SESSION['usuario_id'],
                    ':actividad_id' => $actividad['id'],
                    ':fecha'        => $fecha,
                ]);
                header('Location: mis_reservas.php?reserva=ok');
                exit;
            } catch (PDOException $ex) {
                error_log('La Quinta · error al reservar: ' . $ex->getMessage());
                $aviso = 'Ya tienes esa actividad reservada ese día.';
            }
        }
    }
}

$titulo = 'Reservar';
require __DIR__ . '/includes/cabecera.php';
?>
<p class="migas"><a href="inicio.php">Inicio</a> › <?= e($actividad['categoria']) ?> › <?= e($actividad['nombre']) ?></p>
<h1><?= e($actividad['nombre']) ?></h1>
<p class="etiqueta"><?= e($actividad['categoria']) ?></p>
<p><?= e($actividad['descripcion']) ?></p>

<?php if ($errores): ?>
    <div class="resumen-errores" role="alert">
        <p><strong>No se ha podido reservar.</strong></p>
        <ul>
            <?php foreach ($errores as $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if ($aviso !== ''): ?>
    <?= mensaje($aviso, 'exito') ?>
<?php endif; ?>

<form method="post" action="reserva.php?id=<?= (int) $actividad['id'] ?>" novalidate>
    <input type="hidden" name="actividad_id" value="<?= (int) $actividad['id'] ?>">

    <div class="campo">
        <label for="fecha">¿Qué día quieres venir?</label>
        <input type="date" id="fecha" name="fecha" value="<?= e($fecha) ?>"
               min="<?= e(date('Y-m-d')) ?>" required>
    </div>

    <button type="submit" class="boton">Confirmar reserva</button>
</form>
<?php require __DIR__ . '/includes/pie.php'; ?>
