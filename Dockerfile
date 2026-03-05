# ============================================================
# Stage 1: Composer dependencies
# ============================================================
FROM composer:2 AS composer

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader

COPY . .
RUN composer dump-autoload --optimize

# ============================================================
# Stage 2: Node build (frontend assets)
# ============================================================
FROM node:22-alpine AS node

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts

COPY . .
COPY --from=composer /app/vendor ./vendor
RUN npm run build

# ============================================================
# Stage 3: Production runtime
# ============================================================
FROM php:8.4-cli-alpine

RUN apk add --no-cache \
    sqlite-libs \
    libpq \
    icu-libs \
    freetype \
    libjpeg-turbo \
    libpng \
    libzip \
    && apk add --no-cache --virtual .build-deps \
    postgresql-dev \
    icu-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql pdo_sqlite intl gd zip bcmath \
    && apk del .build-deps

WORKDIR /var/www/html

COPY --from=composer /app/vendor ./vendor
COPY --from=node /app/public/build ./public/build
COPY . .

RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

RUN cp .env.example .env \
    && php artisan key:generate \
    && php artisan config:clear

EXPOSE 8080

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
