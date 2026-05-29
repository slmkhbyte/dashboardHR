#!/usr/bin/env sh

set -eu

php artisan package:discover --ansi
php artisan migrate --force

if [ "${RUN_DEMO_SEED:-false}" = "true" ]; then
    php artisan db:seed --force
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec apache2-foreground
