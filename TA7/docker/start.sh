#!/bin/sh
set -eu

if [ ! -f .env ]; then
    cp .env.example .env
fi

if [ -z "${APP_KEY:-}" ]; then
    APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
    export APP_KEY
fi

# Use Render's actual HTTPS address for public avatar URLs.
if [ -z "${APP_URL:-}" ] && [ -n "${RENDER_EXTERNAL_URL:-}" ]; then
    export APP_URL="$RENDER_EXTERNAL_URL"
fi

export DB_CONNECTION=sqlite
if [ -n "${APP_DATA_DIR:-}" ]; then
    mkdir -p "$APP_DATA_DIR/public"
    export DB_DATABASE="${DB_DATABASE:-$APP_DATA_DIR/database.sqlite}"
    export PUBLIC_STORAGE_PATH="${PUBLIC_STORAGE_PATH:-$APP_DATA_DIR/public}"
else
    export DB_DATABASE="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
fi

PORT="${PORT:-10000}"
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

mkdir -p bootstrap/cache storage/framework/cache/data storage/framework/sessions \
    storage/framework/views storage/logs storage/app/private storage/app/public \
    "$(dirname "$DB_DATABASE")" "${PUBLIC_STORAGE_PATH:-storage/app/public}"
touch "$DB_DATABASE"
chown -R www-data:www-data storage bootstrap/cache "$(dirname "$DB_DATABASE")" "${PUBLIC_STORAGE_PATH:-storage/app/public}"

php artisan migrate --force
# The seeder inserts missing samples and preserves edited account information.
php artisan db:seed --force
php artisan storage:link --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec apache2-foreground
