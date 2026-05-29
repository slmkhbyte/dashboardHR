#!/usr/bin/env sh

set -eu

php artisan package:discover --ansi
php artisan config:cache

exec php artisan queue:work --tries=1 --timeout=0
