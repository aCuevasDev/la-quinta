<?php
// Mis reservas (RF-16, RF-17, CL-07). Página privada.
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/funciones.php';
require_once __DIR__ . '/includes/db.php';

// CL-07: se filtra SIEMPRE por el usuario de la sesión.
$stmt = db()->prepare(
    'SELECT r.fecha, a.nombre AS actividad, c.nombre AS categoria
       FROM reservas r
       JOIN actividades a ON a.id = r.actividad_id
       JOIN categorias c ON c.id = a.categoria_id
      WHERE r.usuario_id = :usuario_id
      ORDER BY r.fecha, a.nombre'
);
$stmt->execute([':usuario_id' => $_SESSION['usuario_id']]);
$reservas = $stmt->fetchAll();

$titulo = 'Mis reservas';
require __DIR__ . '/includes/cabecera.php';
?>
<h1>Mis reservas</h1>

<?php if (isset($_GET['reserva']) && $_GET['reserva'] === 'ok'): ?>
    <?= mensaje('Tu reserva se ha guardado.', 'exito') ?>
<?php endif; ?>

<?php if (!$reservas): ?>
    <?= mensaje('Todavía no tienes ninguna reserva.', 'exito') ?>
    <p><a class="boton" href="inicio.php">Ver actividades</a></p>
<?php else: ?>
    <table class="tabla">
        <caption class="visualmente-oculto">Lista de tus reservas</caption>
        <thead>
            <tr>
                <th scope="col">Día</th>
                <th scope="col">Actividad</th>
                <th scope="col">Categoría</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reservas as $reserva): ?>
                <tr>
                    <td><?= e(fecha_es($reserva['fecha'])) ?></td>
                    <td><?= e($reserva['actividad']) ?></td>
                    <td><?= e($reserva['categoria']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php require __DIR__ . '/includes/pie.php'; ?>
