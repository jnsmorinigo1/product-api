#!/bin/sh
set -eu

cd /var/www/html

if [ ! -f .env ]; then
    cp .env.example .env
fi

installed_vendor=0
if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
    installed_vendor=1
fi

if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force --ansi
fi

if [ "$#" -eq 0 ]; then
    set -- php artisan serve --host=0.0.0.0 --port=8000
fi

if [ "${1:-}" = "php" ] && [ "${2:-}" = "artisan" ] && [ "${3:-}" = "serve" ]; then
    echo "Waiting for MySQL..."
    attempt=0
    until php -r 'try { new PDO(sprintf("mysql:host=%s;port=%s;dbname=%s", getenv("DB_HOST") ?: "mysql", getenv("DB_PORT") ?: "3306", getenv("DB_DATABASE") ?: "products"), getenv("DB_USERNAME") ?: "laravel", getenv("DB_PASSWORD") ?: "secret"); } catch (Throwable $e) { fwrite(STDERR, $e->getMessage().PHP_EOL); exit(1); }'; do
        attempt=$((attempt + 1))
        if [ "$attempt" -ge 30 ]; then
            echo "MySQL did not start in time."
            exit 1
        fi
        sleep 2
    done

    php artisan migrate --force --ansi
    php artisan db:seed --force --ansi
fi

if [ "$(id -u)" = "0" ] && [ -n "${HOST_UID:-}" ]; then
    chown -R "${HOST_UID}:${HOST_GID:-$HOST_UID}" storage bootstrap/cache .env 2>/dev/null || true
    if [ "$installed_vendor" = "1" ] && [ -d vendor ]; then
        chown -R "${HOST_UID}:${HOST_GID:-$HOST_UID}" vendor 2>/dev/null || true
    fi

    exec setpriv --reuid "${HOST_UID}" --regid "${HOST_GID:-$HOST_UID}" --clear-groups --inh-caps=-all "$@"
fi

exec "$@"
