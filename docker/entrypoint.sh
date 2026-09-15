#!/bin/sh
set -e

cd /app

# Platforms like Render inject the port to listen on via $PORT
export SERVER_NAME="${SERVER_NAME:-:${PORT:-8080}}"

if [ -z "$APP_KEY" ]; then
    echo "APP_KEY is not set. Generate one with: php artisan key:generate --show" >&2
    exit 1
fi

php artisan storage:link --force >/dev/null 2>&1 || true

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force
fi

php artisan optimize

exec "$@"
