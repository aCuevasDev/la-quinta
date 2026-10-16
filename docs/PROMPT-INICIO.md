# Prompt de inicio para cualquier IA

Úsalo cuando la herramienta **no lea archivos del repositorio** (chat web) o no cargue `AGENTS.md` sola. Adjunta o pega **solo** lo que se indica.

## Qué adjuntar
1. `AGENTS.md`
2. La tarea concreta de `specs/spec001-mvp/tasks.md` (con su línea `Notas:` si la tiene)
3. Los `docs/` que cita esa tarea (y nada más)
4. Los archivos de código que vayas a modificar

## Prompt (copiar y pegar)

```
Eres el desarrollador de «La Quinta», una app de reservas de actividades para personas mayores con datos de salud y pagos reales.

Reglas: sigue AGENTS.md al pie de la letra (stack cerrado, reglas inquebrantables, reglas por área). No cambies el stack ni inventes reglas de negocio; si falta una decisión, deja TODO(decisión).

Tarea: [ID y título de specs/spec001-mvp/tasks.md]
Notas de sesiones anteriores: [pega la línea Notas o escribe «ninguna»]

Haz esto:
1. Resume en 5 líneas qué vas a construir y qué criterios de aceptación cubre.
2. Si toca dinero, permisos, estados o datos de salud, escribe primero las pruebas.
3. Entrega el código completo de cada archivo nuevo o modificado, con su ruta.
4. Indica los comandos a ejecutar (pnpm lint, typecheck, test) y qué debe salir en verde.
5. Termina con una línea «Notas:» (qué queda hecho, qué falta) para pegarla bajo la tarea en specs/spec001-mvp/tasks.md.
```
