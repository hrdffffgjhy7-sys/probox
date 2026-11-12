#!/bin/sh
set -e

PORT="${PORT:-8080}"

sed -ri "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

cd /var/www/html

mkdir -p storage/app/backups storage/app/private storage/app/public storage/framework/cache/data \
    storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

DB_FILE="${DB_DATABASE:-/var/www/html/storage/app/database.sqlite}"
mkdir -p "$(dirname "$DB_FILE")"
if [ ! -f "$DB_FILE" ]; then
    touch "$DB_FILE"
fi

php artisan migrate --force

USER_COUNT=$(php artisan tinker --execute="echo \App\Models\User::count();" 2>/dev/null | tr -dc '0-9')
if [ -z "$USER_COUNT" ] || [ "$USER_COUNT" = "0" ]; then
    php artisan db:seed --force
fi

php artisan storage:link 2>/dev/null || true
php artisan config:cache
php artisan view:cache 2>/dev/null || true
php artisan route:cache 2>/dev/null || true

chown -R www-data:www-data storage bootstrap/cache

exec apache2-foreground
