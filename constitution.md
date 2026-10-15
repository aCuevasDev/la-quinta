# Constitución · La Quinta

> Reglas globales e innegociables. Si una tarea choca con alguna, se para y se pregunta antes de seguir.

## Prioridad

seguridad básica > claridad y accesibilidad > que funcione > estética

## Reglas inquebrantables

1. **El cliente nunca decide.** Todo dato que llega del navegador (formularios, URL, JavaScript) se valida de nuevo en PHP. El JavaScript solo ayuda al usuario.
2. **Autorización en cada página privada.** `inicio`, `categoria`, `reserva`, `mis_reservas` y `perfil` incluyen `includes/auth.php` en la primera línea. Sin sesión → redirige a `login.php`.
3. **Cada usuario ve solo lo suyo.** Las consultas de reservas y perfil filtran siempre por `usuario_id` de la sesión, nunca por un id que venga de la URL.
4. **Consultas preparadas con PDO.** Prohibido concatenar variables dentro de una sentencia SQL.
5. **Una sola conexión.** Solo `includes/db.php` crea la conexión a la base de datos.
6. **Contraseñas:** se guardan con `password_hash()` y se comprueban con `password_verify()`. Nunca en texto plano, nunca visibles en pantalla.
7. **Escapar al mostrar.** Todo texto que escribe el usuario se imprime con `htmlspecialchars()`.
8. **Fechas** guardadas como `DATE` (`AAAA-MM-DD`) y mostradas como `dd/mm/aaaa`. No se permite reservar un día anterior a hoy (se valida en PHP).
9. **Sin reservas duplicadas:** `UNIQUE (usuario_id, actividad_id, fecha)` en la base de datos y comprobación previa en PHP.
10. **Accesibilidad es requisito, no extra** (público: personas mayores): texto base 18 px, buen contraste, botones grandes (≥ 48 px), etiquetas siempre visibles, errores escritos en la página (nunca solo con color), un solo botón principal por pantalla.
11. **Todo texto visible en español**, con frases cortas y lenguaje llano.
12. **La spec es la única fuente de verdad.** Todo comportamiento visible debe salir de un RF, RNF o CL de `spec.md`. Lo que no está en la spec no se implementa..
13. **No introduzcas tecnologías** que no estén en la tabla del §2 de `AGENTS.md`.

## Prohibiciones rápidas

No concatenar SQL · no guardar contraseñas en claro · no confiar en el cliente · no mostrar errores técnicos de PHP/MySQL al usuario · no `localStorage` para datos de usuario o reservas · no carruseles, hover obligatorio, gestos ni scroll infinito · no límites de tiempo en formularios · no frameworks ni librerías nuevas sin aprobación.

## Fuera de alcance de esta versión

Pagos online, roles de guía y administrador, cancelar o reprogramar reservas, límite de plazas, recuperar contraseña, verificación por email o teléfono, cifrado de datos de salud, varios idiomas y app instalable (PWA). Quedan para una versión futura.