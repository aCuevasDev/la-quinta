#!/usr/bin/env bash
# Prueba de humo de La Quinta (F0-03).
# Uso: bash tests/smoke.sh
# Requiere: bash, docker compose y curl (Git Bash / WSL / Linux).
#
# Variable opcional:
#   BASE   URL de la web (por defecto http://localhost:8000)

set -u

BASE="${BASE:-http://localhost:8000}"
FALLOS=0

fallo() {
    echo "  FALLA: $1"
    FALLOS=$((FALLOS + 1))
}

echo "1) Levantando contenedores (docker compose up -d --wait)"
if ! docker compose up -d --wait; then
    echo "  FALLA: no se pudieron levantar los contenedores"
    exit 1
fi

echo "2) Pagina publica /login.php responde 200"
CODIGO="$(curl -s -o /dev/null -w '%{http_code}' "$BASE/login.php")"
[ "$CODIGO" = "200" ] || fallo "login.php devolvio $CODIGO (se esperaba 200)"

echo "3) Pagina privada /inicio.php sin sesion redirige a login.php"
DESTINO="$(curl -s -o /dev/null -w '%{redirect_url}' "$BASE/inicio.php")"
[ "$DESTINO" = "$BASE/login.php" ] || fallo "inicio.php redirigio a '$DESTINO' (se esperaba $BASE/login.php)"

echo "4) Datos de ejemplo: 4 categorias y 12 actividades"
CATEGORIAS="$(docker compose exec -T db mysql -N -ula_quinta -pla_quinta la_quinta -e 'SELECT COUNT(*) FROM categorias;' 2>/dev/null | tr -d '[:space:]')"
ACTIVIDADES="$(docker compose exec -T db mysql -N -ula_quinta -pla_quinta la_quinta -e 'SELECT COUNT(*) FROM actividades;' 2>/dev/null | tr -d '[:space:]')"
[ "$CATEGORIAS" = "4" ] || fallo "hay '$CATEGORIAS' categorias (se esperaban 4)"
[ "$ACTIVIDADES" = "12" ] || fallo "hay '$ACTIVIDADES' actividades (se esperaban 12)"

echo "5) Sintaxis PHP de todos los ficheros"
SALIDA="$(docker compose exec -T web sh -c "find /var/www/html -name '*.php' -exec php -l {} \;")"
if echo "$SALIDA" | grep -q "Parse error\|Errors parsing"; then
    fallo "hay errores de sintaxis en PHP:"
    echo "$SALIDA" | grep "Parse error\|Errors parsing"
fi

echo
if [ "$FALLOS" -eq 0 ]; then
    echo "SMOKE OK"
    exit 0
fi

echo "SMOKE FALLO ($FALLOS comprobaciones)"
exit 1
