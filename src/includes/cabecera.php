<?php
// Cabecera y navegación comunes (RF-20).
// Variables esperadas: $titulo (string). En páginas públicas, $publica = true.
require_once __DIR__ . '/funciones.php';

$titulo = $titulo ?? 'La Quinta';
$publica = $publica ?? false;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo) ?> · La Quinta</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body class="<?= $publica ? 'es-publica' : 'es-privada' ?>">
    <a class="salto-contenido" href="#contenido">Saltar al contenido</a>

    <header class="cabecera">
        <div class="cabecera__interior">
            <p class="marca">
                <?php if ($publica): ?>
                    La Quinta
                <?php else: ?>
                    <a href="inicio.php">La Quinta</a>
                <?php endif; ?>
            </p>

            <?php if (!$publica): ?>
                <nav class="menu" aria-label="Menú principal">
                    <ul>
                        <li><a href="inicio.php">Inicio</a></li>
                        <li><a href="mis_reservas.php">Mis reservas</a></li>
                        <li><a href="perfil.php">Mi perfil</a></li>
                        <li><a href="quienes_somos.php">Quiénes somos</a></li>
                        <li><a href="logout.php">Cerrar sesión</a></li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </header>

    <main id="contenido" class="contenido">
