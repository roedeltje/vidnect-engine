#!/usr/bin/env bash
set -e

echo "==============================="
echo " VidNect Engine deploy"
echo "==============================="

BASE_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$BASE_DIR"

echo
echo "→ Pulling latest code from git..."
git pull

echo
echo "→ Fixing permissions on storage/..."
sudo chown -R www-data:www-data storage
sudo chmod -R 775 storage

echo
echo "→ Reloading Apache..."
sudo systemctl reload apache2

echo
echo "→ Running health check..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/health)

if [ "$HTTP_CODE" -eq 200 ]; then
  echo "✓ Health check OK (200)"
elif [ "$HTTP_CODE" -eq 503 ]; then
  echo "⚠ Health check returned 503 (DB not ready?)"
else
  echo "✗ Health check failed (HTTP $HTTP_CODE)"
  exit 1
fi

echo
echo "✓ Deploy finished successfully"
