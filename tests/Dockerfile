ARG PHP_VERSION=82

FROM bref/php-${PHP_VERSION}-console:2

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

COPY /package /var/package

COPY /app /var/task

RUN composer dump-autoload
