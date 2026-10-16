<?php
// Protege las páginas privadas. Se incluye SIEMPRE en la primera línea de
// inicio.php, categoria.php, reserva.php, mis_reservas.php y perfil.php
// (constitution, regla 2). Nunca en login, registro ni logout.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
