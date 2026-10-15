# 07 · Cancelaciones, reembolsos, cambios de sesión y reseñas

## Regla de las 24 horas
El usuario puede cancelar si `now < starts_at − 24 horas reales`. Se comparan **instantes (UTC)** y se muestra en `Europe/Madrid`; **no** se resta «un día de calendario» para que los cambios de hora (25-oct-2026 y 28-mar-2027) no alteren la regla. Si no se puede, el botón se sustituye por texto explicativo y el teléfono de ayuda (`E-CANCEL-001`).

## Opciones del usuario (`/perfil/reservas`)
- **Cancelar toda la reserva:** reembolso del 100 % de lo pagado.
- **Quitar a una persona** (si `seats > 1`): se elimina un asistente (sus datos se borran de inmediato), `seats − 1`, se libera la plaza y se reembolsa `unit_price_cents`. Se puede quitar a cualquiera si **queda al menos una persona**; si se quita al titular, la cuenta que paga sigue siendo la gestora de la reserva.

El checkout explica estas opciones antes de pagar. Antes de confirmar, una pantalla grande resume qué se cancela, cuánto se devuelve y en qué plazo («Entre 5 y 10 días laborables») con dos botones: «Sí, cancelar» y «No, mantener mi reserva».

## Procedimiento de cancelación
1. **Transacción:** `CONFIRMED → CANCELLED` (condicional; el doble clic no duplica), libera `reserved_seats`, crea `refund` en `PENDING` con `idempotency_key = refund:{booking_id}:{operación}` y pone `booking.refund_status = PENDING`.
2. **Job Inngest** llama a `stripe.refunds.create` con esa clave. El webhook `refund.updated` cierra el ciclo (`SUCCEEDED`/`FAILED`).
3. **Gratuitas:** `refund_status = NOT_REQUIRED`. **`MANUAL_ADMIN`:** se crea tarea de reembolso manual en `/admin/pagos`; el admin la marca como devuelta con su referencia.
4. Tras cancelar: procesar lista de espera y enviar confirmación (SMS/email y contacto de aviso).
5. **Si Stripe rechaza:** hasta 5 reintentos con backoff; luego `FAILED`, alerta al admin y mensaje al usuario («Estamos revisando su reembolso; le llamaremos» · `E-REFUND-001`). Aparece en la bandeja «Reembolsos con problemas»; nunca queda en un limbo invisible.

## Reprogramación por el admin
Si cambia `starts_at` de una sesión con reservas: mostrar cuántas personas se ven afectadas y pedir confirmación. Guardar `rescheduled_at` y `previous_starts_at`. Efectos:
- Aviso inmediato por email y SMS con la nueva fecha.
- Durante **14 días o hasta el nuevo inicio** (lo que ocurra antes) el usuario puede **cancelar sin la restricción de 24 h y con reembolso del 100 %** (`SESSION_RESCHEDULED`).
- Se reinician los recordatorios.
- Bajar la capacidad por debajo de `reserved_seats` está bloqueado.

## Cancelación de una sesión completa por el admin
Requiere motivo, reautenticación y confirmación con el recuento de reservas y el importe total a devolver.
- La sesión pasa a `CANCELLED` **al instante** (nadie más reserva).
- Un job Inngest procesa **cada reserva de forma independiente** (transición, reembolso, aviso), con reintentos y sin depender del límite de tiempo de una función.
- Las `PENDING_PAYMENT` se expiran y sus sesiones de Stripe se cierran. Se vacía la lista de espera con aviso.
- El admin ve progreso, fallos por reserva y puede **relanzar solo las fallidas**.

## Guía no disponible
El admin reasigna desde `/admin/sesiones/[id]`. Se avisa al guía anterior y al nuevo; el acceso del anterior se revoca de inmediato. Si falta sustituto a < 24 h, alerta al admin.

## Reseñas verificadas
- Solo quien tenga `booking` `CONFIRMED`, con su `booking_attendee` titular `attended = true`, de una sesión `COMPLETED`, dentro de los **30 días** siguientes. Cancelada, expirada o sin asistencia **no** puede reseñar.
- Una reseña por usuario y actividad; se guardan `session_id` y `booking_id`.
- Texto plano (sin HTML), máx. 1000 caracteres, sanitizado. Estado inicial `PENDING`; el admin publica o rechaza con motivo.
- Se muestra solo `display_name` y fecha. Si la cuenta se anonimiza, se muestra «Usuario de La Quinta».
- Recordatorio único 24 h tras la actividad (transaccional, sin marketing).
