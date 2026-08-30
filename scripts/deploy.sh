#!/usr/bin/env bash

set -euo pipefail

DEPLOY_PATH="${DEPLOY_PATH:-}"
PHP_BIN="${PHP_BIN:-php}"
RELEASE_ARCHIVE="${RELEASE_ARCHIVE:-/tmp/accounting-software-release.tar.gz}"
MAINTENANCE_FLAG=0

set_env_value() {
  local key="$1"
  local value="$2"

  if grep -q "^${key}=" .env; then
    sed -i "s|^${key}=.*|${key}=${value}|" .env
  else
    printf '\n%s=%s\n' "$key" "$value" >> .env
  fi
}

detect_php_bin() {
  local candidates=()

  if [[ -n "${PHP_BIN:-}" ]]; then
    candidates+=("$PHP_BIN")
  fi

  candidates+=(
    php8.3
    php8.2
    /usr/bin/php8.3
    /usr/bin/php8.2
    /usr/local/bin/php8.3
    /usr/local/bin/php8.2
    /www/server/php/83/bin/php
    /www/server/php/82/bin/php
    php
  )

  local candidate
  for candidate in "${candidates[@]}"; do
    if command -v "$candidate" >/dev/null 2>&1 || [[ -x "$candidate" ]]; then
      if "$candidate" -r 'exit(PHP_VERSION_ID >= 80200 ? 0 : 1);' >/dev/null 2>&1; then
        printf '%s\n' "$candidate"
        return 0
      fi
    fi
  done

  return 1
}

require_writable_deploy_tree() {
  local blocked

  blocked="$(find . -mindepth 1 \
    ! -path "./.env" \
    ! -path "./.user.ini" \
    ! -path "./storage" \
    ! -path "./storage/*" \
    ! -path "./bootstrap/cache" \
    ! -path "./bootstrap/cache/*" \
    ! -writable \
    -print \
    -quit)"

  if [[ -n "$blocked" ]]; then
    cat <<EOF
Deployment user cannot update this path because at least one existing file or directory is not writable:
  $DEPLOY_PATH/$blocked

Fix the server ownership/permissions once, then re-run the deployment. Example:
  sudo chown -R \$(whoami):\$(whoami) "$DEPLOY_PATH"
  find "$DEPLOY_PATH" -type d -exec chmod 775 {} \\;
  find "$DEPLOY_PATH" -type f -exec chmod 664 {} \\;

EOF
    exit 1
  fi
}

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

if ! PHP_BIN="$(detect_php_bin)"; then
  cat <<EOF
No PHP 8.2+ binary was found on the remote server.
This Laravel application requires PHP >= 8.2, but the default server PHP appears to be older.

Install/enable PHP 8.2+ on the server, or set the GitHub Actions PHP_BIN secret to the full PHP 8.2+ binary path.
Common aaPanel paths:
  /www/server/php/82/bin/php
  /www/server/php/83/bin/php

EOF
  exit 1
fi

echo "Using PHP binary: $PHP_BIN ($("$PHP_BIN" -r 'echo PHP_VERSION;'))"

mkdir -p \
  bootstrap/cache \
  public/build \
  storage/app/private \
  storage/fonts \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/testing \
  storage/framework/views \
  storage/logs

if [[ ! -f .env ]]; then
  echo "Missing $DEPLOY_PATH/.env. Create the production environment file before deploying."
  exit 1
fi

if [[ -n "${DEPLOY_APP_DEBUG:-}" ]]; then
  case "$DEPLOY_APP_DEBUG" in
    true|false)
      set_env_value "APP_DEBUG" "$DEPLOY_APP_DEBUG"
      echo "Updated APP_DEBUG=$DEPLOY_APP_DEBUG"
      ;;
    *)
      echo "DEPLOY_APP_DEBUG must be true or false when provided."
      exit 1
      ;;
  esac
fi

require_writable_deploy_tree

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
chmod 777 storage/fonts
find bootstrap/cache -type d -exec chmod 775 {} +

"$PHP_BIN" artisan migrate --force
"$PHP_BIN" artisan storage:link || true
"$PHP_BIN" artisan optimize:clear
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache

rm -f "$RELEASE_ARCHIVE" /tmp/accounting-software-deploy.sh
