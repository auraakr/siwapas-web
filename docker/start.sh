#!/bin/sh
# Container entry point on Railway. Each step can be switched with an environment variable.
set -e

cd /app

# Migrations change the SHARED Supabase database. Off by default: run them on purpose (see
# DEPLOY.md, "Migrations"), or set RUN_MIGRATIONS=true for a deploy you have approved.
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force --no-interaction
fi

# Cache config, routes, views and Filament components with the runtime environment.
php artisan optimize
php artisan filament:optimize || true

# Scheduler (orders:expire every 5 minutes, and the queue when QUEUE_VIA_SCHEDULER=true) in the
# same container: the cheapest option on a trial plan. Set RUN_SCHEDULER=false when a separate
# cron/worker service takes over.
if [ "${RUN_SCHEDULER:-true}" = "true" ]; then
    php artisan schedule:work >> /proc/1/fd/1 2>&1 &
fi

# Optional Inertia SSR server (needs INERTIA_SSR_ENABLED=true; see DEPLOY.md for the trade-off).
if [ "${INERTIA_SSR_ENABLED:-false}" = "true" ]; then
    php artisan inertia:start-ssr >> /proc/1/fd/1 2>&1 &
fi

exec frankenphp php-server --root /app/public --listen ":${PORT:-8080}"
