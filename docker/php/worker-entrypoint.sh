#!/bin/sh
set -e

echo "Waiting for cache table..."

until php artisan tinker --execute="exit(\Illuminate\Support\Facades\Schema::hasTable('cache') ? 0 : 1);" >/dev/null 2>&1
do
    sleep 2
done

echo "Cache table exists."

exec php artisan queue:work redis --sleep=3 --tries=3