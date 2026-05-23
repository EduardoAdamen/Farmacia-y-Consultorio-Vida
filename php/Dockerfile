FROM node:20-alpine AS frontend

WORKDIR /app

COPY src/package*.json ./
RUN npm ci

COPY src/vite.config.js ./
COPY src/resources ./resources
COPY src/public ./public
RUN npm run build


FROM php:8.3-fpm-bookworm AS php-base

RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    git \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libonig-dev \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    unzip \
    zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" bcmath exif gd mbstring opcache pcntl pdo pdo_mysql zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY php/conf.d/production.ini /usr/local/etc/php/conf.d/production.ini

WORKDIR /var/www/html


FROM php-base AS vendor

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY src/ ./

RUN rm -f bootstrap/cache/*.php

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader


FROM php-base AS app

COPY php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
COPY --chown=www-data:www-data src/ ./
COPY --from=vendor --chown=www-data:www-data /var/www/html/vendor ./vendor
COPY --from=frontend --chown=www-data:www-data /app/public/build ./public/build

RUN rm -f bootstrap/cache/*.php \
    && mkdir -p \
    /opt/app-public \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && cp -a public/. /opt/app-public/ \
    && chown -R www-data:www-data /var/www/html /opt/app-public \
    && chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]
