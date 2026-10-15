# 10 · Seguridad y RGPD

## Seguridad
- **Autorización:** DAL obligatoria (`docs/01`). Pruebas automáticas de IDOR para cada recurso.
- **Validación:** Zod en toda entrada (formularios, URL, webhooks, jobs). Se rechaza cualquier campo no declarado.
- **RLS:** activado y denegar todo en todas las tablas (`docs/03`). No usar la Data API de Supabase.
- **Rate limiting (Upstash, por IP y por cuenta):**

| Recurso | Límite |
|---|---|
| Inicio de sesión | Retardo progresivo + Turnstile; 20 intentos / 15 min por IP |
| Petición de OTP | 3 / 10 min por destino |
| Reservas | 10 / min por usuario |
| API general | 60 / min por IP |
| Webhook de Stripe | Exento; protegido por firma |

- **Anti-acaparamiento de plazas:** máx. 3 reservas `PENDING_PAYMENT` simultáneas por usuario; Turnstile en la reserva; alerta si una IP crea muchas reservas expiradas.
- **CSRF:** verificar `Origin` en Server Actions; cookies `SameSite=Lax`.
- **Cabeceras:** CSP estricta con nonce, `frame-ancestors 'none'`, HSTS con preload, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy` mínima.
- **XSS e inyección:** prohibido `dangerouslySetInnerHTML` con contenido de usuario; `$queryRaw` solo parametrizado.
- **Archivos:** solo imágenes; validar MIME y tamaño (máx. 5 MB), recodificar y renombrar aleatoriamente.
- **Cifrado:** salud (`user.health_notes_enc`, `booking_attendee.health_notes_enc`) con AES-256-GCM y clave de datos propia (DEK) protegida por KMS; guardar `key_id` y **rotar cada 12 meses** con job de recifrado. Contactos de emergencia = datos personales de acceso restringido. TLS 1.2+; backups cifrados.
- **Pagos:** la app nunca ve datos de tarjeta (Stripe Checkout alojado).
- **Logs:** lista blanca de campos; Sentry con `beforeSend` que elimina PII y salud; prohibido registrar cuerpos de petición completos.
- **Enumeración de cuentas:** respuestas idénticas; comparaciones en tiempo constante.
- **BD:** rol de aplicación sin DDL; `audit_log` solo `INSERT`/`SELECT`; staging nunca usa datos reales.
- **Dependencias:** Dependabot, `pnpm audit` en CI, versiones fijadas, Next.js siempre con los últimos parches de seguridad.

## RGPD

### Base jurídica y consentimientos
- Ejecución del contrato (reservas), interés legítimo (seguridad, antifraude) y **consentimiento explícito** para salud (art. 9) y marketing.
- `consent` versionado: tipo, versión, fecha, IP, origen (`SELF`/`ADMIN_ASSISTED`). Cada cambio de texto legal sube versión y puede exigir reaceptación.
- Datos de **terceros** (acompañantes, contacto de emergencia): casilla `THIRD_PARTY_DATA` y aviso informativo a esas personas en la primera comunicación.
- Solo mayores de 18; validar `birth_date` en el alta y en cada asistente.

### Documentación obligatoria
Registro de actividades de tratamiento, **EIPD/DPIA**, contratos de encargado (Supabase, Vercel, Stripe, Resend, Twilio, Upstash, Inngest, Sentry, AWS) con cláusulas y transferencias justificadas, evaluación de DPO y procedimiento de brechas.

### Retención y limpieza
| Dato | Plazo | Acción |
|---|---|---|
| Salud y emergencia del **snapshot** de `booking_attendee` | 7 días tras `completed_at` (o de inmediato si se cancela) | `NULL` + `scrubbed_at` |
| Salud y emergencia del **perfil** (`user`) | Mientras el usuario los mantenga; revisión anual | Se borran al retirar consentimiento o cerrar cuenta |
| `incident` | 12 meses | Anonimizar |
| Reservas, pagos, recibos | 6 años | Conservar; anonimizar datos personales al suprimir cuenta |
| `audit_log` | 3 años (`STANDARD`) o 6 años (`FINANCIAL`) | Borrado |
| `notification_log` | 12 meses | Borrado |
| Sesiones caducadas y OTP | 30 días | Borrado |
| Copias de seguridad | 14 días máx. | Rotación; tras restaurar, **reaplicar supresiones registradas** |

El job `scrub-sensitive-data` corre cada hora, es idempotente, procesa por lotes, registra cada ejecución y reintenta fallos. Tiene pruebas que comprueban que los campos quedan realmente vacíos.

### Derechos y anonimización
Acceso y portabilidad (autoservicio en `/perfil/privacidad`), rectificación, oposición, limitación y supresión, con respuesta en máx. 1 mes (también por teléfono vía admin). La **supresión es anonimización real**: nombre → «Usuario anonimizado»; email y teléfono nulos o no identificables; se eliminan contraseña, sesiones, cuentas externas, TOTP y datos de salud; `status = ANONYMIZED`. Se conservan solo datos contables mínimos y prueba de consentimientos. Como los únicos de email/teléfono son parciales, la persona puede volver a registrarse.

### Páginas y textos legales
`/legal/aviso-legal` (LSSI), `/legal/privacidad`, `/legal/cookies`, `/legal/terminos` (reservas, cancelación, **sin desistimiento de 14 días** al ser ocio con fecha concreta, responsabilidad y aptitud), `/accesibilidad` (declaración conforme a la Ley 11/2023 y la Directiva europea de accesibilidad, con canal de reclamaciones). Banner de cookies: solo técnicas por defecto; «Aceptar» y «Rechazar» con el mismo peso visual; analítica sin cookies o con consentimiento. Todo en ES y CA, lenguaje claro y **revisado por abogado** antes de publicar.

### Brechas
Procedimiento escrito: detección por alertas → contención (revocar sesiones, rotar claves) → evaluación de riesgo → notificación a la AEPD en ≤ 72 h y a los afectados si hay alto riesgo → registro.

### Seguros
La organización debe contratar seguro de responsabilidad civil. La app recoge la declaración de aptitud de cada asistente y las condiciones de participación.
