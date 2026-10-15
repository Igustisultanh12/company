#!/usr/bin/env bash
# =====================================================================
#  Skrip update / deploy untuk aaPanel
#  Pemakaian (di folder project):  bash deploy.sh
# =====================================================================
set -euo pipefail

PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"
WEB_USER="${WEB_USER:-www}"

echo "▶ Mode maintenance"
$PHP_BIN artisan down --retry=15 || true

echo "▶ Tarik kode terbaru dari branch main"
git pull origin main

echo "▶ Dependensi PHP"
$COMPOSER_BIN install --no-dev --optimize-autoloader --no-interaction

echo "▶ Build aset frontend"
if [ -f package-lock.json ]; then npm ci --no-audit --no-fund; else npm install --no-audit --no-fund; fi
npm run build

echo "▶ Migrasi database"
$PHP_BIN artisan migrate --force

echo "▶ Optimasi cache"
$PHP_BIN artisan storage:link 2>/dev/null || true
$PHP_BIN artisan optimize:clear
$PHP_BIN artisan optimize

echo "▶ Izin folder"
chown -R "$WEB_USER":"$WEB_USER" storage bootstrap/cache public/build 2>/dev/null || true
chmod -R 775 storage bootstrap/cache

$PHP_BIN artisan up
echo "✅ Deploy selesai"
