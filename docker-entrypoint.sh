#!/bin/sh
set -e

if [ ! -f .env ]; then
    cp .env.example .env
fi

if [ "$DB_CONNECTION" = "sqlite" ] && [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
fi

if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
    php artisan key:generate --force
fi

php artisan config:clear
php artisan migrate --force

exec php artisan serve --host 0.0.0.0 --port "${PORT:-8080}"
