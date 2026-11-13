# Static hosting image: the design is pre-rendered into dist/ (php artisan export:static).
FROM caddy:2-alpine
COPY Caddyfile /etc/caddy/Caddyfile
COPY dist /srv
