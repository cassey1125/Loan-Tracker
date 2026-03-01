#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/loan-tracker}"
PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"
NPM_BIN="${NPM_BIN:-npm}"
GIT_BRANCH="${GIT_BRANCH:-main}"

cd "$APP_DIR"

echo "[1/10] Pulling latest code..."
git fetch origin
git checkout "$GIT_BRANCH"
git pull origin "$GIT_BRANCH"

echo "[2/10] Installing PHP dependencies..."
"$COMPOSER_BIN" install --no-dev --optimize-autoloader --no-interaction --prefer-dist

echo "[3/10] Installing Node dependencies..."
"$NPM_BIN" ci

echo "[4/10] Building frontend assets..."
"$NPM_BIN" run build

echo "[5/10] Running migrations..."
"$PHP_BIN" artisan migrate --force

echo "[6/10] Clearing old caches..."
"$PHP_BIN" artisan optimize:clear

echo "[7/10] Caching production config..."
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache

echo "[8/10] Restarting queue workers..."
"$PHP_BIN" artisan queue:restart

echo "[9/10] Running health checks..."
"$PHP_BIN" artisan loans:reconcile-statuses
"$PHP_BIN" artisan financial:monitor

echo "[10/10] Done."
