<?php
// Utilidades comunes. No inicia sesión ni abre conexiones.

/** Escapa texto antes de mostrarlo en HTML (constitution, regla 7). */
function e(?string $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Lee una variable de entorno y devuelve un valor por defecto si no existe. */
function env(string $clave, string $defecto = ''): string
{
    $valor = getenv($clave);
    return ($valor === false || $valor === '') ? $defecto : $valor;
}

/** Mensaje visible en la página: con texto y rol de alerta, no solo color. */
function mensaje(string $texto, string $tipo = 'error'): string
{
    $clase = $tipo === 'exito' ? 'mensaje mensaje-exito' : 'mensaje mensaje-error';
    return '<p class="' . $clase . '" role="alert">' . e($texto) . '</p>';
}

/** Normaliza nombre/apellidos: recorta extremos y colapsa espacios internos (RF-02, RF-04). */
function normalizar_nombre(string $valor): string
{
    return (string) preg_replace('/\s+/u', ' ', trim($valor));
}

/** Nombre válido: 2-50 caracteres, solo letras (tildes, ñ, ç, ü), espacios, guion y apóstrofo. */
function nombre_valido(string $valor): bool
{
    $valor = trim($valor);
    $largo = mb_strlen($valor);
    return $largo >= 2 && $largo <= 50 && preg_match("/^[\p{L} '’-]+$/u", $valor) === 1;
}

/** Apellidos válidos: 2-80 caracteres, mismo juego de caracteres que el nombre. */
function apellidos_validos(string $valor): bool
{
    $valor = trim($valor);
    $largo = mb_strlen($valor);
    return $largo >= 2 && $largo <= 80 && preg_match("/^[\p{L} '’-]+$/u", $valor) === 1;
}

/** Contraseña válida: entre 8 y 64 caracteres (DA-01). */
function password_valida(string $valor): bool
{
    $largo = mb_strlen($valor);
    return $largo >= 8 && $largo <= 64;
}

/** ¿Es una fecha real en formato AAAA-MM-DD? Rechaza, por ejemplo, el 31 de febrero. */
function fecha_valida(string $fecha): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $fecha);
    return $d !== false && $d->format('Y-m-d') === $fecha;
}

/** Pasa una fecha AAAA-MM-DD a dd/mm/aaaa para mostrarla. */
function fecha_es(string $fecha): string
{
    $d = DateTime::createFromFormat('Y-m-d', $fecha);
    return $d ? $d->format('d/m/Y') : $fecha;
}
