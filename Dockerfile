FROM php:8.3-cli-alpine

RUN apk add --no-cache \
    sqlite \
    sqlite-dev \
    oniguruma-dev \
    libzip-dev \
    icu-dev \
    $PHPIZE_DEPS \
    && docker-php-ext-install -j$(nproc) pdo_sqlite pdo_mysql mbstring bcmath intl zip \
    && apk del $PHPIZE_DEPS

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader

COPY . .

RUN composer dump-autoload --optimize --no-dev \
    && mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8080
ENTRYPOINT ["docker-entrypoint.sh"]
