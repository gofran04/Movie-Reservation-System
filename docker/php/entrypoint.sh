#!/bin/sh
set -e

echo "Starting Laravel container..."

php artisan migrate --force

php artisan db:seed --force

php artisan storage:link || true

# comment :start PHP-FPM (IMPORTANT FINAL STEP, this exec$@ will becomes "exec php-fpm" ), we need this only for local development, in production we will use: exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}, to start laravel's HTTP server to serve the application, while in local we use php-fpm to serve the application through nginx, so we need to comment this line in production, and uncomment the next line to start laravel's HTTP server
#exec "$@"

exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}