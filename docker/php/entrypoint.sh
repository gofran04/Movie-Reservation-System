#!/bin/sh
set -e

echo "Starting Laravel container..."

php artisan migrate --force

php artisan storage:link || true

# start PHP-FPM (IMPORTANT FINAL STEP, this exec$@ will becomes "exec php-fpm" )
exec "$@"