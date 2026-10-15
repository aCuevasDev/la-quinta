# 01 · Producto, alcance y roles

## Propósito
Combatir la soledad no deseada con un catálogo de actividades presenciales en cuatro pilares: **Salud y Deporte**, **Cultura y Memoria**, **Nuevas Tecnologías**, **Ocio y Relación**. Los usuarios exploran, ven disponibilidad en tiempo real y reservan con pago seguro. Los admins gestionan; los guías pasan lista en campo.

## Usuarios
- **Persona mayor (USER):** poca soltura digital; puede no tener email.
- **Familiar o cuidador (USER):** reserva para otra persona.
- **Guía (GUIDE):** monitor en campo, móvil, cobertura variable.
- **Admin (ADMIN):** backoffice, soporte telefónico, finanzas.

## Principios rectores (si chocan, gana el de número menor)
1. Seguridad de las personas y de sus datos de salud.
2. Accesibilidad y claridad: reservar sin ayuda, o con una llamada.
3. El teléfono es ciudadano de primera: el email nunca es obligatorio.
4. Dinero exacto e idempotente.
5. Datos mínimos y efímeros.
6. Operable: auditoría, colas con reintentos, monitorización.
7. Lenguaje llano en ES y CA.

## Alcance del MVP
- Cuentas: email+contraseña, OTP por email o SMS, Google; cuentas solo con teléfono creadas por admin; perfil con emergencia y salud; preferencias de accesibilidad.
- Catálogo: filtros (fecha, categoría, texto, accesibilidad), paginación por URL, ficha con punto de encuentro con foto y datos de accesibilidad.
- Disponibilidad: lista de «Próximas fechas» en tarjetas grandes (principal) y calendario mensual (secundario).
- Reservas: hasta 4 plazas (configurable), datos por asistente, reserva para otra persona, declaración de aptitud.
- Pagos: Stripe Checkout con reserva temporal de 30 min; flujo gratuito; reserva manual por admin.
- Cancelaciones: hasta 24 h antes, reembolso automático, cancelación parcial (quitar persona), cancelación masiva, reprogramación.
- Lista de espera y mínimo de participantes.
- Panel de guía (PWA offline), panel de admin, perfil de usuario.
- Notificaciones por email y SMS, recordatorios 48 h y 24 h, broadcast por cola.
- Soporte: botón de ayuda con teléfono visible, horario y «te llamamos nosotros».
- RGPD completo.

## Fuera del MVP
App nativa, chat, WhatsApp (post-MVP), otros idiomas además de ES/CA, multi-organización, facturación electrónica oficial (el MVP genera recibo PDF), cuotas o bonos.

## Roles
Tres roles: `USER`, `GUIDE`, `ADMIN`. Una persona puede tener varios (`user_role`).

| Acción | Anónimo | USER | GUIDE | ADMIN |
|---|---|---|---|---|
| Ver catálogo, fichas y fechas | Sí | Sí | Sí | Sí |
| Reservar y pagar | No | Sí | Sí (como usuario) | Sí, y reservas manuales en nombre de otros |
| Ver y cancelar reservas | No | Solo las suyas | Solo las suyas | Todas |
| Ver datos de salud y emergencia | No | Solo los suyos | Solo de sus sesiones asignadas y en la ventana temporal | Solo con motivo registrado; auditado |
| Check-in | No | No | Solo en sus sesiones | Sí, como corrección auditada |
| Registrar incidencias | No | No | Solo en sus sesiones | Sí |
| Avisos rápidos con plantilla | No | No | Solo a su sesión en curso | Sí |
| Gestionar actividades y sesiones | No | No | No | Sí |
| Reembolsos manuales | No | Solo vía cancelación propia | No | Sí, con reautenticación |
| Moderar reseñas | No | No | No | Sí |
| Gestionar usuarios, roles y guías | No | No | No | Sí |
| Broadcast | No | No | No | Sí |
| Ver auditoría | No | No | No | Solo lectura |

## Reglas inquebrantables de roles
1. El registro público **siempre** crea `USER`; se ignora cualquier campo de rol del cliente.
2. Guías y admins se crean **solo por invitación**; nunca se envían credenciales.
3. Debe existir siempre al menos un `ADMIN` activo (recomendado dos); un admin no puede quitarse el último rol.
4. La autorización real vive en el **DAL**: sesión + rol + propiedad. El `proxy.ts` es solo cortesía (mitiga bypass tipo CVE-2025-29927).
5. Cada Server Action es un endpoint POST público: validar con Zod, rate limit y permisos aunque la UI oculte el botón.
6. Cambiar roles, suspender o forzar cierre de sesión **destruye las sesiones al instante**.
7. Recurso ajeno o sin permiso → respuesta **404** (no revelar existencia).
