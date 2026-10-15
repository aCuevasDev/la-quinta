# PLAN.md · La Quinta

Plan técnico y de construcción por fases. **Se completa una fase entera (su Definición de Hecho) antes de empezar la siguiente.** El detalle ejecutable está en `specs/tasks.md`; las reglas, en `constitution.md` y `AGENTS.md`; el contrato (qué debe hacer la web), en `specs/spec.md`.

## 1. Visión y orden de construcción

Primero cimientos y base de datos (F0–F1), después el acceso de usuarios (F2), luego el producto de cara al usuario (F3–F5) y por último calidad y entrega (F6). Las reservas solo se construyen cuando el acceso y la protección de páginas privadas ya están probados.

```
F0 Cimientos → F1 Base de datos → F2 Acceso → F3 Inicio y actividades
   → F4 Reservas → F5 Perfil y páginas informativas → F6 Calidad y entrega
```

## 2. Arquitectura

Aplicación web con **páginas PHP independientes** que comparten archivos de `includes/` (conexión, sesión, cabecera y pie). No hay framework: cada página recibe la petición, comprueba la sesión, consulta la base de datos con PDO y devuelve HTML.

```
Navegador ──► página .php ──► includes/auth.php (sesión)
                         └──► includes/db.php (PDO) ──► MySQL
                         └──► includes/cabecera.php / pie.php
```

### Mapa de páginas

| Página | Pública | Qué hace | Requisitos |
|---|---|---|---|
| `login.php` | Sí | Inicio de sesión y enlace a registro | RF-01 a RF-03, RF-06 |
| `registro.php` | Sí | Crear cuenta | RF-04 a RF-06 |
| `logout.php` | No | Cerrar sesión | RF-08 |
| `inicio.php` | No | 4 tarjetas de categorías | RF-09 |
| `categoria.php?id=` | No | 3 actividades de la categoría | RF-10 |
| `reserva.php?id=` | No | Detalle de la actividad y elección de día | RF-11 a RF-15 |
| `mis_reservas.php` | No | Reservas del usuario | RF-16, RF-17 |
| `perfil.php` | No | Datos del usuario | RF-18 |
| `quienes_somos.php` | No | Información de La Quinta | RF-19 |

La navegación común (RF-20) se incluye desde `cabecera.php` en todas las páginas privadas.

### Módulos (`includes/`)

- `db.php`: función que devuelve la conexión PDO (modo de errores por excepciones, juego de caracteres `utf8mb4`). Es el único sitio donde se abre la conexión.
- `auth.php`: inicia la sesión PHP y, si no existe `$_SESSION['usuario_id']`,
  redirige a `login.php`. Se incluye en la primera línea de cada página privada
  y nunca en `login.php`, `registro.php` ni `logout.php`, que llaman a
  `session_start()` por su cuenta (si no, se redirigirían a sí mismas en bucle).
- `cabecera.php` y `pie.php`: HTML común, navegación y enlace «Saltar al contenido».

### Flujo de sesión

1. Al iniciar sesión correctamente se llama a `session_regenerate_id(true)` y se guardan `$_SESSION['usuario_id']` y `$_SESSION['nombre']`.
2. Toda página privada incluye `auth.php`.
3. Al cerrar sesión se vacía `$_SESSION`, se destruye la sesión y se redirige a `login.php`.
4. Para saber quién es el usuario se usa siempre la sesión, nunca un dato recibido por URL o formulario.

### JavaScript

Solo como ayuda al usuario: campos obligatorios, mostrar/ocultar contraseña (opcional) y fecha mínima de hoy en el selector de día. La validación definitiva siempre se hace en PHP (regla 1 de la constitución).

## 3. Datos

Base de datos relacional MySQL/MariaDB con 4 tablas (ver `sql/schema.sql`).

| Tabla | Campos principales | Restricciones |
|---|---|---|
| `usuarios` | `id`, `nombre`, `apellidos`, `password_hash`, `creado_en` | `UNIQUE (nombre, apellidos)` |
| `categorias` | `id`, `nombre`, `descripcion` | — |
| `actividades` | `id`, `categoria_id`, `nombre`, `descripcion` | `FOREIGN KEY` a `categorias` |
| `reservas` | `id`, `usuario_id`, `actividad_id`, `fecha`, `creado_en` | `FOREIGN KEY` a `usuarios` y `actividades`; `UNIQUE (usuario_id, actividad_id, fecha)` |

Relaciones:
- `categorias` 1 ── N `actividades`
- `usuarios` 1 ── N `reservas`
- `actividades` 1 ── N `reservas`
- `usuarios` N ── N `actividades` (a través de `reservas`)

Consulta principal de «Mis reservas» (con `JOIN`, filtrada por la sesión):

```sql
SELECT r.fecha, a.nombre AS actividad, c.nombre AS categoria
FROM reservas r
JOIN actividades a ON a.id = r.actividad_id
JOIN categorias c ON c.id = a.categoria_id
WHERE r.usuario_id = :usuario_id
ORDER BY r.fecha;
```

Datos de ejemplo (`sql/datos.sql`): 4 categorías y 12 actividades (3 por categoría).

## 4. Decisiones técnicas y simplificaciones

El proyecto original de La Quinta era más ambicioso (pagos, roles, datos de salud, varios idiomas). Esta versión se limita a lo que se domina: PHP, MySQL, HTML, CSS y JavaScript. **Prevalece lo escrito en este plan y en `specs/spec.md`.**

| ID | Decisión | Motivo |
|---|---|---|
| D1 | PHP sin framework, MySQL/MariaDB con PDO | Es el stack conocido; permite terminar y entender todo el código |
| D2 | Sesiones de PHP (`$_SESSION`) | Suficiente para un solo tipo de usuario |
| D3 | El registro pide **nombre, apellidos y contraseña** | El login exige contraseña; si el registro no la pide, nadie podría entrar. **Pendiente de confirmar** |
| D4 | La identidad es nombre + apellidos (`UNIQUE`) | Es lo que pide el enunciado. Limitación conocida: dos personas con el mismo nombre y apellidos no pueden registrarse (ver riesgos) |
| D5 | Sin pagos, roles, cancelación, límite de plazas ni recuperación de contraseña | Fuera de alcance de esta versión (ver `constitution.md`) |
| D6 | Actividades y categorías en la base de datos, no escritas a mano en el HTML | Un solo lugar para editar los textos y las 12 actividades |
| D7 | Fechas como `DATE` (`AAAA-MM-DD`), mostradas `dd/mm/aaaa` | Es lo único que necesita una reserva por día |
| D8 | Mismo mensaje de error si falla el login, sin decir qué dato falló | No ayudar a adivinar usuarios existentes |
| D9 | No se piden ni guardan datos de salud ni datos sensibles | Reduce el riesgo; si se añadieran en el futuro habría que rehacer la seguridad |
| D10 | Mejora opcional: token CSRF en los formularios | Se valorará si hay tiempo en F6 |

## 5. Fases

### F0 · Cimientos
- **Objetivo:** proyecto ejecutable en local, con estructura, estilo común y control de versiones.
- **Entregables:** repositorio en GitHub con `constitution.md`, `AGENTS.md` y `specs/`; carpetas y páginas vacías; entorno local funcionando; CSS base accesible; cabecera y pie comunes.
- **Hecho cuando:** una página de prueba se ve en local usando cabecera, pie y estilo comunes.

### F1 · Base de datos
- **Objetivo:** esquema relacional y datos de ejemplo cargados desde cero.
- **Entregables:** `sql/schema.sql`, `sql/datos.sql` y `includes/db.php`.
- **Hecho cuando:** dos importaciones crean todo, las restricciones fallan como deben ante datos inválidos y la conexión solo se abre desde `db.php`.

### F2 · Acceso
- **Objetivo:** registrarse, entrar y salir con páginas privadas protegidas.
- **Entregables:** `auth.php`, `registro.php`, `login.php`, `logout.php` y validación en servidor y en JavaScript.
- **Hecho cuando:** recorrido registro → login → cerrar sesión correcto y errores de RF-03, RF-05 y RF-06 controlados.

### F3 · Inicio y actividades
- **Objetivo:** explorar las categorías y sus actividades.
- **Entregables:** `inicio.php`, `categoria.php`, tarjetas accesibles y control de ids inexistentes.
- **Hecho cuando:** desde inicio se llega a cada categoría y a sus 3 actividades; un id erróneo muestra un mensaje claro.

### F4 · Reservas
- **Objetivo:** reservar un día y consultar las reservas propias.
- **Entregables:** `reserva.php` con selector de día, validaciones (sin día, día pasado, duplicada) y `mis_reservas.php` con `JOIN`.
- **Hecho cuando:** se reserva, aparece en «Mis reservas» solo para su usuario y los errores de RF-12, RF-13 y RF-14 están controlados.

### F5 · Perfil y páginas informativas
- **Objetivo:** completar el resto de páginas y la navegación.
- **Entregables:** `perfil.php`, `quienes_somos.php` y menú común.
- **Hecho cuando:** todas las páginas son accesibles desde el menú.

### F6 · Calidad y entrega
- **Objetivo:** verificar, pulir y entregar.
- **Entregables:** revisión de seguridad, prueba de casos límite, accesibilidad con teclado y zoom, prueba con personas mayores, spec y plan actualizados, entrega en Git.
- **Hecho cuando:** se cumplen todos los RF de `spec.md`, spec y código coinciden y el repositorio está limpio.

## 6. Riesgos

| Riesgo | Mitigación |
|---|---|
| Inyección SQL | PDO con consultas preparadas en todo el código; revisión en F6-01 |
| Contraseñas expuestas | `password_hash()` y `password_verify()`; nunca en claro ni visibles |
| Un usuario ve datos de otro | Filtrar siempre por `usuario_id` de la sesión; prueba con dos usuarios en F4-09 |
| Dos personas con el mismo nombre y apellidos | Limitación asumida por D4; si hay que resolverla, añadir un campo identificador (por ejemplo, teléfono o usuario) y cambiar `spec.md` antes de tocar código |
| Reservas duplicadas o con fecha pasada | Validación en PHP y `UNIQUE` en la base de datos |
| Personas mayores no logran usar la web | Texto grande, botones grandes, mensajes claros, prueba con usuarios reales en F6-06 |
| Spec y código se desincronizan | Cada cambio pasa primero por `spec.md`; revisión final en F6-07 |
| Errores técnicos visibles al usuario | Excepciones controladas y errores de PHP ocultos en la versión final (F6-03) |

## 7. Decisiones pendientes

Ver «Dudas abiertas» de `specs/spec.md`. Hasta que se cierren se usan los valores por defecto de este plan:

1. ¿Hay que añadir cancelar reserva? (por defecto: no)
2. ¿Hay límite de personas por día? (por defecto: no)
3. ¿Qué información extra lleva cada actividad: horario, lugar, precio? (por defecto: nombre, descripción y categoría)
4. ¿El registro lleva contraseña? (por defecto: sí, ver D3)

## 8. Cómo usar este plan con IA

1. Abre el repo con `constitution.md`, `AGENTS.md`, `specs/spec.md`, `specs/plan.md` y `specs/tasks.md`.
2. **Modo Plan primero:** antes de codificar una fase, pide a la IA que lea la spec y proponga cómo la haría; revisa y aprueba.
3. Pide: «Ejecuta la siguiente tarea sin marcar de `specs/tasks.md` siguiendo `AGENTS.md`».
4. Revisa el diff, prueba a mano el «Hecho cuando» y deja que marque la tarea.
5. Al completar una fase, pide: «Verifica la Definición de Hecho de la fase X de `specs/plan.md` y lista lo que falte».
6. Si la IA se encuentra una decisión no acordada, debe parar y preguntar; se anota en `spec.md` y después se continúa.