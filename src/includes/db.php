<?php
// ÚNICO lugar que abre la conexión a la base de datos (constitution, regla 5).
// Lee la conexión de variables de entorno; así funciona igual con Docker,
// XAMPP o `php -S` (plan, riesgo «el contenedor no conecta con la base de datos»).

require_once __DIR__ . '/funciones.php';

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = env('DB_HOST', 'localhost');
    $port = env('DB_PORT', '3306');
    $name = env('DB_NAME', 'la_quinta');
    $user = env('DB_USER', 'root');
    $pass = env('DB_PASSWORD', '');

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $ex) {
        // Mensaje amable, sin datos técnicos (RNF-05). El detalle va al registro.
        error_log('La Quinta · error de conexión a la base de datos: ' . $ex->getMessage());
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html lang="es"><head><meta charset="utf-8">'
           . '<meta name="viewport" content="width=device-width, initial-scale=1">'
           . '<title>Estamos teniendo un problema</title></head>'
           . '<body style="font-family: system-ui, sans-serif; font-size: 18px; line-height: 1.5; padding: 2rem; max-width: 40rem; margin: 0 auto">'
           . '<h1>Estamos teniendo un problema</h1>'
           . '<p>Ahora mismo no podemos conectar con la base de datos. Vuelve a intentarlo dentro de unos minutos.</p>'
           . '<p><a href="login.php">Volver al inicio</a></p>'
           . '</body></html>';
        exit;
    }

    return $pdo;
}
