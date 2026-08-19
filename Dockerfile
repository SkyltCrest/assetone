FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
        git unzip libzip-dev qrencode \
    && docker-php-ext-install pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

EXPOSE 8080

# `php artisan serve` is a local-dev convenience command: when a .env file is
# present it deliberately strips almost all environment variables before
# spawning its child server process (see ServeCommand::startProcess), which
# breaks env-var-only production containers like this one. Invoke the PHP
# built-in server directly with Laravel's own router script instead, so the
# full container environment (APP_KEY, DB_*, etc.) reaches the app.
CMD php artisan migrate --force \
    && php artisan storage:link --force \
    && cd public \
    && exec php -S 0.0.0.0:${PORT:-8080} /app/vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php
