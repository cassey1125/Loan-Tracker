#!/usr/bin/env sh
set -eu

# Warm lightweight Laravel caches on boot for faster request handling.
php artisan config:cache --no-ansi
php artisan view:cache --no-ansi

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
