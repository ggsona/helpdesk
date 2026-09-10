#!/bin/sh
set -e

echo "=== Iniciando HelpDesk Laravel ==="

# Generar APP_KEY si no existe
if [ -z "$APP_KEY" ]; then
    echo "Generando APP_KEY..."
    php artisan key:generate --force
fi

# Descubrir paquetes (se omitió durante el build por falta de BD)
echo "Descubriendo paquetes..."
php artisan package:discover --ansi

# Limpiar y cachear configuración para producción
echo "Optimizando configuración..."
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Correr migraciones (--force para entornos de producción)
echo "Ejecutando migraciones..."
php artisan migrate --force

# Correr seeders solo si la BD está vacía (primer deploy)
# Se verifica si el usuario admin ya existe para evitar duplicados
echo "Verificando si se necesitan seeders..."
USER_EXISTS=$(php artisan tinker --no-interaction --execute="echo \App\Models\User::where('email','admin@helpdesk.com')->exists() ? 'yes' : 'no';" 2>/dev/null | tail -1)
if [ "$USER_EXISTS" != "yes" ]; then
    echo "Corriendo seeders (primer deploy)..."
    php artisan db:seed --force
else
    echo "Seeders omitidos (datos ya existen)."
fi

# Crear enlace simbólico de storage (por si acaso)
php artisan storage:link --force 2>/dev/null || true

echo "=== App lista. Iniciando Nginx y PHP-FPM ==="

# Iniciar Supervisor (maneja PHP-FPM + Nginx)
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
