# Railway Deployment Guide (Laravel + MySQL)

This project is configured for Railway with:
- `Procfile` (web/worker/cron commands)
- `railway.json` (build/deploy defaults)
- `nixpacks.toml` (PHP + Node build pipeline)

## 1. Connect GitHub Repo

1. Open Railway project.
2. Click `+ Create`.
3. Choose `GitHub Repo`.
4. Select `cassey1125/Loan-Tracker`.
5. Railway creates the app service (you named it `Loan-Tracker`).

## 2. Keep MySQL Only

1. In `Architecture`, keep the `MySQL` service.
2. If you have an unused Postgres service/volume, click it and remove it.

## 3. Configure `Loan-Tracker` (Web)

1. Click `Loan-Tracker` service.
2. Go to `Variables`.
3. Add/set:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<your-railway-domain>
APP_KEY=<paste output of php artisan key:generate --show>
LOG_CHANNEL=stderr
LOG_LEVEL=info

DB_CONNECTION=mysql
DB_URL=${{MySQL.MYSQL_URL}}
```

4. Still in service, go to `Settings`.
5. Set `Pre-Deploy Command`:

```bash
php artisan migrate --force
```

6. Set `Start Command`:

```bash
php artisan serve --host=0.0.0.0 --port=$PORT
```

7. Click `Deploy` (or trigger redeploy from latest commit).

## 4. Create Worker Service

1. In `Architecture`, click `+ Create`.
2. Choose `Empty Service` (or duplicate from repo service).
3. Name it `Loan-Tracker-Worker`.
4. In `Settings`, set `Start Command`:

```bash
php artisan queue:work --sleep=3 --tries=3 --timeout=120 --no-interaction
```

5. In `Variables`, copy the same env vars as `Loan-Tracker`:
- `APP_ENV`, `APP_DEBUG`, `APP_KEY`, `APP_URL`
- `DB_CONNECTION`, `DB_URL`
- `LOG_CHANNEL`, `LOG_LEVEL`

6. Deploy the worker service.

## 5. Create Cron Service

1. In `Architecture`, click `+ Create`.
2. Choose `Empty Service`.
3. Name it `Loan-Tracker-Cron`.
4. In `Settings`, set `Start Command`:

```bash
sh -lc 'while true; do php artisan schedule:run --no-interaction; sleep 60; done'
```

5. In `Variables`, copy the same env vars as `Loan-Tracker`.
6. Deploy the cron service.

## 6. Generate Public Domain

1. Open `Loan-Tracker` service.
2. Go to `Settings` or `Networking`.
3. Click `Generate Domain`.
4. Copy that URL and update `APP_URL` variable.
5. Redeploy `Loan-Tracker`.

## 7. Validate After Deploy

1. Open the app URL.
2. Login and run a smoke test:
- create loan
- record payment
- open dashboard
- open reports
3. Check logs:
- web logs: no DB/auth errors
- worker logs: queue worker running
- cron logs: `schedule:run` every minute

## 8. Optional Persistent Backup Storage

If you rely on `storage/app/backups`, attach a volume to the service that writes backups, or switch backups to external storage.
