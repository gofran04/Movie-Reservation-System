#!/bin/sh

set -e

echo "Starting Laravel container..."

php artisan migrate --force || echo "Migration failed"
echo "Container kept alive for debugging."

# Create symbolic link for storage , (|| true) to ignore if it already exists
php artisan storage:link || true

# start PHP-FPM (IMPORTANT FINAL STEP, this exec$@ will becomes "exec php-fpm" )
exec "$@"