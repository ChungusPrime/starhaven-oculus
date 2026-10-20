# syntax=docker/dockerfile:1

ARG PHP_VERSION=8.4

FROM serversideup/php:${PHP_VERSION}-fpm-nginx AS php-base

USER root
RUN install-php-extensions sqlsrv pdo_sqlsrv
USER www-data

WORKDIR /var/www/html

FROM php-base AS vendor

COPY --chown=www-data:www-data composer.json composer.lock ./
RUN composer install \
    --no-autoloader \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist

COPY --chown=www-data:www-data . .
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --prefer-dist

FROM node:24-bookworm-slim AS assets

WORKDIR /var/www/html

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY vite.config.js ./
COPY resources ./resources
COPY --from=vendor /var/www/html/vendor ./vendor
RUN mkdir -p public && npm run build

FROM php-base AS production

ENV HEALTHCHECK_PATH="/up" \
    PHP_OPCACHE_ENABLE="1" \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS="0"

COPY --from=vendor --chown=www-data:www-data /var/www/html /var/www/html
COPY --from=assets --chown=www-data:www-data /var/www/html/public/build /var/www/html/public/build

RUN mkdir -p \
    bootstrap/cache \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs

EXPOSE 8080
