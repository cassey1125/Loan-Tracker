web: php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
worker: php artisan queue:work --sleep=3 --tries=3 --timeout=120 --no-interaction
cron: sh -lc 'while true; do php artisan schedule:run --no-interaction; sleep 60; done'
