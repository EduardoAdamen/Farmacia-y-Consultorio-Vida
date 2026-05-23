#!/usr/bin/env sh
set -eu

cd /var/www/html

if [ -z "${APP_KEY:-}" ] || echo "${APP_KEY}" | grep -q "CAMBIAR"; then
    echo "ERROR: APP_KEY no esta configurado. Genera una llave real y agregala a .env.aws antes de iniciar produccion." >&2
    exit 1
fi

mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    public

if [ -d /opt/app-public ]; then
    cp -a /opt/app-public/. public/
fi

chown -R www-data:www-data storage bootstrap/cache public 2>/dev/null || true

if [ "${WAIT_FOR_DB:-true}" = "true" ]; then
    php -r '
        $host = getenv("DB_HOST") ?: "mysql";
        $port = getenv("DB_PORT") ?: "3306";
        $db = getenv("DB_DATABASE") ?: "";
        $user = getenv("DB_USERNAME") ?: "";
        $pass = getenv("DB_PASSWORD") ?: "";
        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

        for ($attempt = 1; $attempt <= 60; $attempt++) {
            try {
                new PDO($dsn, $user, $pass, [PDO::ATTR_TIMEOUT => 3]);
                fwrite(STDOUT, "Base de datos disponible.\n");
                exit(0);
            } catch (Throwable $e) {
                fwrite(STDOUT, "Esperando base de datos ({$attempt}/60)...\n");
                sleep(2);
            }
        }

        fwrite(STDERR, "No se pudo conectar a la base de datos.\n");
        exit(1);
    '
fi

php artisan storage:link --force >/dev/null 2>&1 || true

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force
fi

if [ "${RUN_SEEDERS:-false}" = "true" ]; then
    php artisan db:seed --force
fi

php artisan optimize:clear

if [ "${APP_ENV:-production}" != "local" ]; then
    php artisan config:cache
    if [ "${CACHE_ROUTES:-false}" = "true" ]; then
        php artisan route:cache
    fi
    php artisan view:cache
fi

exec "$@"
