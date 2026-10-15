# 09 · Notificaciones y trabajos programados

## Catálogo
| Mensaje | Canal | Tipo |
|---|---|---|
| Código de acceso (OTP) | Email o SMS según contacto | Transaccional |
| Invitación de guía o admin | Email o SMS | Transaccional |
| Reserva confirmada (con punto de encuentro) | Email + SMS | Transaccional |
| Recordatorio 48 h y 24 h antes | Email + SMS | Transaccional |
| Cancelación, reembolso emitido o fallido | Email + SMS | Transaccional |
| Sesión reprogramada o cancelada | Email + SMS (prioridad alta) | Transaccional |
| Oferta de lista de espera | SMS + email | Transaccional |
| Invitación a valorar | Email | Transaccional |
| Cambio de contacto o seguridad | Al contacto anterior | Transaccional |
| Incidencia en campo | SMS + email al admin | Operativo |
| Novedades y promociones | Email, solo con consentimiento | Marketing |

## Reglas
- Plantillas en ES y CA, frases cortas. **SMS:** los acentos fuerzan Unicode (70 caracteres por segmento); redactar con juego de caracteres GSM-7 siempre que se pueda o presupuestar varios segmentos. Incluir el teléfono de ayuda cuando proceda.
- Avisos críticos (reprogramación, cancelación, oferta) por **email y SMS**; si un canal falla, 3 reintentos con backoff y se usa el otro como respaldo.
- Sin email → todo por SMS. Con `notify_contact_*` → ese contacto recibe copia.
- **Nunca datos de salud en un mensaje.**
- Marketing desactivado por defecto, con consentimiento aparte, enlace de baja y `List-Unsubscribe`. Nunca se mezcla con transaccional.
- Todo envío queda en `notification_log` (destinatario enmascarado); los fallos alertan.
- Límite de SMS por usuario y día (coste y abuso). Solo `+34` por defecto.
- Email con SPF, DKIM y DMARC `p=quarantine`; remitente fijo reconocible.

## Broadcast (`/admin/broadcast`)
Admin elige sesión, canal (email, SMS o ambos) y plantilla o texto libre. Antes de enviar ve **vista previa, número de destinatarios y coste de SMS** y confirma. Se procesa en cola, con progreso (enviados/fallidos), reintentos y relanzamiento solo de fallidos. Urgentes (p. ej. clima) por SMS. Siempre comunicación operativa de la actividad contratada, nunca marketing.

## Trabajos (Inngest)
Todos **idempotentes**, con reintentos y backoff, control de concurrencia (nunca dos instancias del mismo lote), registro de cada ejecución y alerta si fallan tras agotar reintentos.

| Trabajo | Frecuencia | Qué hace |
|---|---|---|
| `complete-sessions` | 15 min | Pasa a `COMPLETED` sesiones terminadas y fija `completed_at` |
| `reconcile-pending-payments` | 5 min | Consulta a Stripe las `PENDING_PAYMENT` vencidas |
| `reminders` | 1 h | Recordatorios 48 h y 24 h; único por reserva y plantilla |
| `min-participants-check` | 1 h | Cancela sesiones sin mínimo en `confirm_deadline_at` |
| `min-participants-warning` | Diario | Avisa al admin 48 h antes si faltan inscritos |
| `waitlist-offers` | Evento + 5 min | Ofrece (reteniendo plazas) y caduca ofertas vencidas |
| `process-refund` | Evento + reintentos | Ejecuta reembolsos en Stripe con clave de idempotencia |
| `cancel-session-fanout` | Evento | Cancela y reembolsa cada reserva de una sesión |
| `send-broadcast` | Evento | Envía avisos masivos por lotes |
| `scrub-sensitive-data` | 1 h | Limpia salud y emergencia 7 días tras la sesión |
| `anonymize-user` | Evento | Ejecuta una anonimización solicitada |
| `seat-drift-audit` | Diario | Recalcula y corrige `reserved_seats` |
| `purge-expired` | Diario | Borra sesiones, OTP y logs caducados |
| `review-invites` | 1 h | Invitación a valorar 24 h después |
| `unassigned-guide-alert` | Diario | Sesiones a < 72 h sin guía |
| `health-data-review` | Mensual | Pide confirmar salud al cumplirse 12 meses |
| `rotate-keys` | Anual (manual) | Recifra datos con la nueva clave |
