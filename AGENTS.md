# AGENTS.md · La Quinta

> Lee este archivo entero antes de escribir código. Las reglas globales están en `constitution.md`; el contrato (qué debe hacer la web) está en `specs/spec001-mvp/spec.md`.

## 1. Qué es el proyecto

**La Quinta** es una web para que **personas mayores** reserven actividades presenciales en 4 categorías: Ocio y relación, Nuevas tecnologías, Salud y deporte, y Cultura y memoria. Cada categoría tiene 3 actividades.

Hay un único tipo de usuario: la persona registrada. Flujo principal: registro → login → inicio → categoría → reservar (elegir día) → mis reservas. Además hay páginas de perfil y «Quiénes somos».

Público con poca soltura digital. Prioridad: seguridad básica > claridad y accesibilidad > que funcione > estética.

## 2. Stack (decisiones cerradas; no cambiar sin aprobación)

| Capa                 | Decisión                                                       |
| -------------------- | -------------------------------------------------------------- |
| Servidor             | PHP 8 sin framework                                            |
| Base de datos        | MySQL (relacional)                                             |
| Acceso a datos       | PDO con consultas preparadas                                   |
| Front                | HTML5, CSS y JavaScript sin librerías                          |
| Sesiones             | Sesiones de PHP (`$_SESSION`)                                  |
| Entorno local        | **Docker (Docker Compose)**: `docker compose up -d` · alternativa XAMPP / `php -S localhost:8000 -t src` |
| Control de versiones | Git + GitHub                                                   |
| Pruebas              | Comprobación manual con los «Hecho cuando» de `specs/spec001-mvp/tasks.md` |

Comandos útiles (Docker, entorno principal):
- Levantar la app: `docker compose up -d` → `http://localhost:8000`
- Parar: `docker compose down`
- Resetear la base de datos: `docker compose down -v && docker compose up -d`
- Ver registros: `docker compose logs -f web`
- Alternativa sin Docker: `php -S localhost:8000 -t src` con XAMPP/MySQL local
- Tablas y datos: `sql/schema.sql` y `sql/datos.sql` (se importan solos al primer arranque del contenedor)

## 3. Estructura de código

```
constitution.md      reglas globales e innegociables
AGENTS.md            este archivo
Dockerfile           imagen PHP 8 + Apache (web)
docker-compose.yml   levanta web + MySQL 8 con un comando
specs/
  spec.md            QUÉ y POR QUÉ (requisitos en EARS, casos límite, dudas abiertas)
  plan.md            CÓMO técnico
  tasks.md           tareas con «Hecho cuando»
sql/
  schema.sql         tablas: usuarios, categorias, actividades, reservas
  datos.sql          4 categorías y 12 actividades
src/                 RAÍZ WEB (DocumentRoot): aquí vive todo el código servido
  includes/
    db.php           ÚNICO lugar que crea la conexión PDO
    auth.php         inicia sesión y redirige a login si no hay usuario (solo páginas privadas)
    cabecera.php     cabecera y navegación comunes
    pie.php          pie común
  css/  js/  img/
  login.php  registro.php  logout.php
  inicio.php  categoria.php  reserva.php
  mis_reservas.php  perfil.php  quienes_somos.php
```

Modelo de datos (relaciones 1:N):
- `categorias` 1 ── N `actividades`
- `usuarios` 1 ── N `reservas`
- `actividades` 1 ── N `reservas`

## 4. Reglas inquebrantables

Están en **`constitution.md`**. Léelas antes de empezar cualquier tarea.

## 5. Cómo trabajar (flujo obligatorio)

1. Abre `specs/spec001-mvp/tasks.md` y toma **la primera tarea sin marcar**.
2. Lee `constitution.md`, el apartado de `specs/spec001-mvp/spec.md` que cita la tarea y, si hace falta, `specs/spec001-mvp/plan.md`.
3. Implementa en pasos pequeños; no toques nada que no sea de la tarea.
4. Comprueba a mano los criterios «Hecho cuando» de la tarea, incluidos los casos de error.
5. Marca la tarea `[x]` y haz commit indicando qué requisitos (RF-xx) cumple.
6. Si falta información o algo no encaja, clasifica el caso:
   a) Está en la spec (RF, RNF, CL o duda cerrada): impleméntalo.
   b) Hay una duda abierta con valor por defecto: aplica ese valor, cita su ID
      (DA-xx) en el commit y avisa al terminar la tarea.
   c) No está en ninguna parte: PARA esa parte. Añade una duda nueva en
      `specs/spec001-mvp/spec.md` §3.3 con tu propuesta de valor por defecto y pregunta. Continúa
      solo con lo que no depende de ella.
   d) La spec parece errónea, incompleta o contradice la constitución: PARA. No
      corrijas el código por tu cuenta: propón el cambio en la spec, espera
      aprobación y actualiza después spec → plan → tasks → código.
   Solo el equipo cierra una duda: la IA propone, no decide. Los detalles internos
   que no cambian lo que ve el usuario (nombres de variables, estructura de
   funciones) los decides tú.

## 6. Qué leer según la tarea

| Si tocas…                              | Lee                                                                     |
| -------------------------------------- | ----------------------------------------------------------------------- |
| Registro, login, sesión, cerrar sesión | `specs/spec001-mvp/spec.md` (Acceso, RF-01 a RF-08), `src/includes/auth.php`            |
| Inicio y categorías                    | `specs/spec001-mvp/spec.md` (Inicio y actividades, RF-09 y RF-10), `sql/schema.sql` |
| Reservas                               | `specs/spec001-mvp/spec.md` (Reserva, RF-11 a RF-15) y casos límite                 |
| Mis reservas, perfil, quiénes somos    | `specs/spec001-mvp/spec.md` (Otras páginas, RF-16 a RF-20)                          |
| Tablas y datos                         | `sql/schema.sql`, `sql/datos.sql`                                       |
| Estructura y módulos                   | `specs/spec001-mvp/plan.md`                                                |
| Docker y entorno                       | §8 «Docker» de este archivo y `docker-compose.yml`                      |
| Estilo y accesibilidad                 | `constitution.md` (regla 10) y §8 de este archivo                       |

## 7. Definición de hecho (cualquier tarea)

- Cumple los RF de la tarea y su «Hecho cuando».
- Se han probado los casos de error de esos RF (campos vacíos, duplicados, día pasado, id inexistente).
- No aparecen errores ni avisos de PHP en pantalla.
- La página privada redirige a `login.php` si no hay sesión.
- Consultas con PDO preparado y salida con `htmlspecialchars()`.
- Textos en español y se ve bien en móvil y escritorio.

## 8. Reglas por área

### PHP y seguridad (`src/**/*.php`, `src/includes/`)
- Cada formulario valida en servidor: campos vacíos, longitud y formato, aunque JavaScript ya lo haya comprobado.
- Usa `$_SESSION['usuario_id']` para saber quién es el usuario; nunca un `usuario_id` recibido por URL o formulario.
- Si un `id` de la URL no existe (`reserva.php?id=999`), muestra un mensaje claro y no un error técnico.
- Después de procesar un formulario con éxito, redirige con `header('Location: ...')` para evitar reenvíos.
- Desactiva la visualización de errores técnicos en la versión final.

### Base de datos (`sql/`)
- Nombres en minúsculas y `snake_case`, sin `ñ` ni acentos (`creado_en`, `categoria_id`).
- Claves primarias `INT AUTO_INCREMENT`; relaciones con `FOREIGN KEY`.
- Las restricciones importantes (`UNIQUE`, `NOT NULL`, claves foráneas) van en la base de datos, no solo en PHP.
- Consulta `sql/schema.sql` antes de añadir o cambiar una tabla; cualquier cambio queda anotado en `specs/spec001-mvp/plan.md`.
- Para listados con datos de varias tablas (por ejemplo, mis reservas) usa `JOIN`.

### UI accesible (HTML, CSS, JS)
- Texto base 18 px, interlineado 1,5 y contraste alto.
- Botones y enlaces grandes (≥ 48 px) con separación entre ellos; foco visible al navegar con teclado.
- Etiquetas siempre visibles en los formularios (no solo placeholder); errores junto al campo y también un resumen arriba.
- Los mensajes de éxito y error se quedan escritos en la página; no usar `alert()` para información importante.
- Un solo botón principal por pantalla.
- Toda imagen lleva `alt`; usa encabezados en orden (`h1`, `h2`...) y un enlace «Saltar al contenido».
- Comprueba cada página nueva usando solo el teclado.

### Pruebas manuales
- Antes de dar una tarea por hecha, prueba el caso feliz y cada caso límite de su RF.
- Recorrido completo de control: registro → login → inicio → categoría → reservar → mis reservas → perfil → cerrar sesión.
- Usa solo los datos de ejemplo de `sql/datos.sql`; nunca datos reales de personas.

### Docker (`Dockerfile`, `docker-compose.yml`)
Docker es el entorno principal (RNF-06 y RNF-07 de la spec). La app debe levantarse con un solo comando y sin instalar PHP ni MySQL en la máquina.
- `Dockerfile`: imagen base `php:8.3-apache` + `docker-php-ext-install pdo_mysql`. Copia `src/` en `/var/www/html`, que es la raíz web (RNF-08).
- `docker-compose.yml`: dos servicios.
  - `web`: construye desde el `Dockerfile`, publica `8000:80`, monta el código en vivo (`./src:/var/www/html`, que es el DocumentRoot de Apache) y `depends_on: db` con `condition: service_healthy`.
  - `db`: imagen `mysql:8.4`, credenciales de desarrollo, volumen persistente (`db_data:/var/lib/mysql`) y `healthcheck` con `mysqladmin ping`.
- Variables de entorno del servicio `web`: `DB_HOST: db`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`.
- `src/includes/db.php` es el **único** sitio que abre la conexión: lee `DB_HOST`, `DB_NAME`, `DB_USER` y `DB_PASSWORD` con `getenv()`, con valores por defecto para XAMPP (`localhost`, `root`). Nunca escribir el host `localhost` a fuego.
- Importación automática: monta `./sql` en `/docker-entrypoint-initdb.d` para que `schema.sql` y `datos.sql` se carguen al crear el contenedor (RNF-07).
- `.dockerignore`: `.git`, `db_data` y cualquier cosa que no deba copiarse en la imagen.
- Puerto local: `http://localhost:8000`.
- Comandos: `docker compose up -d`, `docker compose down` y `docker compose down -v && docker compose up -d` para recrear la base de datos desde cero.

## 9. Prohibiciones rápidas

Ver la lista en `constitution.md`. Resumen: no concatenar SQL · no contraseñas en claro · no confiar en el cliente · no errores técnicos a la vista · no librerías ni frameworks nuevos.

## 10. Ahorro de tokens y cambio de IA

El estado del proyecto vive en **git + `specs/spec001-mvp/tasks.md`**, nunca en la memoria de un chat.

1. **Una tarea por sesión.** Lee este archivo, `constitution.md`, la tarea y solo el apartado de la spec que cita.
2. **Antes de agotar el contexto o cambiar de herramienta:** haz commit, marca la tarea si está terminada y, si queda a medias, añade bajo ella una línea `  - Notas: qué está hecho, qué falta, archivos tocados`.
3. **Al empezar con otra IA:** que lea `AGENTS.md`, `constitution.md`, `specs/spec001-mvp/tasks.md` (primera tarea sin marcar y sus notas) y `git log -5`.
4. **IA sin acceso a archivos** (chat web): pega `constitution.md`, `AGENTS.md`, la tarea y el trozo de `specs/spec001-mvp/spec.md` que toca.
5. No cambies reglas, stack ni estructura por iniciativa propia: propón el cambio y espera aprobación.