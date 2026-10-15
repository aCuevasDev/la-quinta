# 04 · Máquinas de estado

Toda transición se ejecuta **dentro de una transacción** y comprueba el estado de origen: `UPDATE … WHERE id = ? AND status = origen`. Si afecta a 0 filas, se ignora o se reintenta según el caso. Toda transición no listada es inválida y lanza error. Solo el servicio de estados escribe `status`.

## Booking
| Desde | Hacia | Disparador | Efectos |
|---|---|---|---|
| (nuevo) | PENDING_PAYMENT | Reserva de pago creada | Reserva plazas; `expires_at` = ahora + 30 min; crea `payment` |
| (nuevo) | CONFIRMED | Reserva gratuita o manual | Reserva plazas; envía confirmación |
| PENDING_PAYMENT | CONFIRMED | `checkout.session.completed` con `payment_status = paid`, o `async_payment_succeeded` | Confirma; email/SMS; recibo |
| PENDING_PAYMENT | EXPIRED | `checkout.session.expired`, `async_payment_failed` o reconciliación | Libera plazas; ofrece a lista de espera |
| PENDING_PAYMENT | CANCELLED | El usuario abandona | Libera plazas; expira la sesión de Stripe |
| CONFIRMED | CANCELLED | Usuario (≥ 24 h reales), admin o cancelación de sesión | Libera plazas; crea `refund` si hubo cobro |
| EXPIRED | CONFIRMED | Pago tardío con plazas disponibles | Reintenta reserva atómica; sin plazas → reembolso automático `LATE_PAYMENT` y aviso |

`CANCELLED` es terminal. `EXPIRED` solo pasa a `CONFIRMED` por el pago tardío descrito.

## ActivitySession
| Desde | Hacia | Disparador |
|---|---|---|
| SCHEDULED | COMPLETED | Job cada 15 min: `starts_at + duration_min` ya pasó (fija `completed_at`) |
| SCHEDULED | CANCELLED | Admin, o job de mínimo de participantes no alcanzado |

`COMPLETED` y `CANCELLED` son terminales. Cambiar la fecha de una sesión con reservas solo por el flujo de reprogramación (`docs/07`).

## Refund
- PENDING → SUCCEEDED | FAILED.
- FAILED → PENDING: reintento automático hasta 5 veces con backoff exponencial.
- Agotados los reintentos: `booking.refund_status = FAILED`, alerta y resolución manual en `/admin/pagos`.

## Payment
CREATED → PROCESSING → SUCCEEDED | FAILED | EXPIRED. Una vez `SUCCEEDED` no vuelve atrás; las devoluciones son filas `refund`.

## Waitlist (con retención de plazas)
- WAITING → OFFERED: hay plaza; se **retienen** las plazas (`reserved_seats += seats`) durante 2 h.
- OFFERED → CONVERTED: el usuario completa la reserva (la retención se convierte en la reserva).
- OFFERED → EXPIRED: no responde; se **liberan** las plazas y se ofrece al siguiente.
- WAITING | OFFERED → CANCELLED: el usuario se da de baja (si estaba OFFERED, libera).
- No se ofrece si la sesión empieza en menos de 2 h.

## Idempotencia
Cada transición debe ser segura ante reintentos y dobles clics: repetirla no cambia el resultado ni duplica efectos (emails, reembolsos, plazas).
