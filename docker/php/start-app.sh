#!/bin/sh
set -e

cd /var/www/html

echo "Waiting for PostgreSQL..."
for i in $(seq 1 60); do
    php -r '
$host = getenv("DB_HOST") ?: "postgres";
$port = (int) (getenv("DB_PORT") ?: 5432);
$fp = @fsockopen($host, $port, $errno, $errstr, 1);
if ($fp) {
    fclose($fp);
    exit(0);
}
exit(1);
' && break
    sleep 1
done

if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

php artisan config:clear >/dev/null 2>&1 || true
php artisan cache:clear >/dev/null 2>&1 || true
php artisan migrate --force

exec php artisan serve --host=0.0.0.0 --port=8000
