# 11 · UX, accesibilidad y rutas

## Sistema de diseño (obligatorio)
- **Texto:** base 18 px móvil y 20 px escritorio, interlineado 1,5, fuente sans legible (Atkinson Hyperlegible). Contraste **AA** en todo y **7:1 (AAA)** en texto principal. Nada transmite información solo con color: siempre icono + texto.
- **Controles:** áreas táctiles **≥ 48 px** (56 px en acciones principales), 8 px de separación. Foco visible de 3 px. **Un solo botón principal por pantalla.**
- **Selector de tamaño de texto** (100/125/150 %) siempre visible en cabecera, junto a alto contraste y menos movimiento. Se guarda en la cuenta y, sin sesión, en cookie técnica.
- **Se evitan:** carruseles, interacciones solo con hover, gestos, doble clic, scroll infinito, límites de tiempo en formularios, CAPTCHA de imágenes, mayúsculas largas, desplegables anidados.
- **Formularios:** etiqueta siempre visible; `autocomplete` e `inputmode` correctos; errores junto al campo **y** resumen inicial con el foco; lenguaje llano («Falta el teléfono. Escríbalo con 9 números»).
- **Mensajes:** confirmaciones y errores importantes = **alertas persistentes** (`role="status"`/`"alert"`) que no desaparecen solas. Toasts solo para avisos menores: ≥ 10 s, cerrables, nunca con información crítica.
- **Navegación:** ≤ 5 ítems en cabecera, migas, «Atrás» coherente, jerarquía de encabezados, landmarks ARIA, «Saltar al contenido».
- **Idioma:** ES y CA con selector visible, detección por `Accept-Language`, `lang` correcto.

## Elementos fijos sin solapamientos
- **Barra inferior de reserva** (móvil) a ancho completo; el **botón de ayuda** vive en la cabecera fija y como primer ítem de la barra inferior. Sin botones flotantes sueltos.
- **Banner de cookies** en el flujo (arriba): empuja contenido, no tapa nada.
- Con zoom ≥ 200 % o si los fijos ocupan > 25 % de pantalla, pasan a posición estática. `scroll-padding`/`scroll-margin` para que el foco nunca quede oculto (WCAG 2.4.11). Reflow hasta 400 % sin scroll horizontal.

## Ayuda telefónica
«¿Necesita ayuda?» abre un panel con el **teléfono en texto grande y enlace `tel:`**, horario y «Le llamamos nosotros» (nombre, teléfono, franja → `support_callback`). Fuera de horario: cuándo se devolverá la llamada.

## Pantallas clave
- **Ficha de actividad:** galería sin carrusel automático; bloque «Para saber antes de ir» (accesibilidad, terreno, distancia, sillas, qué llevar); punto de encuentro con foto y mapa; lista de próximas fechas.
- **Confirmación `/reserva/[id]/confirmacion`:** mensaje grande y persistente con fecha, hora, lugar y cómo llegar; «Imprimir», «Añadir al calendario», «Enviar a un familiar» (SMS/email).
- **Pago caducado o fallido:** explicación amable y botón para retomar sin perder datos.
- **404 y 500:** mensaje amable con teléfono de ayuda y enlaces principales.

## Rutas
| Grupo | Rutas |
|---|---|
| Públicas | `/`, `/actividades`, `/actividades/[slug]`, `/quienes-somos`, `/contacto`, `/accesibilidad`, `/legal/aviso-legal`, `/legal/privacidad`, `/legal/cookies`, `/legal/terminos` |
| Acceso | `/auth/login`, `/auth/registro`, `/auth/verificar`, `/auth/recuperar`, `/auth/invitacion/[token]` |
| Reserva | `/reserva/nueva?sesion=[id]`, `/reserva/[id]/pago`, `/reserva/[id]/confirmacion`, `/lista-espera/[id]/oferta` |
| Usuario | `/perfil`, `/perfil/reservas`, `/perfil/reservas/[id]`, `/perfil/datos`, `/perfil/seguridad`, `/perfil/privacidad` |
| Guía | `/guia`, `/guia/sesion/[id]` |
| Admin | `/admin`, `/admin/actividades` (`/nueva`, `/[id]`), `/admin/sesiones` (`/[id]`), `/admin/reservas` (`/nueva`, `/[id]`), `/admin/pagos`, `/admin/usuarios` (`/[id]`), `/admin/guias`, `/admin/resenas`, `/admin/broadcast`, `/admin/incidencias`, `/admin/llamadas`, `/admin/auditoria`, `/admin/ajustes` |
| API | `/api/webhooks/stripe`, `/api/inngest`, `/api/health` |
| Sistema | `not-found`, `error`, `global-error`, `sitemap.xml`, `robots.txt` (con `/admin`, `/guia`, `/perfil` en `noindex`) |
