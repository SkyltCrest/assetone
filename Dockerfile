FROM dunglas/frankenphp:1-php8.2

# Laravel needs pdo_mysql; zip lets Composer unpack faster. Most other common
# extensions already ship in the FrankenPHP image.
RUN install-php-extensions pdo_mysql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

EXPOSE 8080

# FrankenPHP serves ./public over a real, multi-threaded HTTP server, so
# concurrent requests - e.g. a phone opening several tabs at once - are handled
# in parallel. The previous `php -S` server was single-process: overlapping
# first-visit requests each minted their own session and CSRF token, the browser
# kept only the last cookie, and the orphaned tabs then failed CSRF with
# "419 Page Expired".
#
# frankenphp is invoked directly (not `php artisan serve`, which strips the
# environment when a .env file is present) so APP_KEY, DB_*, etc. reach the app.
CMD php artisan migrate --force \
    && php artisan storage:link --force \
    && exec frankenphp php-server --root public/ --listen "0.0.0.0:${PORT:-8080}"
