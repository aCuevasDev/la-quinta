<?php
// Inicio: las 4 categorías en tarjetas (RF-09). Página privada.
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/funciones.php';
require_once __DIR__ . '/includes/db.php';

$categorias = db()
    ->query('SELECT id, nombre, descripcion FROM categorias ORDER BY id')
    ->fetchAll();

$titulo = 'Inicio';
require __DIR__ . '/includes/cabecera.php';
?>
<h1>¿Qué te apetece hacer?</h1>
<p class="intro">Hola, <?= e($_SESSION['nombre']) ?>. Elige una categoría para ver sus actividades.</p>

<ul class="tarjetas">
    <?php foreach ($categorias as $categoria): ?>
        <li class="tarjeta">
            <h2><?= e($categoria['nombre']) ?></h2>
            <p><?= e($categoria['descripcion']) ?></p>
            <a class="boton" href="categoria.php?id=<?= (int) $categoria['id'] ?>">Ver detalles</a>
        </li>
    <?php endforeach; ?>
</ul>
<?php require __DIR__ . '/includes/pie.php'; ?>
