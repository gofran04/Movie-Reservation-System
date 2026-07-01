#!/bin/sh

set -e

echo "Starting Laravel container..."

# (optional but safe in dev/prod small setups)
php artisan migrate --force --ansi -vvv

echo "Migrations done."

# Create symbolic link for storage , (|| true) to ignore if it already exists
php artisan storage:link || true

# start PHP-FPM (IMPORTANT FINAL STEP, this exec$@ will becomes "exec php-fpm" )
exec "$@"