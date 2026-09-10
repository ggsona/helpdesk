# =====================================================
# Dockerfile para Laravel en Render.com (Free Tier)
# PHP 8.2 + Nginx + Supervisor
# =====================================================

FROM php:8.2-fpm-alpine

# --- Instalar dependencias del sistema ---
RUN apk add --no-cache \
    nginx \
    supervisor \
    nodejs \
    npm \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    openssl-dev \
    icu-dev

# --- Instalar extensiones PHP necesarias para Laravel ---
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache

# --- Instalar Composer ---
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# --- Configurar directorio de trabajo ---
WORKDIR /var/www/html

# --- Copiar archivos del proyecto ---
COPY . .

# --- Instalar dependencias PHP (sin dev) ---
# Se usan variables dummy para que artisan package:discover no intente
# conectarse a la BD durante el build (no hay MySQL disponible en esta fase).
RUN APP_KEY=base64:dummykeyfordockerbuild0000000000000000000= \
    DB_CONNECTION=sqlite \
    DB_DATABASE=/dev/null \
    composer install --no-dev --optimize-autoloader --no-interaction

# --- Instalar dependencias Node y compilar assets ---
RUN npm ci && npm run build

# --- Permisos de Laravel ---
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# --- Configuración de Nginx ---
COPY docker/nginx.conf /etc/nginx/nginx.conf

# --- Configuración de Supervisor ---
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# --- Script de inicio ---
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 8080

CMD ["/start.sh"]
