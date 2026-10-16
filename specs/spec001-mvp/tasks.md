# TASKS.md · La Quinta

Lista ejecutable por fases. **Regla:** toma la primera tarea sin marcar de la fase activa, lee `constitution.md` y solo el apartado de `specs/spec001-mvp/spec.md` que cita la tarea, comprueba a mano su «Hecho cuando» (incluidos los casos de error) y marca `[x]` al terminar. No pases de fase sin cumplir su Definición de Hecho (al final de cada fase).

Formato: `- [ ] ID · Tarea` → referencia · **Hecho cuando** …

Si una tarea queda a medias, añade debajo una línea `  - Notas: qué está hecho, qué falta, archivos tocados`.

---

## F0 · Cimientos
- [ ] F0-01 · Crear el repositorio Git, subirlo a GitHub y añadir `constitution.md`, `AGENTS.md` y la carpeta `specs/spec001-mvp/` con `spec.md`, `plan.md` y `tasks.md` → `AGENTS` §3 · el repo está en GitHub con esa estructura
- [ ] F0-02 · Crear las carpetas y archivos vacíos del proyecto (`includes/`, `sql/`, `css/`, `js/`, `img/` y las 9 páginas `.php`) → `AGENTS` §3 · la estructura coincide con la de `AGENTS.md`
- [ ] F0-03 · Preparar el entorno local (XAMPP, Docker o `php -S`) y anotarlo en `AGENTS.md` §2 → `AGENTS` §2 · una página `.php` de prueba se ve en el navegador y `[completar]` ya no aparece
- [ ] F0-04 · CSS base accesible: texto 18 px, interlineado 1,5, contraste alto, botones ≥ 48 px, foco visible → `constitution` regla 10 · una página de muestra con título, texto, formulario y botón cumple todo
- [ ] F0-05 · `includes/cabecera.php` y `includes/pie.php` con navegación común y enlace «Saltar al contenido» → RF-20 · se incluyen en una página de prueba y se navega con teclado
- [ ] F0-06 · Plantilla de mensajes de error y éxito visibles en la página (con texto, no solo color) → `constitution` regla 10 · un mensaje de prueba se ve bien en móvil y escritorio

**Definición de Hecho F0:** el proyecto arranca en local, la estructura está en Git y una página de prueba usa cabecera, pie y estilo comunes.

## F1 · Base de datos
- [ ] F1-01 · Escribir `sql/schema.sql` con las tablas `usuarios`, `categorias`, `actividades` y `reservas` (claves foráneas y `UNIQUE`) → `plan` Datos · se importa sin errores y las 4 tablas existen
- [ ] F1-02 · Escribir `sql/datos.sql` con 4 categorías (con su descripción breve) y 12 actividades (3 por categoría) → `spec` RF-09, RF-10 · tras importar, cada categoría tiene exactamente 3 actividades
- [ ] F1-03 · `includes/db.php` con la conexión PDO (errores controlados, sin mostrar datos técnicos) → `constitution` reglas 4 y 5 · una página de prueba conecta y, con datos de conexión falsos, muestra un mensaje amable
- [ ] F1-04 · Comprobar las restricciones en la base de datos (usuario duplicado, reserva duplicada, actividad inexistente) → `constitution` regla 9 · cada intento inválido falla en MySQL

**Definición de Hecho F1:** la base de datos se crea desde cero con dos importaciones y `db.php` es el único sitio donde se abre la conexión.

## F2 · Acceso
- [ ] F2-01 · `includes/auth.php`: inicia sesión PHP y redirige a `login.php` si no hay `$_SESSION['usuario_id']` solo lo incluyen las páginas privadas → RF-07 · abrir una página privada sin sesión lleva a `login.php`
- [ ] F2-02 · `registro.php`: formulario con nombre, apellidos y contraseña, validación en servidor (campos vacíos y longitud) → RF-04, RF-06 · cada campo vacío indica cuál falta
- [ ] F2-03 · Registro: guardar con `password_hash()` y rechazar nombre y apellidos duplicados → RF-04, RF-05 · usuario nuevo guardado con contraseña cifrada; el duplicado muestra error y no se crea
- [ ] F2-04 · Registro: tras crear la cuenta, redirigir a `login.php` con mensaje de éxito → RF-04 · tras registrarse se llega a login con el aviso visible
- [ ] F2-05 · `login.php`: formulario con nombre, apellidos, contraseña y enlace a registro → RF-01 · el formulario se ve completo y el enlace funciona
- [ ] F2-06 · Login: comprobar con `password_verify()`, guardar `usuario_id` y nombre en sesión y redirigir a `inicio.php` → RF-02 · entra con datos correctos
- [ ] F2-07 · Login: credenciales incorrectas o campos vacíos muestran error sin revelar qué dato falló → RF-03, RF-06 · no se inicia sesión y el mensaje es claro
- [ ] F2-08 · `logout.php`: cerrar sesión y volver a `login.php` → RF-08 · tras cerrar sesión, las páginas privadas vuelven a pedir login
- [ ] F2-09 · JavaScript de ayuda en los formularios (campos obligatorios, mostrar/ocultar contraseña opcional) sin sustituir la validación de PHP → `constitution` regla 1 · desactivando JS, la validación de PHP sigue funcionando

**Definición de Hecho F2:** recorrido registro → login → cerrar sesión correcto y todos los errores de RF-03, RF-05 y RF-06 controlados.

## F3 · Inicio y actividades
- [ ] F3-01 · `inicio.php`: leer las categorías de la BD y mostrarlas en tarjetas con explicación breve y botón «Ver detalles» → RF-09 · se ven las 4 tarjetas; solo accede quien tiene sesión
- [ ] F3-02 · `categoria.php?id=`: mostrar las 3 actividades de la categoría con botón «Reservar» → RF-10 · cada categoría muestra sus 3 actividades
- [ ] F3-03 · `categoria.php`: controlar un `id` inexistente o inválido → casos límite · mensaje claro y enlace para volver, sin errores técnicos
- [ ] F3-04 · Estilo de tarjetas accesible (un solo botón principal por tarjeta, imágenes con `alt`) → `constitution` regla 10 · revisadas con teclado y en móvil

**Definición de Hecho F3:** desde inicio se llega a cada categoría y a sus actividades; ids erróneos controlados.

## F4 · Reservas
- [ ] F4-01 · `reserva.php?id=`: mostrar la información completa de la actividad y un selector de día → RF-11 · se ven nombre, descripción, categoría y selector
- [ ] F4-02 · `reserva.php`: controlar un `id` de actividad inexistente → casos límite · mensaje claro sin error técnico
- [ ] F4-03 · Reserva: error si se confirma sin elegir día → RF-12 · no se crea reserva y el error se ve junto al campo
- [ ] F4-04 · Reserva: error si el día es anterior a hoy (validación en PHP y ayuda en JS con `min`) → RF-13 · no se crea reserva con fecha pasada, aunque se manipule el formulario
- [ ] F4-05 · Reserva: avisar si ya existe esa actividad ese día para el usuario → RF-14 · no se duplica y sale un aviso visible
- [ ] F4-06 · Reserva: guardar con `usuario_id` de la sesión (nunca del formulario) y redirigir a `mis_reservas.php` → RF-15, `constitution` regla 3 · la reserva aparece en la base de datos con el usuario correcto
- [ ] F4-07 · `mis_reservas.php`: listar las reservas del usuario con `JOIN` (actividad, categoría, día) → RF-16 · solo se ven las reservas propias
- [ ] F4-08 · `mis_reservas.php`: mensaje cuando no hay reservas → RF-17 · usuario nuevo ve el mensaje
- [ ] F4-09 · Comprobar con dos usuarios distintos que cada uno ve solo sus reservas → `constitution` regla 3 · el usuario A no ve nada del usuario B

**Definición de Hecho F4:** se reserva, se ve en «Mis reservas» y los errores de RF-12, RF-13 y RF-14 están controlados.

## F5 · Perfil y páginas informativas
- [ ] F5-01 · `perfil.php`: mostrar nombre y apellidos del usuario con sesión → RF-18 · muestra los datos del usuario que ha iniciado sesión
- [ ] F5-02 · `quienes_somos.php`: página informativa accesible desde la navegación → RF-19 · se abre desde el menú
- [ ] F5-03 · Revisar la navegación común (inicio, mis reservas, perfil, quiénes somos, cerrar sesión) en todas las páginas privadas → RF-20 · el menú es igual y funciona en todas

**Definición de Hecho F5:** todas las páginas del proyecto son accesibles desde el menú.

## F6 · Calidad y entrega
- [ ] F6-01 · Revisión de seguridad: consultas preparadas en todo el código, `htmlspecialchars()` en toda salida y ninguna contraseña visible → `constitution` reglas 4, 6 y 7 · búsqueda en el código sin concatenar SQL ni imprimir datos sin escapar
- [ ] F6-02 · Probar todos los casos límite: tildes y ñ, URL privada sin sesión, día pasado, reserva repetida, ids inexistentes → `spec` Casos límite · cada caso funciona como indica la spec
- [ ] F6-03 · Desactivar la visualización de errores técnicos de PHP en la versión final → `AGENTS` §8 · un fallo provocado no muestra rutas ni consultas
- [ ] F6-04 · Accesibilidad: recorrido solo con teclado, zoom 200 %, móvil y escritorio → `constitution` regla 10 · sin elementos solapados ni controles inalcanzables
- [ ] F6-05 · Recorrido completo de control: registro → login → inicio → categoría → reservar → mis reservas → perfil → cerrar sesión → `spec` Hecho cuando · todo el flujo funciona sin errores
- [ ] F6-06 · Probar la web con una o dos personas mayores y anotar qué les cuesta entender → `spec` Contexto · lista de mejoras anotada y aplicada o apuntada en dudas abiertas
- [ ] F6-07 · Actualizar `specs/spec001-mvp/spec.md` y `specs/spec001-mvp/plan.md` con lo que haya cambiado durante el desarrollo y cerrar las dudas abiertas → `spec` Dudas abiertas · spec y código coinciden (spec-anchored)
- [ ] F6-08 · Commit final y Pull Request o entrega con el repositorio limpio → `AGENTS` §5 · rama principal con todo y sin archivos de prueba

**Definición de Hecho F6:** la web cumple todos los RF de `specs/spec001-mvp/spec.md`, spec y código están sincronizados y el proyecto está entregado en Git.