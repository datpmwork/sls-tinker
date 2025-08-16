FROM bref/php-82-console:2

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
COPY --from=bref/extra-xdebug-php-82:1.8.2 /opt /opt

COPY composer.json /var/package/
COPY config/ /var/package/config/
COPY src/ /var/package/src/

COPY composer.json ./

RUN composer install --no-autoloader --no-scripts

COPY . /var/task

RUN composer dump-autoload
