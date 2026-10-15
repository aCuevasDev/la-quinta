# 03 · Modelo de datos

## Convenciones
- PK `uuid` v7. Todas las tablas con `created_at`, `updated_at` (`TIMESTAMPTZ`).
- Importes `int` en céntimos. Emails `citext`. Teléfonos E.164.
- **Soft delete** (`deleted_at`) solo en `user`, `activity`, `activity_session`. Reservas, pagos, reembolsos y auditoría **nunca se borran**; se anonimizan (`docs/10`).
- Identificadores en inglés `snake_case`. **Sin `ñ` ni acentos en identificadores.**

## Notas de implementación (obligatorias)
1. **Prisma no declara `CHECK`, índices únicos parciales ni RLS.** Se crean en migraciones SQL manuales y se verifican con pruebas de integración.
2. **RLS activado y denegar todo** en todas las tablas y buckets privados; el acceso es solo con el rol de aplicación vía Prisma. No usar la Data API de Supabase. Prueba obligatoria: «ninguna tabla sin RLS».
3. El rol de BD de la aplicación **no tiene DDL**, y en `audit_log` solo tiene `INSERT` y `SELECT`.

## Enums
- `role`: USER, GUIDE, ADMIN
- `user_status`: ACTIVE, SUSPENDED, ANONYMIZED
- `activity_category`: HEALTH_SPORT, CULTURE_MEMORY, TECH, LEISURE
- `difficulty`: EASY, MODERATE, HARD
- `activity_status`: DRAFT, PUBLISHED, ARCHIVED
- `session_status`: SCHEDULED, COMPLETED, CANCELLED
- `booking_status`: PENDING_PAYMENT, CONFIRMED, CANCELLED, EXPIRED
- `payment_method`: STRIPE, MANUAL_ADMIN, FREE
- `refund_status` (en booking): NONE, NOT_REQUIRED, PENDING, SUCCEEDED, FAILED
- `cancel_reason`: USER_REQUEST, ADMIN, SESSION_CANCELLED, SESSION_RESCHEDULED, MIN_NOT_REACHED, PAYMENT_EXPIRED, LATE_PAYMENT, OTHER
- `review_status`: PENDING, PUBLISHED, REJECTED
- `consent_type`: TERMS, PRIVACY, HEALTH_DATA, THIRD_PARTY_DATA, MARKETING
- `consent_origin`: SELF, ADMIN_ASSISTED
- `channel`: EMAIL, SMS
- `notification_kind`: TRANSACTIONAL, BROADCAST, MARKETING
- `waitlist_status`: WAITING, OFFERED, CONVERTED, EXPIRED, CANCELLED
- `retention_class`: STANDARD, FINANCIAL

## Tablas

### user
`id`, `name`, `display_name` (nombre de pila, único dato público), `birth_date`, `location`, `locale` (es|ca), `email` (citext, null), `email_verified_at`, `phone` (E.164, null), `phone_verified_at`, `password_hash` (null, argon2id), `emergency_contact_name`, `emergency_contact_phone`, `health_notes_enc` (bytea), `health_notes_key_id`, `health_notes_updated_at`, `accessibility_prefs` (jsonb: `font_scale`, `high_contrast`, `reduce_motion`), `stripe_customer_id` (null), `status`, `created_by` (FK user, null), `anonymized_at`, `deleted_at`.
- CHECK: `email IS NOT NULL OR phone IS NOT NULL`.
- Únicos parciales: `email WHERE deleted_at IS NULL AND anonymized_at IS NULL`; igual para `phone`.

### user_role
`user_id`, `role`, `granted_by`, `granted_at`, `revoked_at`. PK (`user_id`, `role`).

### Tablas de autenticación
Las que exija Better Auth (`account`, `session`, `verification`, two-factor…), ajustadas para incluir en `session`: `user_agent`, `ip_hash`, `trusted_device`, `last_seen_at`, `absolute_expires_at`. Las verificaciones guardan **solo el hash** del OTP. Ver `docs/05`.

### user_totp (si el plugin no la cubre)
`user_id`, `secret_enc`, `confirmed_at`, `recovery_codes_hash[]`.

### invitation
`id`, `email`, `phone`, `role`, `token_hash`, `expires_at` (72 h), `accepted_at`, `created_by`.

### consent (solo inserción; nunca se edita)
`id`, `user_id`, `type`, `version`, `granted` (bool), `granted_at`, `origin`, `ip`, `user_agent`.

### activity
`id`, `slug`, `title` (jsonb {es,ca}), `description` (jsonb {es,ca}), `category`, `difficulty`, `location_text`, `image_url`, `gallery` (jsonb con `alt` obligatorio), `meeting_point_text` (jsonb), `meeting_point_image_url`, `base_price_cents`, `vat_rate_bp` (0 si exenta), `max_seats_per_booking` (def. 4), `important_info` (jsonb {es:[],ca:[]}), `accessibility` (jsonb: `reduced_mobility_ok`, `seats_available`, `terrain`, `walking_distance_m`, `needs_stairs`, `toilets_nearby`), `status`, `deleted_at`.
- Único parcial: `slug WHERE deleted_at IS NULL`. No se modifican slugs al borrar.

### activity_session
`id`, `activity_id`, `guide_id` (FK user, null), `starts_at`, `duration_min`, `capacity`, `reserved_seats`, `price_override_cents` (null), `min_participants` (null), `confirm_deadline_at` (null), `meeting_point_override` (jsonb, null), `status`, `cancelled_at`, `cancel_note`, `completed_at`, `rescheduled_at` (null), `previous_starts_at` (null), `deleted_at`.
- `reserved_seats` = plazas de reservas `CONFIRMED` + `PENDING_PAYMENT` vigentes + **retenciones de ofertas** de lista de espera.
- CHECK: `capacity > 0`, `reserved_seats >= 0`, `reserved_seats <= capacity`, `duration_min > 0`.
- Índices: (`activity_id`,`starts_at`), (`guide_id`,`starts_at`), (`status`,`starts_at`).

### booking
`id`, `user_id` (cuenta titular), `session_id`, `seats` (1..`max_seats_per_booking`), `unit_price_cents`, `amount_cents`, `vat_cents`, `currency` (EUR), `status`, `payment_method`, `refund_status`, `expires_at` (solo PENDING_PAYMENT), `confirmed_at`, `cancelled_at`, `cancelled_by`, `cancel_reason`, `cancel_note`, `created_by`, `terms_version`, `aptitude_declared_at`, `notify_contact_name`, `notify_contact_phone`, `notify_contact_email`, `receipt_number` (null, correlativo único).
- Único parcial: (`user_id`,`session_id`) `WHERE status IN ('PENDING_PAYMENT','CONFIRMED')`.
- Índices: (`session_id`,`status`), (`user_id`,`created_at`), (`status`,`expires_at`).
- Un admin puede superar `max_seats_per_booking` en reserva manual (auditado).
- No existe `fecha_reserva`: usar `created_at`.

### booking_attendee
`id`, `booking_id`, `position`, `full_name`, `phone`, `birth_date`, `is_account_holder`, `emergency_contact_name`, `emergency_contact_phone`, `health_notes_enc`, `health_notes_key_id`, `third_party_consent` (obligatorio si no es el titular), `attended` (bool null: null = sin marcar), `checked_in_at`, `checked_in_by`, `scrubbed_at`.

### payment
`id`, `booking_id`, `method`, `amount_cents`, `status` (CREATED, PROCESSING, SUCCEEDED, FAILED, EXPIRED), `stripe_checkout_session_id` (null, único), `stripe_payment_intent_id` (null, único), `reference` (texto, medio de cobro manual), `paid_at`, `failure_code`.
- `FREE` no crea fila; `MANUAL_ADMIN` crea fila `SUCCEEDED`; `STRIPE` la crea al reservar.

### refund
`id`, `payment_id` (null en manuales), `booking_id`, `amount_cents`, `status` (PENDING, SUCCEEDED, FAILED), `stripe_refund_id` (null, único), `idempotency_key` (único), `reason`, `requested_by`, `attempts`, `last_error`, `processed_at`, `manual_reference` (null).

### processed_webhook_event
`event_id` (PK, `evt_…`), `type`, `received_at`, `processed_at`, `status` (PROCESSED, IGNORED, FAILED).

### review
`id`, `user_id`, `activity_id`, `session_id`, `booking_id`, `rating` (1–5), `comment`, `status`, `moderated_by`, `moderated_at`. Único (`user_id`,`activity_id`).

### waitlist_entry
`id`, `session_id`, `user_id`, `seats`, `status`, `offered_at`, `offer_expires_at`. Único parcial (`session_id`,`user_id`) mientras esté activa. En `OFFERED` las plazas están **retenidas** en `reserved_seats`.

### broadcast
`id`, `session_id`, `created_by`, `channels`, `subject`, `body`, `status`, `total`, `sent`, `failed`.

### notification_log
`id`, `user_id`, `booking_id`, `session_id`, `broadcast_id`, `channel`, `kind`, `template`, `recipient_masked`, `status`, `provider_message_id`, `attempts`, `error`, `sent_at`. Único (`booking_id`,`template`) para recordatorios.

### incident
`id`, `session_id`, `reported_by`, `attendee_id` (null), `type`, `description` (**sin datos de salud**; aviso en el formulario), `emergency_called`, `occurred_at`.

### support_callback
`id`, `name`, `phone`, `preferred_time`, `status` (OPEN, DONE), `handled_by`.

### audit_log (solo inserción)
`id`, `actor_user_id`, `actor_role`, `action`, `entity_type`, `entity_id`, `metadata` (jsonb **sin PII ni salud**), `ip_hash`, `retention_class`, `created_at`.

### setting
`key`, `value` (jsonb): horas de cancelación, cierre de reservas, minutos de reserva, días de retención, textos de ayuda, teléfono y horario de soporte.

## Reglas de integridad
- La capacidad nunca baja de `reserved_seats` (error E-SESSION-003).
- `amount_cents` y `vat_cents` se calculan en servidor: `seats × (price_override_cents ?? base_price_cents)`; `unit_price_cents` se congela al reservar.
- `status` solo cambia mediante el servicio de estados (`docs/04`).
- Cualquier tabla nueva nace con RLS activado.
