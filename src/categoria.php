<?php
// Detalle de una categoría con sus 3 actividades (RF-10, CL-05). Página privada.
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/funciones.php';
require_once __DIR__ . '/includes/db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$categoria = null;
$actividades = [];

if ($id !== null && $id !== false) {
    $stmt = db()->prepare('SELECT id, nombre, descripcion FROM categorias WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $categoria = $stmt->fetch();

    if ($categoria) {
        $stmt = db()->prepare(
            'SELECT id, nombre, descripcion FROM actividades WHERE categoria_id = :id ORDER BY id'
        );
        $stmt->execute([':id' => $id]);
        $actividades = $stmt->fetchAll();
    }
}

// CL-05: id inexistente, no numérico o vacío.
if (!$categoria) {
    $titulo = 'Categoría no encontrada';
    require __DIR__ . '/includes/cabecera.php';
    echo '<h1>Categoría no encontrada</h1>';
    echo mensaje('No hemos encontrado esa categoría.', 'error');
    echo '<p><a class="boton" href="inicio.php">Volver al inicio</a></p>';
    require __DIR__ . '/includes/pie.php';
    exit;
}

$titulo = $categoria['nombre'];
require __DIR__ . '/includes/cabecera.php';
?>
<p class="migas"><a href="inicio.php">Inicio</a> › <?= e($categoria['nombre']) ?></p>
<h1><?= e($categoria['nombre']) ?></h1>
<p class="intro"><?= e($categoria['descripcion']) ?></p>

<ul class="tarjetas">
    <?php foreach ($actividades as $actividad): ?>
        <li class="tarjeta">
            <h2><?= e($actividad['nombre']) ?></h2>
            <p><?= e($actividad['descripcion']) ?></p>
            <a class="boton" href="reserva.php?id=<?= (int) $actividad['id'] ?>">Reservar</a>
        </li>
    <?php endforeach; ?>
</ul>
<?php require __DIR__ . '/includes/pie.php'; ?>
