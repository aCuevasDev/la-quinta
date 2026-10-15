# 12 · Pruebas y criterios de aceptación

## Estrategia
- **Unitarias (Vitest):** importes e IVA en céntimos; todas las transiciones de estado (válidas e inválidas); regla de 24 h incluyendo los cambios de hora **25-oct-2026** y **28-mar-2027**; esquemas Zod; permisos por rol; cifrado, rotación y descifrado; ventana temporal del guía.
- **Integración (BD real con Testcontainers):** 100 solicitudes simultáneas por la última plaza (gana exactamente una); doble clic en reservar y en cancelar; webhooks duplicados, desordenados y con importe distinto; pago tardío; reembolso con fallo y reintento; reserva gratuita y manual; bajar capacidad por debajo de lo reservado; lista de espera con retención; **matriz de autorización** (cada función del DAL × cada rol × recurso propio y ajeno); scrubbing deja campos vacíos; anonimización; restauración de backup reaplica supresiones; **ninguna tabla sin RLS**.
- **E2E (Playwright):** registro con email, con OTP y solo teléfono; checkout completo con Stripe en modo test; pago caducado y reintento; paginación con «Atrás»; reserva con acompañante; cancelación total y parcial; reprogramación; lista de espera; reserva manual; cancelación masiva; check-in por persona y modo offline; incidencia; broadcast; exportar y borrar cuenta; ES y CA.
- **Accesibilidad:** axe-core en CI sobre cada ruta y estado; revisión manual con teclado, NVDA, VoiceOver y TalkBack; zoom 200 % y 400 %; simulación de daltonismo; sin solapamientos de elementos fijos. axe detecta solo una parte: es **obligatoria una ronda con 5–8 personas mayores reales** antes del lanzamiento y otra tras corregir.
- **Seguridad:** IDOR y escalada de rol, OWASP ZAP en staging, análisis de dependencias, pentest externo antes de producción.
- **Carga (k6):** 100 usuarios por una plaza, 500 navegando el catálogo, ráfaga de webhooks.
- **Puerta de calidad:** cobertura ≥ 80 % en `services`; 100 % de transiciones de estado y de la matriz de autorización.

## Criterios de aceptación (Dado / Cuando / Entonces)

### Reservas
- AC-R1: Dado 1 plaza y 100 personas reservando a la vez, cuando se procesan, entonces se crea exactamente una reserva y el resto recibe `E-BOOK-001` con opción de lista de espera.
- AC-R2: Dado un usuario con reserva pendiente para la sesión, cuando vuelve a reservar, entonces se le lleva a su pago vigente y no se crea otra.
- AC-R3: Dado un importe de 0 €, cuando confirma, entonces queda `CONFIRMED` sin Stripe y recibe confirmación.
- AC-R4: Dado un pago no completado, cuando pasan 30 min, entonces pasa a `EXPIRED`, las plazas vuelven y se ofrecen a la lista de espera.
- AC-R5: Dado un pago que llega tras expirar y sin plazas, cuando llega el evento, entonces se reembolsa automáticamente y se avisa.
- AC-R6: Dada una reserva de 2 plazas con datos incompletos de un asistente, cuando intenta continuar, entonces no avanza y el error aparece junto al campo en lenguaje llano.
- AC-R7: Dada una oferta de lista de espera activa, cuando otra persona intenta reservar esas plazas, entonces no puede (están retenidas); al caducar la oferta, vuelven a estar disponibles.

### Pagos y webhooks
- AC-P1: Dado el mismo evento de Stripe recibido dos veces, cuando se procesa, entonces se aplica una sola vez.
- AC-P2: Dado un importe pagado distinto del esperado, cuando llega `completed`, entonces no se confirma, se reembolsa y se alerta.
- AC-P3: Dado un webhook sin firma válida, cuando llega, entonces responde 400 y no cambia nada.

### Cancelaciones
- AC-C1: Dada una reserva con > 24 h reales de margen, cuando el usuario cancela, entonces se libera la plaza, se crea un único reembolso y ve una alerta persistente con el plazo.
- AC-C2: Dado un doble clic en cancelar, cuando se procesa, entonces solo se crea un reembolso.
- AC-C3: Dada una cancelación con < 24 h, cuando lo intenta, entonces no se permite y se le muestra el teléfono de ayuda.
- AC-C4: Dada una sesión reprogramada, cuando el usuario cancela en los 14 días siguientes, entonces se reembolsa el 100 % aunque falten < 24 h.
- AC-C5: Dada una sesión cancelada con 300 reservas, cuando el admin confirma, entonces cada reserva se procesa de forma independiente y las fallidas se pueden relanzar.
- AC-C6: Dada una reserva de 3 plazas, cuando el usuario quita a una persona, entonces `seats = 2`, se libera 1 plaza, los datos de esa persona se borran y se reembolsa `unit_price_cents`.
- AC-C7: Dado un reembolso que Stripe rechaza 5 veces, cuando se agotan reintentos, entonces `refund_status = FAILED`, aparece en la bandeja del admin y el usuario recibe el aviso `E-REFUND-001`.

### Roles y datos
- AC-D1: Dado un usuario autenticado, cuando pide la reserva de otra persona por ID, entonces recibe 404.
- AC-D2: Dado un guía, cuando abre una sesión no asignada, entonces recibe 404.
- AC-D3: Dado un guía fuera de la ventana temporal, cuando abre la lista, entonces solo ve nombres y asistencia.
- AC-D4: Dado un registro con `role=ADMIN` manipulado, cuando se procesa, entonces se crea como `USER`.
- AC-D5: Dado un admin al que otro retira el rol, entonces sus sesiones dejan de ser válidas al instante.
- AC-D6: Dada una sesión completada hace 7 días, cuando corre la limpieza, entonces salud y emergencia de sus asistentes quedan vacías y el perfil conserva los suyos.
- AC-D7: Dado un acompañante que no vino y un titular que sí, entonces ambos estados se guardan por separado.
- AC-D8: Dada cualquier tabla del esquema, entonces tiene RLS activado y la clave anónima no puede leerla.

### Accesibilidad
- AC-A1: Dada cualquier página con texto al 200 % y 400 %, entonces no hay solapamientos ni scroll horizontal y el foco es visible.
- AC-A2: Dado un mensaje de confirmación o error, entonces permanece en pantalla hasta que el usuario lo cierra o navega.
- AC-A3: Dada una persona sin email, cuando se registra con teléfono, entonces completa registro, reserva y recibe confirmación por SMS.
