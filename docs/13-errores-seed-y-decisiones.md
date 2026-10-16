# 13 · Catálogo de errores, datos semilla y decisiones pendientes

## 1. Catálogo de errores
Cada error tiene **código, mensaje en ES y CA y una acción sugerida**. Nunca se muestran trazas técnicas.

| Código | Cuándo | Mensaje (ES) |
|---|---|---|
| E-BOOK-001 | Sin plazas | «Lo sentimos, ya no quedan plazas. Puede apuntarse a la lista de espera.» |
| E-BOOK-002 | Reservas cerradas | «Las reservas de esta fecha ya están cerradas. Llame al [teléfono] y le ayudamos.» |
| E-BOOK-003 | Ya tiene reserva | «Ya tiene una reserva para esta fecha. Puede verla en Mis reservas.» |
| E-BOOK-004 | Demasiadas plazas | «Puede reservar hasta [n] plazas cada vez.» |
| E-BOOK-005 | Demasiadas pendientes | «Tiene varias reservas sin pagar. Termine o cancele alguna para continuar.» |
| E-PAY-001 | Pago caducado | «El tiempo para pagar ha terminado. No se ha cobrado nada. Pulse para volver a intentarlo.» |
| E-PAY-002 | Pago rechazado | «Su banco no ha aceptado el pago. Pruebe con otra tarjeta o llame al [teléfono].» |
| E-PAY-003 | Importe no coincide | «Ha habido un problema con el pago. Le devolvemos el dinero y le llamamos.» |
| E-CANCEL-001 | Fuera de plazo | «Ya no se puede cancelar por internet (faltan menos de 24 horas). Llame al [teléfono].» |
| E-CANCEL-002 | Ya cancelada | «Esta reserva ya estaba cancelada.» |
| E-REFUND-001 | Reembolso fallido | «Estamos revisando su reembolso y le avisaremos en cuanto esté listo.» |
| E-SESSION-003 | Capacidad inferior a lo reservado | «No se puede bajar el aforo por debajo de las plazas ya reservadas.» |
| E-AUTH-001 | Código incorrecto o caducado | «El código no es correcto o ha caducado. Pida uno nuevo.» |
| E-AUTH-002 | Demasiados intentos | «Ha probado demasiadas veces. Espere unos minutos o llame al [teléfono].» |
| E-AUTH-003 | Sin permiso | «No se ha encontrado esta página.» (404 genérico) |
| E-AUTH-004 | Sesión caducada | «Por seguridad hemos cerrado su sesión. Vuelva a entrar; no ha perdido nada.» |
| E-DATA-001 | Faltan datos de asistente | «Falta algún dato de [nombre]. Revíselo, por favor.» |
| E-DATA-002 | Permiso de terceros sin marcar | «Necesitamos que confirme que esta persona le ha dado permiso para usar sus datos.» |
| E-GEN-500 | Error interno | «Algo ha fallado por nuestra parte. Inténtelo de nuevo o llame al [teléfono].» |

## 2. Datos semilla (solo `local` y `staging`)
- **Usuarios:** 1 admin (TOTP de prueba), 2 guías, 6 usuarios (uno solo con teléfono, otro con contacto de aviso).
- **Actividades:** 8 (2 por pilar), una gratuita, una con precio e IVA distinto; textos ES y CA; datos de accesibilidad.
- **Sesiones:** una con muchas plazas, una casi llena, una completa, una cancelada, una con mínimo de participantes, una ya completada, una reprogramada, una sin guía a < 72 h.
- **Reservas:** una por estado (`PENDING_PAYMENT`, `CONFIRMED` de pago, gratuita, manual, `CANCELLED` con reembolso `SUCCEEDED` y con `FAILED`, `EXPIRED`) y una con 3 asistentes.
- **Stripe (modo test):** tarjetas de prueba (pago correcto, rechazo, autenticación) y Stripe CLI para reenviar webhooks.
- **Ajustes iniciales:** los valores por defecto de la tabla siguiente, en `setting`.

## 3. Decisiones pendientes del negocio
Hasta que se cierren, se usan los valores por defecto. «Antes de» indica la fase límite.

| Decisión | Valor por defecto | Antes de |
|---|---|---|
| **Confirmar Better Auth en lugar de Auth.js** (`specs/spec001-mvp/plan.md` C1) | Better Auth | F0 |
| IVA o exención de cada tipo de actividad | 21 % incluido | F3 |
| Razón social, CIF, domicilio y textos legales revisados por abogado | Plantillas | F7 |
| Teléfono, horario y responsable del soporte | `SUPPORT_PHONE`, 9–14 h L–V | F2 |
| Plazo de cancelación y plazo de reembolso comunicado | 24 h; 5–10 días laborables | F4 |
| Máximo de plazas por reserva | 4 | F3 |
| Cierre de reservas antes del inicio | 2 h | F3 |
| Retención de salud tras la actividad | 7 días (validar en DPIA) | F7 |
| Retención contable | 6 años | F7 |
| Necesidad de DPO y resultado de la EIPD | A evaluar | F7 |
| Seguro de responsabilidad civil | Pendiente | F8 |
| Proveedor y remitente de SMS (nombre aprobado) | Twilio | F1 |
| Revisión del catalán por persona nativa | Pendiente | F7 |
| Recibo rectificativo en cancelaciones parciales | Recibo nuevo con importe neto | F4 |
| Factura oficial bajo petición | Recibo PDF | Post-MVP |
| WhatsApp | Post-MVP | Post-MVP |
| Gestión contable de cobros manuales (efectivo, Bizum) | Registro con referencia | F5 |
