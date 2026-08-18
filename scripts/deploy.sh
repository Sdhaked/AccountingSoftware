#!/usr/bin/env bash

set -euo pipefail

DEPLOY_PATH="${DEPLOY_PATH:-}"
PHP_BIN="${PHP_BIN:-php}"
RELEASE_ARCHIVE="${RELEASE_ARCHIVE:-/tmp/accounting-software-release.tar.gz}"
MAINTENANCE_FLAG=0

if [[ -z "$DEPLOY_PATH" ]]; then
  echo "DEPLOY_PATH is required."
  exit 1
fi

if [[ ! -f "$RELEASE_ARCHIVE" ]]; then
  echo "Release archive not found at $RELEASE_ARCHIVE."
  exit 1
fi

mkdir -p "$DEPLOY_PATH"
cd "$DEPLOY_PATH"

mkdir -p \
  bootstrap/cache \
  public/build \
  storage/app/private \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/testing \
  storage/framework/views \
  storage/logs

if [[ ! -f .env ]]; then
  echo "Missing $DEPLOY_PATH/.env. Create the production environment file before deploying."
  exit 1
fi

if [[ -f artisan ]]; then
  "$PHP_BIN" artisan down --render="errors::503" || true
  MAINTENANCE_FLAG=1
fi

cleanup() {
  if [[ "$MAINTENANCE_FLAG" -eq 1 && -f artisan ]]; then
    "$PHP_BIN" artisan up || true
  fi
}

trap cleanup EXIT

find . -mindepth 1 -maxdepth 1 \
  ! -name ".env" \
  ! -name ".user.ini" \
  ! -name "storage" \
  ! -name "bootstrap" \
  -exec rm -rf {} +

tar -xzf "$RELEASE_ARCHIVE" -C "$DEPLOY_PATH"

find storage -type d -exec chmod 775 {} +
find bootstrap/cache -type d -exec chmod 775 {} +

"$PHP_BIN" artisan migrate --force
"$PHP_BIN" artisan storage:link || true
"$PHP_BIN" artisan optimize:clear
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache

rm -f "$RELEASE_ARCHIVE" /tmp/accounting-software-deploy.sh
