/* La Quinta · ayuda al usuario en el navegador.
   Nunca sustituye la validación de PHP (constitution, regla 1). */

document.addEventListener('DOMContentLoaded', function () {
    // El selector de día no permite elegir fechas pasadas (ayuda para RF-13).
    var hoy = new Date();
    var yyyy = hoy.getFullYear();
    var mm = String(hoy.getMonth() + 1).padStart(2, '0');
    var dd = String(hoy.getDate()).padStart(2, '0');
    var fechaHoy = yyyy + '-' + mm + '-' + dd;

    document.querySelectorAll('input[type="date"]').forEach(function (campo) {
        if (!campo.min) {
            campo.min = fechaHoy;
        }
    });

    // Mostrar u ocultar la contraseña (RNF-03: solo si el usuario lo pide).
    document.querySelectorAll('[data-mostrar-password]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            var campo = document.getElementById(boton.getAttribute('data-mostrar-password'));
            if (!campo) {
                return;
            }
            var oculta = campo.type === 'password';
            campo.type = oculta ? 'text' : 'password';
            boton.setAttribute('aria-pressed', oculta ? 'true' : 'false');
            boton.textContent = oculta ? 'Ocultar contraseña' : 'Mostrar contraseña';
        });
    });
});
