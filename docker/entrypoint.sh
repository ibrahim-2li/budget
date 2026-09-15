#!/bin/sh
set -e

cd /app

# Platforms like Render inject the port to listen on via $PORT
export SERVER_NAME="${SERVER_NAME:-:${PORT:-8080}}"

# Render "Secret Files" are mounted at /etc/secrets
if [ -f /etc/secrets/.env ]; then
    cp /etc/secrets/.env /app/.env
fi

if [ -z "$APP_KEY" ] && ! grep -qE '^APP_KEY=.+' /app/.env 2>/dev/null; then
    echo "APP_KEY is not set. Generate one with: php artisan key:generate --show" >&2
    echo "Variables received (names only): $(env | cut -d= -f1 | grep -E '^(APP_|DB_|RENDER)' | sort | tr '\n' ' ')" >&2
    echo "Secret files in /etc/secrets: $(ls -A /etc/secrets 2>/dev/null | tr '\n' ' ')" >&2
    exit 1
fi

# Render exposes the public URL as RENDER_EXTERNAL_URL
if [ -z "$APP_URL" ] && [ -n "$RENDER_EXTERNAL_URL" ]; then
    export APP_URL="$RENDER_EXTERNAL_URL"
fi

if [ -n "$APP_URL" ] && ! printf '%s' "$APP_URL" | grep -qE '^https?://[A-Za-z0-9.-]+(:[0-9]+)?(/[^[:space:]]*)?$'; then
    echo "APP_URL is invalid: [$APP_URL]. Use a full URL like https://your-app.onrender.com (no spaces, quotes or <>)." >&2
    exit 1
fi

php artisan storage:link --force >/dev/null 2>&1 || true

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force
fi

php artisan optimize

exec "$@"
