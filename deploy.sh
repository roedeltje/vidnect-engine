#!/usr/bin/env bash
set -e

echo "==============================="
echo " VidNect Engine deploy"
echo "==============================="

BASE_DIR="$(cd "$(dirname "$0")" && pwd)"
REPO_USER="$(stat -c '%U' "$BASE_DIR/.git")"

echo
echo "→ Repo dir: $BASE_DIR"
echo "→ Git pull as user: $REPO_USER"

sudo -u "$REPO_USER" bash -lc "cd '$BASE_DIR' && git pull"

echo
echo "→ Fixing permissions on storage/..."
sudo chown -R www-data:www-data "$BASE_DIR/storage"
sudo chmod -R 775 "$BASE_DIR/storage"

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
