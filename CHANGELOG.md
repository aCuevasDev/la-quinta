spec -> specs/spec (añadida carpeta por si se modifica la spec o se crean otras (se podrían separar specs por funcionalidades en un futuro)

## Reducido a sólo una BD, quitado el completar de entorno local

| Capa                 | Decisión                                                       |
| -------------------- | -------------------------------------------------------------- |
| Servidor             | PHP 8 sin framework                                            |
| Base de datos        | **MySQL  (relacional)**                                        |
| Acceso a datos       | PDO con consultas preparadas                                   |
| Front                | HTML5, CSS y JavaScript sin librerías                          |
| Sesiones             | Sesiones de PHP (`$_SESSION`)                                  |
| Entorno local        | **XAMPP / Docker / `php -S localhost:8000`**                   |
| Control de versiones | Git + GitHub                                                   |
| Pruebas              | Comprobación manual con los «Hecho cuando» de `specs/spec001-mvp/tasks.md` |
## Le añadimos tests al apartado Pruebas?


## Añadidos RF-13, CL-11

## constitution.md regla 2:
2. **Autorización en cada página privada.** `inicio`, `categoria`, `reserva`,
   `mis_reservas`, `perfil` y `quienes_somos` incluyen `includes/auth.php` en la
   primera línea. Sin sesión → redirige a `login.php`. **Las páginas públicas
   (`login`, `registro`, `logout`) no lo incluyen y llaman a `session_start()`.**
## specs/spec001-mvp/plan.md :
- `auth.php`: inicia la sesión PHP y, si no existe `$_SESSION['usuario_id']`,
  redirige a `login.php`. Se incluye en la primera línea de cada página privada
  y nunca en `login.php`, `registro.php` ni `logout.php`, que llaman a
  `session_start()` por su cuenta (si no, se redirigirían a sí mismas en bucle).
## agents.md
  - auth.php           inicia sesión y redirige a login si no hay usuario (solo páginas privadas)
  
## specs/spec001-mvp/tasks.md 
- solo lo incluyen las páginas privadas (lo mismo de antes) F2-01
  

Como estaba escrito antes el agent interpretaba un loop de redirecciones infinito: `login->registro->auth->login`

### agents.md
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
## constitution.md
 **La spec es la única fuente de verdad.** Todo comportamiento visible debe salir de un RF, RNF o CL de `specs/spec001-mvp/spec.md`. Lo que no está en la spec no se implementa..
 
Estas dos normas junto creaban confusión. esta le decía a la IA que si no sabía algo lo pusiera en dudas abiertas y luego la de constitution le decía que si no sabía algo escribiera todo(decision) y lo buscase en dudas. Por lo que he leído las dudas deberían ir siempre en la spec no en el código y si hace falta se puede proponer modificar la spec 

## Cerradas las dudas abiertas (he dejado un par por responder)
- Modificado RF-04,05 y añadido 21 para poder cerrar las dudas:
  **RF-21:** SI el nombre, los apellidos o la contraseña no cumplen esos límites, ENTONCES EL SISTEMA indicará cuál falla y qué se espera, y no creará la cuenta.