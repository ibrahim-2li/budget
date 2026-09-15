# syntax=docker/dockerfile:1

ARG PHP_VERSION=8.4
ARG NODE_VERSION=22

FROM node:${NODE_VERSION}-bookworm-slim AS node

# ---------------------------------------------------------------------------
# Base: FrankenPHP (Caddy + PHP) with the extensions the app needs
# ---------------------------------------------------------------------------
FROM dunglas/frankenphp:1-php${PHP_VERSION}-bookworm AS base

WORKDIR /app

RUN install-php-extensions \
        pdo_mysql \
        opcache \
        intl \
        zip \
        bcmath \
        pcntl \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/php.ini $PHP_INI_DIR/conf.d/zz-app.ini
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ---------------------------------------------------------------------------
# Build: composer deps + Vite assets (Wayfinder needs PHP during vite build)
# ---------------------------------------------------------------------------
FROM base AS build

COPY --from=node /usr/local/bin/node /usr/local/bin/node
COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist

COPY package.json package-lock.json .npmrc ./
RUN npm ci --no-audit --no-fund

COPY . .

RUN composer dump-autoload --no-dev --optimize --classmap-authoritative --no-interaction \
    && npm run build

# ---------------------------------------------------------------------------
# Production image
# ---------------------------------------------------------------------------
FROM base AS production

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    SERVER_NAME=:8080

ARG USER=app
RUN useradd --create-home --uid 1000 ${USER} \
    && chown -R ${USER}:${USER} /data/caddy /config/caddy

COPY --chown=${USER}:${USER} . .
COPY --chown=${USER}:${USER} --from=build /app/vendor ./vendor
COPY --chown=${USER}:${USER} --from=build /app/public/build ./public/build
COPY --chown=${USER}:${USER} --from=build /app/bootstrap/cache ./bootstrap/cache
COPY --chmod=755 docker/entrypoint.sh /usr/local/bin/entrypoint

RUN rm -f /usr/bin/composer

USER ${USER}

EXPOSE 8080

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD curl -fsS http://127.0.0.1:8080/up || exit 1

ENTRYPOINT ["entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
