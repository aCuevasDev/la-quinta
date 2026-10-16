# Spec · La Quinta

> Contrato de qué debe hacer la web. Los requisitos están numerados (RF-xx) y escritos en sintaxis EARS. Cualquier cambio de comportamiento se anota aquí **antes** de tocar el código. Reglas globales: `constitution.md`. Cómo se construye: `specs/spec001-mvp/plan.md` y `specs/spec001-mvp/tasks.md`.

---

## 1. Contexto

**Objetivo:** permitir que personas mayores se registren y reserven actividades presenciales en cuatro categorías: Ocio y relación, Nuevas tecnologías, Salud y deporte, y Cultura y memoria. Cada categoría tiene 3 actividades.

**Usuario:** persona mayor con poca soltura digital. Un único tipo de usuario: la persona registrada. Por eso la interfaz debe ser clara, con letra grande y mensajes sencillos.

**Historias de usuario:**
- Como persona usuaria, quiero registrarme para tener una cuenta.
- Como persona usuaria, quiero entrar con mi nombre, mis apellidos y mi contraseña para acceder a mis actividades.
- Como persona usuaria, quiero ver las actividades de cada categoría para elegir la que me interesa.
- Como persona usuaria, quiero reservar una actividad en el día que me venga bien.
- Como persona usuaria, quiero ver todas mis reservas en un solo lugar.
- Como persona usuaria, quiero ver mis datos y saber quién está detrás de La Quinta.

---

## 2. Contrato

### 2.1 Requisitos funcionales

#### Acceso
- **RF-01:** CUANDO el usuario abra `login.php`, EL SISTEMA mostrará un formulario con nombre, apellidos y contraseña, y un enlace a la página de registro.
- **RF-02:** CUANDO el usuario envíe credenciales correctas, EL SISTEMA iniciará su sesión y le llevará a `inicio.php`.
- **RF-03:** SI las credenciales son incorrectas, ENTONCES EL SISTEMA mostrará un mensaje de error sin indicar qué dato ha fallado y no iniciará sesión.
- **RF-04:** CUANDO el usuario envíe en `registro.php` un nombre de 2 a 50 caracteres, unos apellidos de 2 a 80 (solo letras, espacios, guion y apóstrofo) y una contraseña de 8 a 64 caracteres, EL SISTEMA creará la cuenta (con la contraseña cifrada) y le llevará a `login.php` con un mensaje de éxito.
- **RF-05:** SI ya existe un usuario con el mismo nombre y apellidos (sin distinguir mayúsculas, tildes ni espacios sobrantes), ENTONCES EL SISTEMA no creará la cuenta y mostrará un mensaje de error.
- **RF-06:** SI algún campo del formulario de login o de registro está vacío, ENTONCES EL SISTEMA indicará cuál falta y no procesará el formulario.
- **RF-07:** SI un usuario sin sesión abre una página privada, ENTONCES EL SISTEMA le redirigirá a `login.php`.
- **RF-08:** CUANDO el usuario pulse «Cerrar sesión», EL SISTEMA cerrará su sesión y le llevará a `login.php`.
- **RF-21:** SI el nombre, los apellidos o la contraseña no cumplen sus límites, ENTONCES EL SISTEMA indicará cuál falla y qué se espera, y no creará la cuenta.

#### Inicio y actividades
- **RF-09:** MIENTRAS el usuario tenga la sesión iniciada, EL SISTEMA mostrará en `inicio.php` las 4 categorías en tarjetas, cada una con su explicación breve y un botón «Ver detalles».
- **RF-10:** CUANDO el usuario pulse «Ver detalles», EL SISTEMA mostrará `categoria.php` con las 3 actividades de esa categoría, cada una con un botón «Reservar».

#### Reserva
- **RF-11:** CUANDO el usuario pulse «Reservar», EL SISTEMA mostrará `reserva.php` con la información completa de la actividad y un selector de día.
- **RF-12:** SI el usuario confirma la reserva sin elegir día, ENTONCES EL SISTEMA mostrará un error y no creará la reserva.
- **RF-13:** SI el día elegido es anterior a hoy o no es una fecha válida, ENTONCES EL SISTEMA mostrará un error y no creará la reserva.
- **RF-14:** SI el usuario ya tiene esa actividad reservada ese mismo día, ENTONCES EL SISTEMA no creará una reserva duplicada y mostrará un aviso.
- **RF-15:** CUANDO el usuario confirme una reserva válida, EL SISTEMA la guardará asociada a su cuenta y le llevará a `mis_reservas.php`.

#### Otras páginas
- **RF-16:** EL SISTEMA mostrará en `mis_reservas.php` únicamente las reservas del usuario con sesión iniciada, indicando actividad, categoría y día.
- **RF-17:** SI el usuario no tiene reservas, ENTONCES EL SISTEMA mostrará un mensaje indicándolo.
- **RF-18:** EL SISTEMA mostrará en `perfil.php` el nombre y los apellidos del usuario con sesión iniciada.
- **RF-19:** EL SISTEMA tendrá una página `quienes_somos.php` accesible desde la navegación.
- **RF-20:** EL SISTEMA mostrará una navegación común (inicio, mis reservas, perfil, quiénes somos, cerrar sesión) en todas las páginas privadas.

### 2.2 Requisitos no funcionales

- **RNF-01 (accesibilidad):** EL SISTEMA usará un texto base de al menos 18 px, buen contraste, botones de al menos 48 px y etiquetas siempre visibles en los formularios.
- **RNF-02 (adaptación):** EL SISTEMA se verá y funcionará correctamente en móvil y en escritorio.
- **RNF-03 (contraseña):** EL SISTEMA no mostrará la contraseña en pantalla mientras se escribe, salvo que el usuario lo pida expresamente.
- **RNF-04 (seguridad):** EL SISTEMA guardará las contraseñas cifradas, usará consultas preparadas y escapará todo texto introducido por el usuario antes de mostrarlo.
- **RNF-05 (mensajes):** EL SISTEMA mostrará todos los textos y errores en español, con frases cortas y lenguaje llano, y nunca mostrará errores técnicos de PHP o MySQL.
- **RNF-06 (ejecución con Docker):** EL SISTEMA incluirá un `Dockerfile` y un `docker-compose.yml` que levanten la web (PHP 8 + Apache) y su base de datos (MySQL 8) con un solo comando (`docker compose up -d`), sin instalar PHP ni MySQL en la máquina anfitriona, y quedará accesible en `http://localhost:8000`.
- **RNF-07 (base de datos reproducible):** AL arrancar el contenedor por primera vez, EL SISTEMA creará la base de datos e importará `sql/schema.sql` y `sql/datos.sql` automáticamente, de modo que `docker compose down -v && docker compose up -d` deje el sistema listo para usarse.
- **RNF-08 (estructura):** EL SISTEMA tendrá todo su código en la carpeta `src/` (páginas, `includes/`, `css/`, `js/` e `img/`), que será la raíz web (DocumentRoot). `sql/`, `specs/` y `docs/` quedarán fuera de la web. Las URLs no cambian (`http://localhost:8000/login.php`).

### 2.3 Casos límite

| ID    | Caso                                                                                              | Comportamiento esperado                                                |
| ----- | ------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------- |
| CL-01 | Nombre o apellidos con tildes, ñ o espacios                                                       | Se guardan, se muestran y permiten iniciar sesión correctamente        |
| CL-02 | Abrir una página privada escribiendo la URL sin sesión                                            | Redirige a `login.php` (RF-07)                                         |
| CL-03 | Reservar un día anterior a hoy, aunque se manipule el formulario                                  | La reserva se rechaza en PHP (RF-13)                                   |
| CL-04 | Reservar dos veces la misma actividad el mismo día                                                | Se avisa y no se duplica (RF-14)                                       |
| CL-05 | `categoria.php?id=` con un id que no existe o no es un número                                     | Mensaje claro y enlace para volver a inicio                            |
| CL-06 | `reserva.php?id=` con un id de actividad que no existe                                            | Mensaje claro y enlace para volver a inicio                            |
| CL-07 | Intentar ver reservas de otro usuario cambiando datos en la URL o en el formulario                | Nunca se muestran: se filtra siempre por la sesión (RF-16)             |
| CL-08 | Enviar un formulario con campos solo con espacios                                                 | Se tratan como vacíos (RF-06)                                          |
| CL-09 | Intentar registrarse con nombre y apellidos que ya existen, cambiando mayúsculas o minúsculas     | Se detecta como duplicado (RF-05)                                      |
| CL-10 | Cerrar la sesión y pulsar «Atrás» en el navegador                                                 | Al recargar o navegar, las páginas privadas vuelven a pedir login      |
| CL-11 | Enviar una fecha inexistente (31 de febrero) o con formato raro, aunque se manipule el formulario | Se rechaza en PHP con un mensaje claro y no se crea la reserva (RF-13) |

---

## 3. Límites

### 3.1 Fuera de alcance

Pagos online, roles de guía y administrador, cancelar o reprogramar reservas, límite de plazas o de personas por día, recuperar contraseña, verificación por email o teléfono, datos de salud, varios idiomas y app instalable (PWA).

### 3.2 Criterio de «Hecho»

La web está terminada cuando:
- Se puede recorrer el flujo completo: registro → login → inicio → categoría → reservar → mis reservas → perfil → cerrar sesión.
- Todos los requisitos RF-01 a RF-20 se cumplen y están marcados en la tabla de validación (apartado 5).
- Todos los casos límite CL-01 a CL-10 se comportan como indica la tabla.
- `docker compose up -d` levanta la web y la base de datos y el flujo completo se recorre en `http://localhost:8000` (RNF-06 y RNF-07).
- El código de la aplicación vive en `src/`, que es la raíz web, y `sql/`, `specs/` y `docs/` no son accesibles por HTTP (RNF-08).
- `specs/spec001-mvp/spec.md` y el código coinciden.

### 3.3 Dudas abiertas

Mientras no se cierren, se aplica el valor por defecto indicado. **La IA no debe asumir otra respuesta.**

| ID    | Duda                                                               | Valor por defecto                                                                                                 | Estado                                                                                                                                                                                     |
| ----- | ------------------------------------------------------------------ | ----------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| DA-01 | ¿El registro pide contraseña?                                      | Sí (el login la exige)                                                                                            | **Cerrada (15/10/2026):** sí; de 8 a 64 caracteres, sin reglas de complejidad. Ver RF-04 y RF-21                                                                                           |
| DA-02 | ¿Pueden existir dos personas con el mismo nombre y apellidos?      | No (`UNIQUE`); si pasa, hay que añadir otro identificador                                                         | **Cerrada (15/10/2026):** no. Se comparan sin distinguir mayúsculas, tildes ni espacios sobrantes (collation `utf8mb4_unicode_ci`). Limitación asumida (plan D4). Ver RF-05, CL-01 y CL-09 |
| DA-03 | ¿Se puede cancelar una reserva?                                    | No                                                                                                                | **Cerrada (15/10/2026):** no; fuera de alcance de esta versión                                                                                                                             |
| DA-04 | ¿Hay límite de personas por actividad y día?                       | No                                                                                                                | **Cerrada (15/10/2026):** no; fuera de alcance de esta versión                                                                                                                             |
| DA-05 | ¿Qué información lleva cada actividad (horario, lugar, precio)?    | Nombre, descripción y categoría                                                                                   | **Abierta.** Mientras tanto no se añaden horario, lugar ni precio. Conviene cerrarla antes de F1-01, porque cambia `schema.sql` y `datos.sql`                                              |
| DA-06 | ¿Qué datos se muestran en el perfil además del nombre y apellidos? | Solo nombre y apellidos                                                                                           | **Cerrada (15/10/2026):** solo nombre y apellidos (RF-18)                                                                                                                                  |
| DA-07 | ¿Qué se considera un nombre y apellidos válidos?                   | Nombre de 2 a 50 caracteres y apellidos de 2 a 80; solo letras (con tildes, ñ, ç, ü), espacios, guion y apóstrofo | **Cerrada (15/10/2026):** según el valor por defecto. Ver RF-04 y RF-21                                                                                                                    |
| DA-08 | ¿Hasta cuánto tiempo vista se puede reservar?                      | Sin límite superior (solo se rechazan los días anteriores a hoy y las fechas inexistentes)                        | **Abierta.** Por decidir                                                                                                                                                                   |

---

## 4. Trazabilidad con el plan y las tareas

| Requisito       | Tareas (`specs/spec001-mvp/tasks.md`)         |
| --------------- | --------------------------------- |
| RF-01           | F2-05                             |
| RF-02           | F2-06                             |
| RF-03           | F2-07                             |
| RF-04           | F2-02, F2-03, F2-04               |
| RF-05           | F2-03, F1-04                      |
| RF-06           | F2-02, F2-07                      |
| RF-07           | F2-01                             |
| RF-08           | F2-08                             |
| RF-09           | F3-01, F3-04                      |
| RF-10           | F3-02, F3-03                      |
| RF-11           | F4-01, F4-02                      |
| RF-12           | F4-03                             |
| RF-13           | F4-04                             |
| RF-14           | F4-05, F1-04                      |
| RF-15           | F4-06                             |
| RF-16           | F4-07, F4-09                      |
| RF-17           | F4-08                             |
| RF-18           | F5-01                             |
| RF-19           | F5-02                             |
| RF-20           | F0-05, F5-03                      |
| RNF-01 a RNF-05 | F0-04, F0-06, F6-01, F6-03, F6-04 |
| RNF-06, RNF-07  | F0-03, F1-02, F1-03               |
| RNF-08          | F0-02, F0-03                      |
| CL-01 a CL-11   | F6-02                             |

---

## 5. Tabla de validación contra la spec

Se rellena al final (tarea F6-05 y F6-07). Marca `[x]` cuando el requisito se haya comprobado a mano en la web.

| Requisito | Cómo se comprueba | Cumple |
|---|---|---|
| RF-01 | Abrir `login.php`: se ven los 3 campos y el enlace a registro | [ ] |
| RF-02 | Entrar con datos correctos lleva a `inicio.php` | [ ] |
| RF-03 | Entrar con una contraseña incorrecta muestra error genérico y no inicia sesión | [ ] |
| RF-04 | Registrar un usuario nuevo lo guarda con contraseña cifrada y lleva a login con aviso | [ ] |
| RF-05 | Registrar un nombre y apellidos ya existentes da error y no crea cuenta | [ ] |
| RF-06 | Enviar cada formulario con un campo vacío indica cuál falta | [ ] |
| RF-07 | Abrir `inicio.php` sin sesión redirige a `login.php` | [ ] |
| RF-08 | «Cerrar sesión» lleva a `login.php` y las páginas privadas dejan de abrirse | [ ] |
| RF-09 | `inicio.php` muestra las 4 tarjetas con explicación y «Ver detalles» | [ ] |
| RF-10 | «Ver detalles» muestra las 3 actividades de esa categoría con «Reservar» | [ ] |
| RF-11 | «Reservar» muestra información completa y selector de día | [ ] |
| RF-12 | Confirmar sin día muestra error y no crea reserva | [ ] |
| RF-13 | Elegir un día pasado muestra error y no crea reserva | [ ] |
| RF-14 | Reservar lo mismo el mismo día avisa y no duplica | [ ] |
| RF-15 | Una reserva válida se guarda y lleva a `mis_reservas.php` | [ ] |
| RF-16 | Con dos usuarios, cada uno ve solo sus reservas | [ ] |
| RF-17 | Un usuario nuevo ve el mensaje de «no tienes reservas» | [ ] |
| RF-18 | `perfil.php` muestra el nombre y apellidos correctos | [ ] |
| RF-19 | `quienes_somos.php` se abre desde el menú | [ ] |
| RF-20 | El menú es igual y funciona en todas las páginas privadas | [ ] |
| RNF-01 | Texto de al menos 18 px, botones grandes y etiquetas visibles | [ ] |
| RNF-02 | Se ve bien en móvil y escritorio | [ ] |
| RNF-03 | La contraseña se oculta al escribirla | [ ] |
| RNF-04 | Sin SQL concatenado, contraseñas cifradas y salida escapada (revisión de código) | [ ] |
| RNF-05 | Todos los textos en español y sin errores técnicos a la vista | [ ] |
| RNF-06 | `docker compose up -d` levanta web y base de datos y se abre `http://localhost:8000` | [ ] |
| RNF-07 | Tras `docker compose down -v && docker compose up -d`, la base de datos tiene las 4 tablas y las 12 actividades | [ ] |
| RNF-08 | El código está en `src/`, las páginas se sirven en `http://localhost:8000/login.php` y `sql/`, `specs/` y `docs/` dan error por HTTP | [ ] |
| CL-01 a CL-10 | Cada caso límite se prueba y se comporta como indica la tabla del apartado 2.3 | [ ] |