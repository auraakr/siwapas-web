# SIWAPAS on Railway: one container that builds the app and serves it with FrankenPHP
# (Caddy + PHP 8.4 in a single process, production grade, no nginx/php-fpm to wire up).
# See DEPLOY.md for the environment variables.

FROM dunglas/frankenphp:1-php8.4-bookworm

# PHP extensions: PostgreSQL (Supabase) and MySQL (Hostinger later), images (Media Library),
# translations, zip (Filament exports) and the usual Laravel set.
RUN install-php-extensions \
        pdo_pgsql pgsql pdo_mysql \
        gd exif intl zip bcmath opcache pcntl \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/php.ini "$PHP_INI_DIR/conf.d/zz-siwapas.ini"

# Composer, and Node for the Vite build (the Wayfinder plugin runs `php artisan` while building,
# so PHP and Node must live in the same image). Node also runs the optional SSR server.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=node:24-bookworm-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:24-bookworm-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

WORKDIR /app

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    APP_ENV=production

# Dependencies first so they are cached between builds.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY . .

# Autoloader + package discovery + Filament assets (post-autoload-dump scripts), then the client
# and SSR bundles. Nothing here touches the database.
RUN composer dump-autoload --optimize --no-dev --no-interaction \
    && npm run build:ssr \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod +x docker/start.sh

EXPOSE 8080

CMD ["docker/start.sh"]
