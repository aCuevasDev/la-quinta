<?php
// Punto de entrada: decide a dónde ir según haya sesión o no.
session_start();

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
} else {
    header('Location: inicio.php');
}
exit;
