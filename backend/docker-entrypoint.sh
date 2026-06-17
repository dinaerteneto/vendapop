#!/bin/bash
set -e

if [ ! -d "vendor" ]; then
    echo "vendor não encontrado — executando composer install..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

if [ ! -f ".env" ]; then
    echo ".env não encontrado — copiando .env.example..."
    cp .env.example .env
fi

if [ -z "$APP_KEY" ] || php artisan key:generate --show 2>/dev/null | grep -qi "No application key"; then
    echo "APP_KEY não definida — gerando nova..."
    php artisan key:generate --force
fi

# inject or update env vars that the built-in PHP server needs
for pair in \
    "DB_CONNECTION=${DB_CONNECTION:-mysql}" \
    "DB_HOST=${DB_HOST:-db}" \
    "DB_PORT=${DB_PORT:-3306}" \
    "DB_DATABASE=${DB_DATABASE:-moda_whatsapp_saas}" \
    "DB_USERNAME=${DB_USERNAME:-moda_user}" \
    "DB_PASSWORD=${DB_PASSWORD:-moda_pass}" \
    "APP_ENV=${APP_ENV:-local}" \
    "APP_DEBUG=${APP_DEBUG:-true}" \
    "MAIL_MAILER=${MAIL_MAILER:-log}" \
    "MAIL_HOST=${MAIL_HOST:-127.0.0.1}" \
    "MAIL_PORT=${MAIL_PORT:-2525}" \
    "MAIL_FROM_ADDRESS=${MAIL_FROM_ADDRESS:-hello@example.com}" \
    "MAIL_FROM_NAME=${MAIL_FROM_NAME:-PopVenda}" \
; do
    key="${pair%%=*}"
    if grep -q "^${key}=" .env 2>/dev/null; then
        sed -i "s|^${key}=.*|${pair}|" .env
    else
        echo "${pair}" >> .env
    fi
done

touch database/database.sqlite 2>/dev/null || true

php artisan config:clear 2>/dev/null || true

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

exec php artisan serve --host=0.0.0.0 --port=8000
