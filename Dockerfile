FROM bref/php-82-console:2

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
COPY --from=bref/extra-xdebug-php-82:1.8.2 /opt /opt

COPY composer.json /var/sls-tinker/
COPY config/ /var/sls-tinker/config/
COPY src/ /var/sls-tinker/src/

COPY test-app/composer.json ./

RUN composer install --no-autoloader --no-scripts

COPY test-app/ /var/task

RUN composer dump-autoload
