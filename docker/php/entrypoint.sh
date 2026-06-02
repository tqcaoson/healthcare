#!/bin/sh
set -e

cd /var/www/html

echo "==> Installing Composer dependencies..."
composer update --no-interaction --optimize-autoloader --no-progress

echo "==> Setting storage permissions..."
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

echo "==> Generating application key (if not set)..."
php artisan key:generate --no-interaction --force

echo "==> Running migrations..."
php artisan migrate --force --no-interaction

echo "==> Running seeders..."
php artisan db:seed --force --no-interaction

echo "==> Creating storage symlink..."
php artisan storage:link --no-interaction || true

echo "==> Clearing config/route/view caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "==> Starting php-fpm..."
exec php-fpm
