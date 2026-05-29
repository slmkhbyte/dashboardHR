#!/usr/bin/env sh

set -eu

php artisan package:discover --ansi
php artisan migrate --force
