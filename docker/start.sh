#!/bin/sh
set -e

echo "=== Iniciando HelpDesk Laravel ==="

# Generar APP_KEY si no existe
if [ -z "$APP_KEY" ]; then
    echo "Generando APP_KEY..."
    php artisan key:generate --force
fi

# Limpiar y cachear configuración para producción
echo "Optimizando configuración..."
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Correr migraciones (--force para entornos de producción)
echo "Ejecutando migraciones..."
php artisan migrate --force

# Crear enlace simbólico de storage (por si acaso)
php artisan storage:link --force 2>/dev/null || true

echo "=== App lista. Iniciando Nginx y PHP-FPM ==="

# Iniciar Supervisor (maneja PHP-FPM + Nginx)
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
