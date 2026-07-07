#!/bin/sh
set -e

echo "Waiting for cache table..."

until php artisan tinker --execute="DB::select('select 1 from cache limit 1');" >/dev/null 2>&1
do
    sleep 2
done

echo "Database ready."

exec php artisan queue:work redis --sleep=3 --tries=3