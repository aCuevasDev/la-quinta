# 08 · Panel de guías, panel de administración y perfil

## Panel de guías (PWA de campo)
**Rutas:** `/guia` (sesiones asignadas de hoy y próximos 7 días) y `/guia/sesion/[id]`. El DAL solo devuelve sesiones con `guide_id = actor.id`; cualquier otro `id` → 404.

- **Ficha de asistente** (tarjeta grande, botones 56 px, texto 20 px): nombre, teléfono con «Llamar», contacto de emergencia con «Llamar», indicador «Tiene observaciones de salud» y casilla grande **Asistió / No vino por persona** (no por reserva).
- **Ventana temporal de datos sensibles:** salud y teléfonos solo desde **24 h antes del inicio hasta 2 h después del final**. Fuera: solo nombre y asistencia. Cada apertura de observaciones de salud se registra en `audit_log` (quién, a quién, cuándo; sin contenido).
- **Check-in:** desde 30 min antes del inicio hasta 24 h después del final; deshacer durante 2 h; después solo un admin (auditado). Un tap guarda y muestra confirmación persistente.
- **Modo sin conexión:** al abrir la sesión con red, la PWA guarda en IndexedDB nombres, teléfonos y contactos de emergencia hasta 2 h después del final de la ventana, y los borra al cerrar sesión. **La salud no se guarda offline** («Requiere conexión»). Los check-ins sin red se encolan con su hora y se sincronizan; si un admin modificó lo mismo, prevalece el más reciente y se registra el conflicto. Riesgo residual (móvil robado) a validar en la DPIA.
- **Emergencias:** botón rojo siempre visible «Emergencia» con «Llamar al 112» y «Llamar al contacto de emergencia de [persona]» (funcionan sin datos). Después, formulario breve de `incident` (tipo, descripción **sin datos de salud**, si se llamó al 112). Aviso inmediato al admin por SMS y email.
- **Avisos rápidos:** plantillas fijas («Llego con 10 minutos de retraso», «Cambio de punto de encuentro») por SMS a los asistentes de su sesión en curso; se registran en `notification_log`.
- **Restricciones:** no ve importes ni pagos ni otras sesiones; no exporta listas; `Cache-Control: no-store`.

## Panel de administración
Solo `ADMIN`, TOTP y sesión de 8 h. Toda acción destructiva o financiera exige **reautenticación reciente** y confirmación, y queda en `audit_log`.

- **Dashboard `/admin`:** ocupación próxima, reservas del día, ingresos, reembolsos pendientes. **Alertas accionables:** webhooks fallidos, reembolsos fallidos, sesiones sin guía a < 72 h, sesiones bajo el mínimo, incidencias abiertas, llamadas pendientes, jobs con error.
- **Actividades:** CRUD con ES/CA, borrador/publicada/archivada, **imagen con alt obligatorio**, accesibilidad y punto de encuentro con foto. No se borra una actividad con sesiones futuras con reservas.
- **Sesiones:** crear una o en lote (recurrentes), asignar guía (avisa de solapes), ajustar capacidad (bloqueado bajo `reserved_seats`), mínimo, reprogramar, cancelar y reasignar guía.
- **Reservas `/admin/reservas`:** buscador (nombre, teléfono, email, ID), filtros, ficha con línea temporal (creada, pagada, cancelada, reembolsada, avisos). Acciones: crear manual, cancelar, reembolsar manualmente, reenviar confirmación, corregir asistencia. Salud tras «Ver datos de salud» con **motivo obligatorio** y auditoría.
- **Pagos `/admin/pagos`:** listado conciliado con Stripe, reembolsos pendientes y fallidos, tareas de reembolso manual, disputas, CSV (sin salud, con reautenticación y registro).
- **Usuarios:** buscar, crear cuenta solo con teléfono, suspender, cambiar contacto verificado, restablecer acceso, tramitar solicitudes RGPD, ver consentimientos. Nunca se ve una contraseña.
- **Guías:** invitar, revocar, agenda y sesiones asignadas.
- **Reseñas:** moderación con motivo de rechazo.
- **Broadcast:** ver `docs/09`.
- **Incidencias y llamadas:** `incident` y `support_callback` con estado y responsable.
- **Auditoría:** solo lectura, filtros por actor, acción y entidad, exportable.
- **Ajustes:** horas de cancelación, cierre de reservas, teléfono y horario de soporte, textos legales y versiones.

## Perfil de usuario
- **`/perfil`:** resumen con la **próxima actividad** destacada y accesos rápidos (ver reserva, llamar a ayuda).
- **`/perfil/reservas`:** pestañas Próximas, Pasadas y Canceladas. Cada reserva: estado en texto claro, «Añadir al calendario» (`.ics`), «Imprimir o descargar recibo», «Cancelar» o «Quitar a una persona» (con la regla explicada) y estado del reembolso.
- **`/perfil/datos`:** datos personales, emergencia y salud (con consentimiento y fecha de última revisión), cambio de email/teléfono con verificación, idioma y **preferencias de accesibilidad** guardadas en la cuenta.
- **`/perfil/seguridad`:** contraseña, sesiones y dispositivos, cerrar todas.
- **`/perfil/privacidad`:** ver y retirar consentimientos (incluido marketing), **descargar mis datos** (JSON y PDF legible, enlace firmado 24 h) y **borrar mi cuenta**.
- **Borrar cuenta:** confirmación con OTP. Se bloquea con mensaje claro si hay reservas `CONFIRMED` o `PENDING_PAYMENT` futuras o reembolsos en curso. Si no, se anonimiza (`docs/10`) y se cierra la sesión.
- Cada 12 meses se pide confirmar o borrar las observaciones de salud.
