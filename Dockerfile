# ============================================================
# Stage 1: Build (composer + node in one stage)
# ============================================================
FROM php:8.4-cli-alpine AS build

# Install Node.js
RUN apk add --no-cache nodejs npm

# Install PHP extensions needed for artisan commands during build
RUN apk add --no-cache postgresql-dev icu-dev \
    && docker-php-ext-install pdo_pgsql intl bcmath

WORKDIR /app

# Composer install
COPY composer.json composer.lock ./
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader

# Node install
COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts

# Copy all files, then finalize builds
COPY . .
RUN composer dump-autoload --optimize
RUN npm run build

# ============================================================
# Stage 2: Production runtime
# ============================================================
FROM php:8.4-cli-alpine

RUN apk add --no-cache \
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
    && docker-php-ext-install pdo_pgsql intl gd zip bcmath \
    && apk del .build-deps

WORKDIR /var/www/html

COPY --from=build /app/vendor ./vendor
COPY --from=build /app/public/build ./public/build
COPY . .

RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 10000

CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}
