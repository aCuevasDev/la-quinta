# La Quinta

Web para que personas mayores reserven actividades presenciales en cuatro categorías:

- Ocio y relación
- Nuevas tecnologías
- Salud y deporte
- Cultura y memoria

Cada categoría tiene 3 actividades. La persona usuaria se registra, inicia sesión, elige una actividad y reserva el día que mejor le venga.

Proyecto desarrollado con **Spec-Driven Development (SDD)**: primero se acuerda por escrito qué debe hacer la web y después se construye siguiendo ese contrato.

## Funcionalidades

- Registro e inicio de sesión (nombre, apellidos y contraseña).
- Página de inicio con las 4 categorías.
- Página de cada categoría con sus 3 actividades.
- Reserva de una actividad eligiendo el día.
- Listado de «Mis reservas».
- Perfil de usuario y página «Quiénes somos».

## Tecnología

| Capa | Tecnología |
|---|---|
| Servidor | PHP 8 (sin framework) |
| Base de datos | MySQL / MariaDB |
| Acceso a datos | PDO con consultas preparadas |
| Front | HTML, CSS y JavaScript |
| Control de versiones | Git y GitHub |

## Cómo arrancar el proyecto en local

### 1. Requisitos

- PHP 8 o superior
- MySQL o MariaDB
- Un entorno local: Docker

### 2. Obtener el código

```bash
git clone [completar: URL del repositorio]
cd la-quinta
```

### 3. Crear la base de datos

1. Crea una base de datos vacía, por ejemplo `la_quinta`.
2. Importa las tablas y después los datos de ejemplo:

```bash
mysql -u root -p la_quinta < sql/schema.sql
mysql -u root -p la_quinta < sql/datos.sql
```

### 4. Configurar la conexión

Edita `src/includes/db.php` con los datos de tu servidor MySQL (host, base de datos, usuario y contraseña).

### 5. Arrancar la web

Con el servidor integrado de PHP:

```bash
php -S localhost:8000 -t src
```

Abre `http://localhost:8000/login.php` en el navegador. Si usas XAMPP o Docker, abre la ruta que corresponda a tu entorno.

## Estructura del proyecto

```
la-quinta/
├── constitution.md      reglas globales e innegociables
├── AGENTS.md            contexto y flujo de trabajo para la IA
├── README.md            este archivo
├── Dockerfile           imagen PHP 8 + Apache (web)
├── docker-compose.yml   levanta web + MySQL 8 con un comando
├── specs/
│   ├── spec.md          QUÉ y POR QUÉ (requisitos RF-xx en EARS)
│   ├── plan.md          CÓMO técnico
│   └── tasks.md         tareas con «Hecho cuando»
├── sql/
│   ├── schema.sql       tablas
│   └── datos.sql        4 categorías y 12 actividades
├── src/                 RAÍZ WEB (DocumentRoot)
│   ├── includes/
│   │   ├── db.php       conexión PDO
│   │   ├── auth.php     comprobación de sesión
│   │   ├── cabecera.php
│   │   └── pie.php
│   ├── css/  js/  img/
│   ├── login.php  registro.php  logout.php
│   ├── inicio.php  categoria.php  reserva.php
│   └── mis_reservas.php  perfil.php  quienes_somos.php
```

## Cómo se trabaja (SDD)

El flujo sigue estos pasos:

1. **Constitución:** reglas globales en `constitution.md`.
2. **Especificación:** requisitos en `specs/spec001-mvp/spec.md`.
3. **Clarificación:** se cierran las dudas abiertas de la spec.
4. **Plan técnico:** `specs/spec001-mvp/plan.md`, revisado antes de programar.
5. **Tareas:** `specs/spec001-mvp/tasks.md`, con «Hecho cuando» para cada una.
6. **Implementación y validación:** una tarea cada vez y comprobación contra la spec.

Si durante la implementación aparece una decisión que no está acordada, se **para**, se actualiza `specs/spec001-mvp/spec.md` y después se continúa.

### Documentos clave

| Archivo | Para qué sirve |
|---|---|
| [`constitution.md`](constitution.md) | Reglas que nunca se rompen |
| [`AGENTS.md`](AGENTS.md) | Qué leer y cómo trabajar (personas o IA) |
| [`specs/spec001-mvp/spec.md`](specs/spec001-mvp/spec.md) | Contrato de la web y tabla de validación |
| [`specs/spec001-mvp/plan.md`](specs/spec001-mvp/plan.md) | Arquitectura, datos y fases |
| [`specs/spec001-mvp/tasks.md`](specs/spec001-mvp/tasks.md) | Lista de tareas por fases |

## Estado del proyecto

Se sigue `specs/spec001-mvp/tasks.md`. Las tareas completadas están marcadas con `[x]` y la fase activa es la primera con tareas sin marcar.

## Fuera de alcance de esta versión

Pagos online, roles de guía y administrador, cancelar reservas, límite de plazas, recuperar contraseña, verificación por email o teléfono, varios idiomas y app instalable. Quedan para una versión futura.

## Autoría

[completar: nombres, curso y centro]