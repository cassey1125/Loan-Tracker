$ErrorActionPreference = "Stop"

param(
    [switch]$Seed,
    [switch]$SkipComposer,
    [switch]$SkipNpm
)

Write-Host "Loan Tracker - Local Setup" -ForegroundColor Cyan

if (-not (Test-Path "artisan")) {
    Write-Error "Run this from the project root (the folder that contains artisan)."
    exit 1
}

if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    Write-Host "Created .env from .env.example"
}

$envLines = Get-Content ".env"
$appKey = ($envLines | Where-Object { $_ -like "APP_KEY=*" }) -replace "^APP_KEY=", ""
if ([string]::IsNullOrWhiteSpace($appKey)) {
    Write-Host "Generating APP_KEY..."
    php artisan key:generate
}

$dbHost = ($envLines | Where-Object { $_ -like "DB_HOST=*" }) -replace "^DB_HOST=", ""
$dbPort = ($envLines | Where-Object { $_ -like "DB_PORT=*" }) -replace "^DB_PORT=", ""
$dbName = ($envLines | Where-Object { $_ -like "DB_DATABASE=*" }) -replace "^DB_DATABASE=", ""
Write-Host "Database config: $dbHost:$dbPort / $dbName"

if (-not $SkipComposer) {
    Write-Host "Installing PHP dependencies..."
    composer install
}

if (-not $SkipNpm) {
    Write-Host "Installing frontend dependencies..."
    npm install
}

Write-Host "Running migrations..."
$seedFlag = ""
if ($Seed) {
    $seedFlag = "--seed"
}
php artisan migrate $seedFlag

Write-Host ""
Write-Host "Setup complete."
Write-Host "Next:"
Write-Host "1) Start XAMPP Apache & MySQL"
Write-Host "2) Run: php artisan serve"
Write-Host "3) Optional: npm run dev"
Write-Host "Open: http://127.0.0.1:8000"
