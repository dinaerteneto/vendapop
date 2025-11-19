FROM php:8.2-cli

# Instalar extensões necessárias do PHP
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar arquivos do projeto
COPY . /var/www/html

# Instalar dependências do Laravel
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Copiar .env se existir (se quiser, pode gerenciar isso fora também)
# RUN cp .env.example .env || true

# Gerar APP_KEY em runtime (se ainda não estiver setado)
RUN php artisan key:generate --force || true

# Expor a porta usada pelo "php artisan serve"
EXPOSE 8000

CMD [ "php", "artisan", "serve", "--host=0.0.0.0", "--port=8000" ]
