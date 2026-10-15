# 02 · Arquitectura, stack e infraestructura

## Stack cerrado
Ver tabla en `AGENTS.md` §2. Todo cambio requiere aprobación explícita.

Notas de versión (verificar documentación vigente al iniciar F0 y **fijar versiones**):
- Next.js 16: `proxy.ts` sustituye a `middleware.ts` y corre en Node.js.
- Prisma: comprobar dónde se declara la URL de conexión en la versión instalada (en Prisma 7 se mueve a `prisma.config.ts`).
- Better Auth: comprobar plugins disponibles (email OTP, phone number, two-factor, admin) antes de implementar `docs/05`.

## Variables de entorno
`DATABASE_URL`, `DIRECT_URL`, `BETTER_AUTH_SECRET`, `BETTER_AUTH_URL`, `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `STRIPE_SECRET_KEY`, `STRIPE_WEBHOOK_SECRET`, `STRIPE_API_VERSION`, `RESEND_API_KEY`, `EMAIL_FROM`, `TWILIO_ACCOUNT_SID`, `TWILIO_AUTH_TOKEN`, `TWILIO_FROM`, `INNGEST_EVENT_KEY`, `INNGEST_SIGNING_KEY`, `UPSTASH_REDIS_REST_URL`, `UPSTASH_REDIS_REST_TOKEN`, `TURNSTILE_SITE_KEY`, `TURNSTILE_SECRET_KEY`, `KMS_KEY_ID`, `AWS_REGION`, `SENTRY_DSN`, `NEXT_PUBLIC_APP_URL`, `SUPPORT_PHONE`, `SUPPORT_HOURS`.

- Ningún secreto con prefijo `NEXT_PUBLIC_`.
- Validación con Zod al arrancar (`src/lib/env.ts`): si falta una variable, la app no inicia.
- Mantener `.env.example` sin valores reales.

## Capas
- `proxy.ts`: redirecciones de cortesía, cabeceras y rate limit global (excluye `/api/webhooks/stripe`).
- `src/server/dal`: acceso a datos + autorización. Firma tipo `getBookingForUser(actor, bookingId)`.
- `src/server/services`: reglas de negocio; llaman al DAL; ejecutan transiciones de estado.
- `src/server/jobs`: Inngest; idempotentes.
- `src/app`: Server Components por defecto; Server Actions delgadas que validan con Zod y delegan en services.

## Convenciones de código
- TypeScript `strict`, sin `any`. Zod en todos los límites (formularios, URL, webhooks, jobs).
- Errores tipados `AppError(code, httpStatus, i18nKey)` con códigos del catálogo (`docs/13`). Nunca se muestran trazas al usuario.
- Identificadores técnicos en inglés `snake_case` (BD) y `camelCase` (TS). Textos visibles solo vía `next-intl`.
- Dinero: utilidades en `src/lib/money.ts` (céntimos enteros, IVA). Fechas: `src/lib/time.ts` (siempre `Europe/Madrid` al mostrar).
- Logs estructurados con **lista blanca** de campos; sin cuerpos de petición completos.

## Entornos y CI/CD
- `local`, `staging`, `production`: BD, claves Stripe (test/live), webhooks, buckets y proyectos de Sentry distintos. Staging usa datos sintéticos.
- En cada PR: lint, tipos, unitarias, integración, E2E y axe. Sin verde no se despliega.
- Migraciones con `migrate deploy`, compatibles hacia atrás. Previews de Vercel contra la BD de staging.

## Operativa
- **Copias:** PITR 7 días + diaria 14 días; restauración probada cada trimestre. RPO ≤ 15 min, RTO ≤ 4 h.
- **Observabilidad:** Sentry (sin PII), comprobación de `/api/health`, alertas por fallo de webhook, fallo de job, 5xx, conexiones BD, reembolsos fallidos, disputas y rebotes de email.
- **Rendimiento:** LCP ≤ 2,5 s en móvil 4G; JS inicial ≤ 170 KB gzip en públicas; `next/image`; pooling de conexiones.
- **SEO:** metadatos por idioma, `sitemap.xml`, datos estructurados `Event` en sesiones; áreas privadas `noindex`.
- **Runbooks:** webhooks caídos, reembolso fallido, reserva atascada, brecha de datos, restauración, rotación de claves, guía ausente.
