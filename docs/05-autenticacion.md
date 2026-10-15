# 05 · Autenticación, sesiones y accesos

Librería: **Better Auth** con sesiones en base de datos (revocables). Antes de implementar, leer su documentación vigente y confirmar que los plugins cubren cada requisito; lo que no cubran se implementa en `src/server/services/auth` sin bajar el nivel de seguridad.

## Métodos de acceso
1. **Email + contraseña.** argon2id; mínimo 10 caracteres; sin reglas de composición absurdas; comprobación contra contraseñas filtradas (HIBP, k-anonimato); opción de mostrar la contraseña.
2. **OTP de 6 dígitos** por email o SMS: válido 10 min, máx. 5 intentos, un solo uso, se guarda solo el hash. **Sin Magic Links** (los escáneres de correo los consumen y fallan en navegadores internos).
3. **Google** (opcional): solo se vincula a una cuenta existente si Google confirma el email verificado.
4. **Cuentas solo con teléfono:** las crea un admin con nombre y teléfono; el usuario entra siempre con OTP por SMS; pueden no tener email ni contraseña.

Todos los formularios de acceso: Turnstile invisible + rate limit por IP, por cuenta y por destino.

## Registro
Campos mínimos: nombre, email **o** teléfono, aceptación de términos y privacidad (se guardan en `consent`). Salud y emergencia se piden **al reservar** o desde el perfil, con consentimiento propio `HEALTH_DATA`. El servidor fija `role = USER`; se ignora cualquier campo de rol.

## Sesiones (en BD, revocables)
| Perfil | Duración | Condiciones |
|---|---|---|
| USER con «Es mi dispositivo» | 30 días renovables | Casilla desmarcada por defecto |
| USER sin marcar | 12 horas | Ordenadores compartidos |
| GUIDE | 7 días renovables | OTP en cada dispositivo nuevo |
| ADMIN | 8 h absolutas, 30 min de inactividad | **TOTP obligatorio**; reautenticación (≤ 10 min) para reembolsos, cambios de rol y borrados |

- Cookies `HttpOnly`, `Secure`, `SameSite=Lax`, prefijo `__Host-`.
- Rotación del identificador al iniciar sesión y al elevar privilegios.
- El perfil muestra sesiones activas y permite cerrarlas todas.
- Cambiar contraseña, email o teléfono cierra las demás sesiones.
- Cambio de rol, suspensión o baja destruye las sesiones **al instante**.

## Protección contra abuso
- Inicio de sesión: **retardo progresivo + Turnstile**, sin bloqueo permanente de la cuenta (evita que un atacante bloquee a un usuario legítimo). Límites en `docs/10`.
- OTP por SMS: solo prefijo `+34` por defecto, límite por usuario y día, protección antifraude del proveedor (SMS pumping).
- Respuestas idénticas exista o no la cuenta; comparaciones de secretos en tiempo constante.

## Recuperación de cuenta
- Con email o teléfono verificado: OTP y nueva contraseña opcional.
- Sin acceso a ninguno: «Te llamamos nosotros». Un admin verifica identidad (nombre completo, fecha de nacimiento y una reserva reciente), actualiza el contacto y queda en `audit_log`.

## Alta de guías y administradores
El admin crea una `invitation` (contacto + rol). El invitado recibe un enlace de un solo uso (72 h, token de 256 bits guardado como hash) por email o SMS, establece su acceso y, si es ADMIN, configura TOTP antes de entrar. **Nunca** se envían contraseñas. Revocar el rol destruye sus sesiones.

## Cambios de contacto
Cambiar email o teléfono exige verificar el nuevo con OTP y avisar al contacto anterior. Queda en `audit_log`.

## Reautenticación
Acciones destructivas o financieras (reembolsos, cancelación masiva, cambios de rol, exportaciones, borrados) exigen reautenticación reciente (≤ 10 min) y confirmación explícita.
