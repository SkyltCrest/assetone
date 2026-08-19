FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
        git unzip libzip-dev qrencode \
    && docker-php-ext-install pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && cp -n .env.example .env

EXPOSE 8080

CMD php artisan migrate --force \
    && php artisan storage:link --force \
    && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
