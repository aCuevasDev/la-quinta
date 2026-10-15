# 06 · Catálogo, reservas y pagos

## Catálogo y disponibilidad
- Filtros: fecha, categoría, texto y accesibilidad (movilidad reducida, sillas, sin escaleras). **Todo el estado vive en la URL** (`?page=2&cat=…`) para que «Atrás» funcione siempre.
- Disponibilidad calculada en servidor: `capacity - reserved_seats`. Estados siempre con **icono + texto + color**:
  - Verde «Hay plazas» (> 30 % libre) · Naranja «Quedan pocas plazas» (≤ 30 %) · Rojo «Completa» (con «Apuntarme a la lista de espera») · Gris tachado «Cancelada».
- Vista principal: **tarjetas grandes** («Martes 14 de octubre, 10:00 · Quedan 3 plazas»); calendario mensual secundario.
- Las reservas se cierran 2 h antes del inicio (`booking_cutoff_hours`).

## Checkout guiado (un paso por pantalla)
1. **Fecha y plazas:** máx. `max_seats_per_booking`; muestra el precio total.
2. **Asistentes:** por plaza, nombre, teléfono y contacto de emergencia obligatorios; «observaciones de salud» obligatoria (puede ser «Ninguna»). Si el asistente no es quien reserva: casilla «Tengo permiso de esta persona para dar sus datos» (`THIRD_PARTY_DATA`).
3. **Condiciones:** declaración de aptitud, consentimiento `HEALTH_DATA`, términos y política de cancelación en lenguaje llano (regla de 24 h, cómo se reembolsa, **sin desistimiento de 14 días** en ocio con fecha fija).
4. **Resumen y pago:** todo lo elegido, importe con IVA, botón grande «Pagar» o «Confirmar reserva». **Solo aquí se reservan las plazas.**

«Paso 2 de 4»; sin límites de tiempo hasta el paso 4. El contador de 30 min solo aparece en la página de pago, con tono tranquilo. Si caduca: mensaje amable y botón «Volver a intentarlo» conservando los datos.

## Reserva atómica (único flujo para pago, gratuito y manual)
Todo en **una transacción**:
1. Validar sesión `SCHEDULED`, antes del cierre, `seats` dentro del máximo, usuario activo y consentimientos vigentes.
2. Reservar plazas con una sola sentencia: `UPDATE activity_session SET reserved_seats = reserved_seats + :n WHERE id = :id AND status = 'SCHEDULED' AND reserved_seats + :n <= capacity RETURNING *`. Sin filas → `E-BOOK-001` y ofrecer lista de espera.
3. Insertar `booking` y `booking_attendee` (salud cifrada). El índice único parcial evita la doble reserva: si ya hay una `PENDING_PAYMENT` del usuario para esa sesión, se devuelve **esa**.
4. Calcular importes en servidor y congelar `unit_price_cents`.
5. Importe 0 → `CONFIRMED`, `payment_method = FREE`, sin Stripe. De pago → `PENDING_PAYMENT`, `expires_at = ahora + 30 min`.
6. **Tras el commit**, crear la Checkout Session de Stripe: `expires_at` 30 min, `client_reference_id = booking.id`, `metadata.booking_id`, **clave de idempotencia = booking.id**. Si Stripe falla, una transacción compensatoria libera plazas y marca `EXPIRED`; mensaje claro y reintento.

## Webhooks de Stripe (`/api/webhooks/stripe`)
- Cuerpo **crudo** y firma verificada con `STRIPE_WEBHOOK_SECRET`; sin firma válida → 400.
- **Excluido del rate limit por IP** (lo protege la firma).
- **Idempotencia:** insertar `event.id` en `processed_webhook_event` (`ON CONFLICT DO NOTHING`); si ya estaba `PROCESSED`, responder 200 sin hacer nada.
- Responder 200 en < 5 s; el trabajo pesado va a Inngest. Si falla el procesado, 500 para que Stripe reintente; alerta tras 3 fallos seguidos.
- Eventos: `checkout.session.completed` (confirma solo si `payment_status = paid`; si `unpaid`, espera), `async_payment_succeeded`, `async_payment_failed`, `expired`, `refund.updated`/`charge.refunded` (sincroniza reembolsos), `charge.dispute.created` (alerta urgente).
- **Verificación de importe y moneda** contra `booking.amount_cents` antes de confirmar; si no coinciden: no confirmar, reembolsar y alertar (`E-PAY-003`).
- **Desorden:** aplicar las transiciones de `docs/04`. Un `completed` posterior a `expired` intenta reconfirmar de forma atómica; sin plazas, reembolso automático `LATE_PAYMENT` con aviso y disculpa.

## Reconciliación
- **Cada 5 min:** para `PENDING_PAYMENT` con `expires_at` vencido hace > 2 min, consultar la sesión en Stripe: pagada → confirmar; si no → expirar y liberar. No depender solo del webhook.
- **Cada noche:** recalcular `reserved_seats` (CONFIRMED + PENDING vigentes + retenciones de oferta); corregir y alertar si hay diferencia.

## Reserva manual por admin (`/admin/reservas/nueva`)
Buscar o crear usuario (se admite solo teléfono), elegir sesión y plazas (puede superar el máximo, auditado), asistentes, forma de cobro (efectivo, transferencia, Bizum, otra) y referencia (`payment.reference`). Mismo procedimiento atómico, `MANUAL_ADMIN`, estado final `CONFIRMED`. Confirmación por SMS/email al usuario o contacto de aviso. Consentimiento de salud obtenido verbalmente: `consent.origin = ADMIN_ASSISTED`. Todo en `audit_log`.

## Reservar para otra persona
La cuenta que paga es la titular y gestiona la cancelación. Cada asistente tiene sus datos. Opcional: contacto de aviso (`notify_contact_*`) que recibe confirmaciones, recordatorios y cambios aunque no tenga cuenta.

## Lista de espera (con retención de plazas)
- Solo en sesiones llenas; una entrada activa por usuario y sesión.
- Al liberarse plazas, un job ofrece al primero cuyo número de plazas encaje y **retiene** esas plazas 2 h. Aviso por SMS y email con enlace al paso 4.
- Si no responde, se liberan y pasa al siguiente. No se ofrece si faltan < 2 h.

## Mínimo de participantes
Con `min_participants`, en `confirm_deadline_at` un job comprueba plazas `CONFIRMED`; si no llega, cancela la sesión (`MIN_NOT_REACHED`) con reembolso del 100 % y aviso. 48 h antes se avisa al admin si faltan inscritos. La ficha lo muestra («Se confirma si hay al menos X personas»).

## Importes, IVA y recibos
- Precios publicados con IVA incluido; `vat_cents = amount_cents - round(amount_cents / (1 + vat_rate_bp/10000))`.
- Cada reserva pagada o manual genera **recibo PDF** con `receipt_number` correlativo, enviado por email y descargable. Factura oficial bajo petición (fuera del MVP).
- Cambios de importe tras una cancelación parcial → recibo rectificativo (formato pendiente de decisión de negocio).
