web: sh ./deploy/boot-web.sh
worker: php artisan queue:work --sleep=3 --tries=3 --timeout=120 --no-interaction
cron: sh -lc 'while true; do php artisan schedule:run --no-interaction; sleep 60; done'
