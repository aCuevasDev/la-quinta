<?php
// Quiénes somos (RF-19). Página privada.
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/funciones.php';

$titulo = 'Quiénes somos';
require __DIR__ . '/includes/cabecera.php';
?>
<h1>Quiénes somos</h1>

<p>La Quinta es un espacio pensado para que las personas mayores disfruten de
actividades presenciales cerca de casa, con calma y sin complicaciones.</p>

<p>Ofrecemos cuatro tipos de actividades:</p>
<ul>
    <li><strong>Ocio y relación:</strong> para conocer gente y pasar un buen rato.</li>
    <li><strong>Nuevas tecnologías:</strong> para perder el miedo al móvil y a internet.</li>
    <li><strong>Salud y deporte:</strong> ejercicio suave adaptado a cada persona.</li>
    <li><strong>Cultura y memoria:</strong> talleres para ejercitar la mente y disfrutar de la cultura.</li>
</ul>

<p>Para participar solo necesitas registrarte y reservar el día que mejor te venga.</p>

<p><a class="boton" href="inicio.php">Ver actividades</a></p>
<?php require __DIR__ . '/includes/pie.php'; ?>
